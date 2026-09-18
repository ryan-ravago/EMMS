<?php

namespace Tests\Unit;

use App\Models\AppUser;
use Filament\Panel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppUserActiveStatusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname');
            $table->string('user_lname');
            $table->string('user_email')->unique();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function test_active_user_can_access_panel(): void
    {
        $user = AppUser::create([
            'user_fname' => 'Jane',
            'user_lname' => 'Doe',
            'user_email' => 'jane@example.com',
            'is_active' => true,
        ]);

        $this->assertTrue($user->canAccessPanel(app(Panel::class)));
    }

    public function test_inactive_user_cannot_access_panel(): void
    {
        $user = AppUser::create([
            'user_fname' => 'John',
            'user_lname' => 'Smith',
            'user_email' => 'john@example.com',
            'is_active' => false,
        ]);

        $this->assertFalse($user->canAccessPanel(app(Panel::class)));
    }
}
