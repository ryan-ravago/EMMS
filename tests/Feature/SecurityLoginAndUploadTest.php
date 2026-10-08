<?php

namespace Tests\Feature;

use App\Filament\Helper\CustomLogin;
use App\Filament\Support\FileUploadDefaults;
use App\Models\AppUser;
use App\Models\SiteSetting;
use Closure;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class SecurityLoginAndUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);
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
            $table->boolean('isActive')->default(true);
        });

        Schema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname')->nullable();
            $table->string('user_lname')->nullable();
            $table->string('user_email')->nullable();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('site_name')->default('EMMS');
            $table->string('site_logo')->nullable();
            $table->string('site_favicon')->nullable();
            $table->string('site_primary_color')->default('amber');
            $table->string('site_font_family')->default('Inter');
            $table->timestamps();
        });
    }

    public function test_panel_head_uses_a_safe_font_name(): void
    {
        SiteSetting::instance()->update(['site_font_family' => 'Roboto']);

        $this->get('/login')
            ->assertOk()
            ->assertSee("font-family: 'Roboto'", false);
    }

    public function test_panel_head_ignores_a_font_name_that_could_inject_html(): void
    {
        SiteSetting::instance()->update(['site_font_family' => "Inter'; }</style><script>alert(1)</script><style>"]);

        $this->get('/login')
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee("font-family: 'Inter'", false);
    }

    public function test_login_regenerates_the_session(): void
    {
        $appUser = $this->createAccount('jane@example.com');
        $sessionIdBeforeLogin = session()->getId();

        Livewire::test(CustomLogin::class)
            ->fillForm(['email' => 'jane@example.com', 'password' => 'secret-password'])
            ->call('authenticate');

        $this->assertAuthenticatedAs($appUser);
        $this->assertNotSame($sessionIdBeforeLogin, session()->getId());
    }

    public function test_inactive_company_account_cannot_log_in(): void
    {
        $this->createAccount('jane@example.com', companyAccountIsActive: false);

        Livewire::test(CustomLogin::class)
            ->fillForm(['email' => 'jane@example.com', 'password' => 'secret-password'])
            ->call('authenticate')
            ->assertNotified($this->invalidLoginNotification());

        $this->assertGuest();
    }

    public function test_missing_emms_account_gets_the_same_message_as_a_wrong_password(): void
    {
        DB::connection('auth_db')->table('usr')->insert([
            'userId' => 'U002',
            'email' => 'outsider@example.com',
            'userPassword' => Hash::make('secret-password'),
            'isActive' => true,
        ]);

        Livewire::test(CustomLogin::class)
            ->fillForm(['email' => 'outsider@example.com', 'password' => 'secret-password'])
            ->call('authenticate')
            ->assertNotified($this->invalidLoginNotification());

        $this->assertGuest();
    }

    public function test_wrong_password_cannot_log_in(): void
    {
        $this->createAccount('jane@example.com');

        Livewire::test(CustomLogin::class)
            ->fillForm(['email' => 'jane@example.com', 'password' => 'wrong-password'])
            ->call('authenticate')
            ->assertNotified($this->invalidLoginNotification());

        $this->assertGuest();
    }

    public function test_file_uploads_only_accept_documents_and_images_by_default(): void
    {
        $this->assertSame(FileUploadDefaults::ACCEPTED_FILE_TYPES, FileUpload::make('attachments')->getAcceptedFileTypes());
    }

    public function test_file_upload_fields_can_still_narrow_accepted_types(): void
    {
        $this->assertSame(['image/*'], FileUpload::make('logo')->image()->getAcceptedFileTypes());
    }

    public function test_svg_and_video_uploads_are_refused(): void
    {
        $this->assertSame('SVG images are not accepted.', $this->uploadError('image/svg+xml'));
        $this->assertSame('Videos are not accepted.', $this->uploadError('video/mp4'));
        $this->assertNull($this->uploadError('image/png'));
        $this->assertNull($this->uploadError('application/pdf'));
    }

    private function createAccount(string $email, bool $companyAccountIsActive = true): AppUser
    {
        DB::connection('auth_db')->table('usr')->insert([
            'userId' => 'U001',
            'name' => 'Jane Doe',
            'email' => $email,
            'userPassword' => Hash::make('secret-password'),
            'isActive' => $companyAccountIsActive,
        ]);

        return AppUser::create([
            'user_fname' => 'Jane',
            'user_lname' => 'Doe',
            'user_email' => $email,
            'is_active' => true,
        ]);
    }

    private function invalidLoginNotification(): Notification
    {
        return Notification::make()
            ->title('Invalid Login')
            ->body('Email or password is incorrect. Attempts remaining: 2')
            ->warning();
    }

    /**
     * Runs the custom rules FileUploadDefaults adds against a file of the given MIME type.
     */
    private function uploadError(string $mimeType): ?string
    {
        $file = Mockery::mock(TemporaryUploadedFile::class);
        $file->allows('getMimeType')->andReturn($mimeType);

        $error = null;

        // FileUpload wraps per-file rules in an array rule; read the per-file rules directly.
        $fileRules = Closure::bind(fn (): array => parent::getValidationRules(), FileUpload::make('attachments'), BaseFileUpload::class)();

        foreach ($fileRules as $rule) {
            if ($rule instanceof Closure) {
                $rule('attachments', $file, function (string $message) use (&$error): void {
                    $error ??= $message;
                });
            }
        }

        return $error;
    }
}
