<?php
namespace Mopsis\Security;

use stdClass;

class Csrf
{
    public static function generateToken(int $ttl = 300)
    {
        $token = [
            'key'        => substr(sha1(uniqid(rand(), true)), 0, 8),
            'value'      => base64_encode(openssl_random_pseudo_bytes(32)),
            'expiration' => time() + $ttl
        ];

        return (object) $token;
    }

    public static function initSession()
    {
        if (!is_array($_SESSION['csrf'])) {
            $_SESSION['csrf'] = [];
            return;
        }

        foreach ($_SESSION['csrf'] as $key => $token) {
            if (!is_object($token) || $token->expiration < time()) {
                unset($_SESSION['csrf'][$key]);
            }
        }
    }

    public static function addToken(stdClass $token)
    {
        static::initSession();

        $_SESSION['csrf'][$token->key] = $token;
    }

    public static function removeToken(string $key)
    {
        unset($_SESSION['csrf'][$key]);
    }

    public static function isValidToken(string $key, string $value)
    {
        if (!strlen($key) || !strlen($value)) {
            return false;
        }

        if (!($token = $_SESSION['csrf'][$key])) {
            return false;
        }

        if ($token->value !== $value) {
            return false;
        }

        if ($token->expiration < time()) {
            return false;
        }

        return true;
    }
}
