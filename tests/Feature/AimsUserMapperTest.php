<?php

namespace Tests\Feature;

use App\Services\Aims\AimsUserMapper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AimsUserMapperTest extends TestCase
{
    private AimsUserMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        $this->mapper = new AimsUserMapper;
    }

    /**
     * @return Collection<int, object>
     */
    private function aimsUsers(): Collection
    {
        return collect([
            ['ID' => 18, 'FULLNAME' => 'NOLE GONOWON', 'EMAIL_ADDRESS' => 'rerirfid@ravagocranes.com'],
            ['ID' => 19, 'FULLNAME' => 'JANICA MURILLO', 'EMAIL_ADDRESS' => 'rerirfid@ravagocranes.com'],
            ['ID' => 4, 'FULLNAME' => 'VANESSA BARENG', 'EMAIL_ADDRESS' => 'vanessa.bareng@ravagocranes.com'],
            ['ID' => 11, 'FULLNAME' => 'NATHANIEL CHUNG', 'EMAIL_ADDRESS' => 'vanessa.bareng@ravagocranes.com'],
            ['ID' => 5, 'FULLNAME' => 'DIETHER B IGNE', 'EMAIL_ADDRESS' => 'dietherigne@gmail.com'],
            ['ID' => 12, 'FULLNAME' => 'IAN DEXTER ALBAY', 'EMAIL_ADDRESS' => 'taken@ravago.com.ph'],
            ['ID' => 1, 'FULLNAME' => 'SUPERMARVS', 'EMAIL_ADDRESS' => 'not an email'],
        ])->map(fn (array $u) => (object) $u);
    }

    /**
     * @return Collection<int, object>
     */
    private function emmsUsers(): Collection
    {
        return collect([
            ['user_id' => 18, 'user_fname' => 'Janica', 'user_lname' => 'Murillo', 'user_email' => 'janica.murillo@ravago.com.ph'],
            ['user_id' => 27, 'user_fname' => 'NOLE', 'user_lname' => 'GONOWON', 'user_email' => 'nole.gonowon@ravago.com.ph'],
            ['user_id' => 30, 'user_fname' => 'Someone', 'user_lname' => 'Else', 'user_email' => 'taken@ravago.com.ph'],
        ])->map(fn (array $u) => (object) $u);
    }

    public function test_existing_emms_users_are_matched_by_name_and_their_emms_id_is_used(): void
    {
        $plan = $this->mapper->plan($this->aimsUsers(), $this->emmsUsers(), [18, 19]);

        // AIMS 19 (Janica Murillo) is EMMS 18; AIMS 18 (Nole Gonowon) is EMMS 27.
        $this->assertSame([18 => 27, 19 => 18], $plan['map']);
        $this->assertSame([], $plan['create']);
    }

    public function test_a_middle_name_in_emms_does_not_stop_the_match(): void
    {
        $emms = collect([(object) ['user_id' => 27, 'user_fname' => 'NOLE VARGAS', 'user_lname' => 'GONOWON', 'user_email' => 'n@x.ph']]);

        $this->assertSame([18 => 27], $this->mapper->plan($this->aimsUsers(), $emms, [18])['map']);
    }

    public function test_aims_users_without_an_emms_account_are_planned_for_creation(): void
    {
        $plan = $this->mapper->plan($this->aimsUsers(), $this->emmsUsers(), [4, 11, 5, 12, 1]);

        $this->assertSame([], $plan['map']);
        $this->assertSame(['user_fname' => 'Diether', 'user_mname' => 'B', 'user_lname' => 'Igne'], array_intersect_key($plan['create'][5], array_flip(['user_fname', 'user_mname', 'user_lname'])));
        $this->assertSame('Ian Dexter', $plan['create'][12]['user_fname']);
        $this->assertSame('Albay', $plan['create'][12]['user_lname']);
        $this->assertSame('Supermarvs', $plan['create'][1]['user_fname']);
    }

    public function test_only_an_unshared_unused_aims_email_is_reused(): void
    {
        $plan = $this->mapper->plan($this->aimsUsers(), $this->emmsUsers(), [4, 11, 5, 12, 1]);

        $this->assertSame('dietherigne@gmail.com', $plan['create'][5]['user_email']);
        $this->assertSame('aims-user-4@aims.invalid', $plan['create'][4]['user_email'], 'shared with Nathaniel Chung');
        $this->assertSame('aims-user-11@aims.invalid', $plan['create'][11]['user_email'], 'shared with Vanessa Bareng');
        $this->assertSame('aims-user-12@aims.invalid', $plan['create'][12]['user_email'], 'already used by another EMMS user');
        $this->assertSame('aims-user-1@aims.invalid', $plan['create'][1]['user_email'], 'not a valid address');
    }

    public function test_a_name_shared_by_two_emms_users_is_never_guessed(): void
    {
        $emms = $this->emmsUsers()->push((object) ['user_id' => 31, 'user_fname' => 'Janica', 'user_lname' => 'Murillo', 'user_email' => 'j2@ravago.com.ph']);

        $plan = $this->mapper->plan($this->aimsUsers(), $emms, [19]);

        $this->assertSame([], $plan['map']);
        $this->assertSame([], $plan['create']);
        $this->assertSame([19 => 'JANICA MURILLO'], $plan['ambiguous']);
    }

    public function test_an_aims_id_that_is_not_in_aims_users_is_reported(): void
    {
        $this->assertSame([999], $this->mapper->plan($this->aimsUsers(), $this->emmsUsers(), [999])['missing']);
    }

    public function test_created_users_are_inactive_in_the_given_department_and_are_matched_next_time(): void
    {
        Schema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname');
            $table->string('user_mname')->nullable();
            $table->string('user_lname');
            $table->string('user_email')->unique();
            $table->unsignedBigInteger('user_dep_id');
            $table->boolean('is_active')->default(true);
        });

        $plan = $this->mapper->plan($this->aimsUsers(), collect(), [5, 4]);
        $created = $this->mapper->create($plan['create'], 6);

        $this->assertCount(2, $created);
        $diether = DB::table('app_users')->where('user_id', $created[5])->first();
        $this->assertSame(['Diether', 'B', 'Igne', 'dietherigne@gmail.com', 6, 0], [$diether->user_fname, $diether->user_mname, $diether->user_lname, $diether->user_email, (int) $diether->user_dep_id, (int) $diether->is_active]);

        $again = $this->mapper->plan($this->aimsUsers(), DB::table('app_users')->get(), [5, 4]);
        $this->assertSame([5 => $created[5], 4 => $created[4]], $again['map']);
        $this->assertSame([], $again['create']);
    }
}
