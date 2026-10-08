<?php

namespace App\Support;

use App\Models\User;

class Permission
{
    public static function allows(User $user, string $ability): bool
    {
        $role = $user->role;
        $map = config('permissions.roles', []);

        if ($role === 'parent') {
            return false;
        }

        $abilities = $map[$role] ?? [];

        if (in_array('*', $abilities, true)) {
            return true;
        }

        return in_array($ability, $abilities, true);
    }

    /** @return list<string> */
    public static function abilitiesFor(User $user): array
    {
        if ($user->role === 'parent') {
            return ['parent.portal'];
        }

        $abilities = config('permissions.roles.'.$user->role, []);

        if (in_array('*', $abilities, true)) {
            return self::allAbilityKeys();
        }

        return array_values($abilities);
    }

    /** @return list<string> */
    public static function allAbilityKeys(): array
    {
        $keys = [];
        foreach (config('permissions.roles', []) as $role => $abilities) {
            if ($role === 'admin') {
                continue;
            }
            foreach ($abilities as $ability) {
                $keys[] = $ability;
            }
        }

        return array_values(array_unique($keys));
    }
}
