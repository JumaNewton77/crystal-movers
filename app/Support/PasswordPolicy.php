<?php

namespace App\Support;

/** Minimum rules for administrator passwords created from the command line or the seeder. */
class PasswordPolicy
{
    public const MIN_LENGTH = 12;

    private const COMMON = ['admin123', 'password', 'password123', '12345678', '123456789', 'qwerty123', 'letmein', 'changeme', 'crystalmovers'];

    /** @return string|null a reason the password is not acceptable, or null when it is fine */
    public static function problem(string $password): ?string
    {
        if (mb_strlen($password) < self::MIN_LENGTH) {
            return 'The password must be at least '.self::MIN_LENGTH.' characters.';
        }

        if (in_array(strtolower($password), self::COMMON, true) || preg_match('/^(.)\1+$/', $password)) {
            return 'That password is too easy to guess. Use a long, unique password.';
        }

        return null;
    }
}
