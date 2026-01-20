<?php

namespace App\Hashing;

use Illuminate\Hashing\Argon2IdHasher as BaseHasher;
use RuntimeException;

class CustomArgon2IdHasher extends BaseHasher
{
    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = [])
    {
        // We bypass the strict algoName check that Laravel normally does
        // to support old bcrypt hashes during migration.
        return password_verify($value, $hashedValue);
    }
}
