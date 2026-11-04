<?php

namespace App\Services\Rfid;

use App\Filament\Pages\MyProfile;
use App\Filament\Resources\AssetMovements\AssetMovementResource;
use App\Mail\AssetMovementMail;
use App\Models\AppUser;
use App\Models\AssetMovement;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Throwable;

/**
 * Emails the people who are set up for it each time an equipment or service vehicle goes out or comes in.
 *
 * It is told about trips, not tags: the recorder merges the scans of an asset's several RFID tags into one trip
 * event, so one time out / time in is one email, however many tags were read.
 *
 * Who gets it is decided in Filament Shield, not in code: every role with the "Receive Emails" permission of the
 * Asset Movement resource (given to asset admin and asset manager by migration, any other role by ticking it),
 * and always the super admins. Each person can still switch it off on My Profile.
 */
class MovementNotifier
{
    public const PERMISSION = 'ReceiveEmails:AssetMovementResource';

    /**
     * @param  list<array{0: AssetMovement, 1: 'out'|'in'}>  $events  the trip and what happened to it
     */
    public function notify(array $events): void
    {
        if (! config('rfid.movement_emails.enabled')) {
            return;
        }

        $events = array_values(array_filter($events, fn (array $event): bool => $this->isRecent(...$event)));

        if ($events === []) {
            return;
        }

        $recipients = $this->recipients();

        if ($recipients === []) {
            return;
        }

        foreach ($events as [$movement, $kind]) {
            // One failing mail must not stop the others, and never the scan processing that called us.
            try {
                Mail::to($recipients)->send($this->mailFor($movement, $kind));
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    /**
     * Whether the person is allowed to receive these emails: a super admin, or a role/permission from Shield.
     * (Used for the switch on My Profile; whether they are emailed also needs the switch to be on.)
     */
    public function canReceive(?AppUser $user): bool
    {
        return $user !== null && ($user->hasRole($this->superAdminRole()) || $user->can(self::PERMISSION));
    }

    /**
     * @return list<string>
     */
    public function recipients(): array
    {
        $permissionExists = Permission::query()->where('name', self::PERMISSION)->where('guard_name', 'web')->exists();

        return AppUser::query()
            ->where('is_active', true)
            ->where('notify_asset_movements', true)
            ->whereNotNull('user_email')
            ->where(function ($eligible) use ($permissionExists): void {
                $eligible->role($this->superAdminRole());

                // Roles (or single users) holding the permission. Before the permission is created there are none.
                if ($permissionExists) {
                    $eligible->orWhere(fn ($holders) => $holders->permission(self::PERMISSION));
                }
            })
            ->pluck('user_email')
            ->map(fn (string $email): string => trim($email))
            ->filter(fn (string $email): bool => $email !== '' && ! Validator::make(['email' => $email], ['email' => 'email'])->fails())
            ->unique()
            ->values()
            ->all();
    }

    private function superAdminRole(): string
    {
        return (string) config('filament-shield.super_admin.name', 'super_admin');
    }

    /**
     * Old scans (a backfill, an import, a reader that was offline for a day) are history, not news.
     *
     * @param  'out'|'in'  $kind
     */
    private function isRecent(AssetMovement $movement, string $kind): bool
    {
        $happenedAt = $kind === AssetMovementMail::OUT ? $movement->time_out : $movement->time_in;

        return $happenedAt !== null
            && $happenedAt->gte(now()->subMinutes((int) config('rfid.movement_emails.max_age_minutes')));
    }

    /**
     * @param  'out'|'in'  $kind
     */
    private function mailFor(AssetMovement $movement, string $kind): AssetMovementMail
    {
        $movement->loadMissing(['asset.assetType', 'yardOut', 'yardIn']);
        $asset = $movement->asset;
        $isOut = $kind === AssetMovementMail::OUT;
        $format = 'M d, Y h:i A';

        $details = array_filter([
            'Asset' => $asset?->eqm_name,
            'Asset code' => $asset?->eqm_prc_code,
            'Type' => $asset?->assetType?->name,
            $isOut ? 'Time out' : 'Time in' => ($isOut ? $movement->time_out : $movement->time_in)?->format($format),
            'Yard' => ($isOut ? $movement->yardOut : $movement->yardIn)?->name,
            'Out since' => $isOut ? null : $movement->time_out?->format($format),
            'Time away' => $isOut ? null : $movement->duration,
        ], fn (?string $value): bool => $value !== null && $value !== '');

        $label = collect([$asset?->eqm_prc_code, $asset?->eqm_name])->filter()->implode(' - ') ?: 'Asset #'.$movement->asset_id;

        return new AssetMovementMail(
            kind: $kind,
            assetLabel: $label,
            assetType: $asset?->assetType?->name ?? 'Asset',
            details: $details,
            note: ! $isOut && $movement->time_out === null
                ? 'No time out was recorded for this trip, so it is flagged for review on the Time In / Out page.'
                : null,
            tripUrl: $this->url(fn (): string => AssetMovementResource::getUrl('view', ['record' => $movement])),
            profileUrl: $this->url(fn (): string => MyProfile::getUrl()),
        );
    }

    /**
     * A link is a nicety: if it cannot be built, the email goes without it.
     *
     * @param  callable(): string  $build
     */
    private function url(callable $build): ?string
    {
        try {
            return $build();
        } catch (Throwable) {
            return null;
        }
    }
}
