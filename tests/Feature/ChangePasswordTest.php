<?php

namespace Tests\Feature;

use App\Filament\Helper\ChangePassword;
use App\Models\AppUser;
use App\Models\Usr;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use ReflectionMethod;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.auth_db' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]]);
        DB::purge('auth_db');

        Schema::connection('auth_db')->create('usr', function (Blueprint $table): void {
            $table->string('userId')->primary();
            $table->string('name')->nullable();
            $table->string('email');
            $table->string('userPassword');
        });

        DB::connection('auth_db')->table('usr')->insert([
            'userId' => 'U001',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'userPassword' => Hash::make('old-password'),
        ]);
    }

    public function test_password_update_writes_the_company_account_password(): void
    {
        $appUser = new AppUser(['user_email' => 'jane@example.com']);
        $this->actingAs($appUser);

        $this->invokeHandleRecordUpdate($appUser, Hash::make('new-password'));

        $companyAccount = Usr::find('U001');
        $this->assertTrue(Hash::check('new-password', $companyAccount->userPassword));
        $this->assertFalse(Hash::check('old-password', $companyAccount->userPassword));
    }

    public function test_already_hashed_password_is_not_hashed_twice(): void
    {
        $appUser = new AppUser(['user_email' => 'jane@example.com']);
        $this->actingAs($appUser);
        $hashedPassword = Hash::make('new-password');

        $this->invokeHandleRecordUpdate($appUser, $hashedPassword);

        $this->assertSame($hashedPassword, Usr::find('U001')->userPassword);
    }

    public function test_password_update_fails_when_no_company_account_matches_the_email(): void
    {
        $appUser = new AppUser(['user_email' => 'missing@example.com']);
        $this->actingAs($appUser);

        $this->expectException(ModelNotFoundException::class);

        $this->invokeHandleRecordUpdate($appUser, Hash::make('new-password'));
    }

    public function test_form_fields_are_cleared_after_a_successful_submission(): void
    {
        $this->actingAs(new AppUser(['user_email' => 'jane@example.com']));

        Livewire::test(ChangePassword::class)
            ->fillForm([
                'password' => 'new-password',
                'passwordConfirmation' => 'new-password',
                'currentPassword' => 'old-password',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertSet('data.password', null)
            ->assertSet('data.passwordConfirmation', null)
            ->assertSet('data.currentPassword', null);

        $this->assertTrue(Hash::check('new-password', Usr::find('U001')->userPassword));
    }

    public function test_form_fields_are_kept_when_the_current_password_is_wrong(): void
    {
        $this->actingAs(new AppUser(['user_email' => 'jane@example.com']));

        Livewire::test(ChangePassword::class)
            ->fillForm([
                'password' => 'new-password',
                'passwordConfirmation' => 'new-password',
                'currentPassword' => 'wrong-password',
            ])
            ->call('save')
            ->assertHasFormErrors(['currentPassword'])
            ->assertSet('data.currentPassword', 'wrong-password');

        $this->assertTrue(Hash::check('old-password', Usr::find('U001')->userPassword));
    }

    public function test_usr_model_stays_guarded_against_mass_assignment(): void
    {
        $this->expectException(MassAssignmentException::class);

        Usr::find('U001')->update(['userPassword' => 'anything']);
    }

    private function invokeHandleRecordUpdate(AppUser $appUser, string $hashedPassword): void
    {
        $method = new ReflectionMethod(ChangePassword::class, 'handleRecordUpdate');
        $method->invoke(new ChangePassword, $appUser, ['password' => $hashedPassword]);
    }
}
