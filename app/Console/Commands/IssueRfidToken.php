<?php

namespace App\Console\Commands;

use App\Models\AppUser;
use App\Models\Department;
use Illuminate\Console\Command;

class IssueRfidToken extends Command
{
    private const SERVICE_EMAIL = 'rfid-integration@emms.invalid';

    protected $signature = 'rfid:issue-token
                            {--dep= : dep_id of the service account (only needed the first time)}
                            {--revoke : Revoke every existing RFID token first}';

    protected $description = 'Issue the Sanctum bearer token used by the external RFID reader server';

    public function handle(): int
    {
        $user = AppUser::firstWhere('user_email', self::SERVICE_EMAIL) ?? $this->createServiceUser();

        if (! $user) {
            return self::FAILURE;
        }

        if ($this->option('revoke')) {
            $user->tokens()->delete();
        }

        $token = $user->createToken('rfid-reader-server', ['rfid:write'], now()->addYear());

        $this->newLine();
        $this->line($token->plainTextToken);
        $this->newLine();
        $this->warn('Copy it now, it is shown only once. Expires '.$token->accessToken->expires_at->toDateString().'.');

        return self::SUCCESS;
    }

    /**
     * Inactive, role-less AppUser: it can hold a token but can never sign in to the panel.
     */
    private function createServiceUser(): ?AppUser
    {
        $depId = $this->option('dep');

        if (! $depId || ! Department::whereKey($depId)->exists()) {
            $this->error('Pass --dep=<dep_id> of an existing department. Available:');
            $this->table(['dep_id', 'dep_code', 'dep_name'], Department::orderBy('dep_id')->get(['dep_id', 'dep_code', 'dep_name'])->toArray());

            return null;
        }

        return AppUser::create([
            'user_fname' => 'RFID',
            'user_lname' => 'Integration',
            'user_email' => self::SERVICE_EMAIL,
            'user_dep_id' => $depId,
            'is_active' => false,
        ]);
    }
}
