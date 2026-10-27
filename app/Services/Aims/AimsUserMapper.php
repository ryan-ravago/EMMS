<?php

namespace App\Services\Aims;

use App\Models\AppUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Maps AIMS USERS ids to EMMS app_users ids. AIMS ids mean nothing in EMMS (Janica Murillo is 19 in AIMS
 * and 18 in EMMS), so people are matched by name: first name + last name, ignoring case and middle names.
 *
 * AIMS users with no EMMS account are created in the AIMS department as inactive accounts (they keep the
 * history readable but cannot sign in). AIMS e-mail addresses are often shared between several people,
 * so an AIMS address is only reused when exactly one AIMS user has it and EMMS does not use it yet;
 * everyone else gets a placeholder address on the reserved ".invalid" domain.
 */
class AimsUserMapper
{
    public const string PLACEHOLDER_DOMAIN = 'aims.invalid';

    /**
     * @param  Collection<int, object{ID: int|string, FULLNAME: string, EMAIL_ADDRESS: ?string}>  $aimsUsers  all AIMS USERS
     * @param  Collection<int, object{user_id: int|string, user_fname: string, user_lname: string, user_email: string}>  $emmsUsers
     * @param  list<int>  $referencedIds  AIMS user ids that are actually used
     * @return array{map: array<int, int>, create: array<int, array{user_fname: string, user_mname: ?string, user_lname: string, user_email: string, aims_name: string}>, ambiguous: array<int, string>, missing: list<int>}
     */
    public function plan(Collection $aimsUsers, Collection $emmsUsers, array $referencedIds): array
    {
        $emmsByKey = $emmsUsers->groupBy(fn (object $u): string => $this->nameKey(trim("{$u->user_fname} {$u->user_lname}")));
        $usedEmails = $emmsUsers->pluck('user_email')->map(fn ($e) => mb_strtolower(trim((string) $e)))->flip();
        $emailCounts = $aimsUsers->countBy(fn (object $u): string => mb_strtolower(trim((string) $u->EMAIL_ADDRESS)));
        $aimsById = $aimsUsers->keyBy(fn (object $u): int => (int) $u->ID);

        $result = ['map' => [], 'create' => [], 'ambiguous' => [], 'missing' => []];

        foreach (array_unique(array_map('intval', $referencedIds)) as $aimsId) {
            $user = $aimsById->get($aimsId);

            if ($user === null) {
                $result['missing'][] = $aimsId;

                continue;
            }

            $matches = $emmsByKey->get($this->nameKey((string) $user->FULLNAME), collect());

            if ($matches->count() === 1) {
                $result['map'][$aimsId] = (int) $matches->first()->user_id;

                continue;
            }

            if ($matches->count() > 1) {
                $result['ambiguous'][$aimsId] = (string) $user->FULLNAME;

                continue;
            }

            $email = mb_strtolower(trim((string) $user->EMAIL_ADDRESS));
            $reusable = filter_var($email, FILTER_VALIDATE_EMAIL) !== false
                && ($emailCounts[$email] ?? 0) === 1
                && ! $usedEmails->has($email);

            $result['create'][$aimsId] = $this->splitName((string) $user->FULLNAME) + [
                'user_email' => $reusable ? $email : "aims-user-{$aimsId}@".self::PLACEHOLDER_DOMAIN,
                'aims_name' => trim((string) $user->FULLNAME),
            ];
        }

        return $result;
    }

    /**
     * Creates the planned accounts and returns AIMS id => new EMMS user id.
     *
     * @param  array<int, array{user_fname: string, user_mname: ?string, user_lname: string, user_email: string}>  $create
     * @return array<int, int>
     */
    public function create(array $create, int $departmentId): array
    {
        return DB::transaction(function () use ($create, $departmentId): array {
            $ids = [];

            foreach ($create as $aimsId => $user) {
                $ids[$aimsId] = (int) AppUser::query()->create([
                    'user_fname' => $user['user_fname'],
                    'user_mname' => $user['user_mname'],
                    'user_lname' => $user['user_lname'],
                    'user_email' => $user['user_email'],
                    'user_dep_id' => $departmentId,
                    'is_active' => false,
                ])->getKey();
            }

            return $ids;
        });
    }

    /**
     * "DIETHER B IGNE" => Diether / B / Igne, "IAN DEXTER ALBAY" => Ian Dexter / - / Albay, "SUPERMARVS" => Supermarvs / - / -.
     *
     * @return array{user_fname: string, user_mname: ?string, user_lname: string}
     */
    public function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $title = fn (string $s): string => mb_convert_case(mb_strtolower($s), MB_CASE_TITLE);

        if (count($parts) <= 1) {
            return ['user_fname' => $title($parts[0] ?? ''), 'user_mname' => null, 'user_lname' => ''];
        }

        $last = array_pop($parts);
        $middle = null;

        // A single trailing initial before the last name ("B", "P.") is a middle name.
        if (count($parts) > 1 && mb_strlen(rtrim(end($parts), '.')) === 1) {
            $middle = mb_strtoupper(array_pop($parts));
        }

        return ['user_fname' => $title(implode(' ', $parts)), 'user_mname' => $middle, 'user_lname' => $title($last)];
    }

    /**
     * First name + last name, upper case, letters only: "NOLE VARGAS GONOWON" and "Nole Gonowon" both give "NOLE|GONOWON".
     */
    private function nameKey(string $fullName): string
    {
        $parts = preg_split('/\s+/', mb_strtoupper(trim($fullName)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $clean = fn (string $s): string => (string) preg_replace('/[^\p{L}]/u', '', $s);

        return $clean($parts[0] ?? '').'|'.$clean((string) end($parts));
    }
}
