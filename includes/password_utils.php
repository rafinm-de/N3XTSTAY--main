<?php
/**
 * Password Hashing Utility
 * Provides secure password hashing and verification functions
 */

/**
 * Hash a password securely using PHP's password_hash function
 * 
 * @param string $password The plain text password to hash
 * @return string The hashed password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password against a hash
 * 
 * @param string $password The plain text password to verify
 * @param string $hash The hashed password to verify against
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Check if a password hash needs to be rehashed
 * Useful for updating hashes when algorithm changes
 * 
 * @param string $hash The password hash to check
 * @return bool True if rehash is needed, false otherwise
 */
function needsRehash($hash)
{
    return password_needs_rehash($hash, PASSWORD_DEFAULT);
}
?>