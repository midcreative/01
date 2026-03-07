<?php

declare(strict_types=1);

namespace App\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

/**
 * JWT-based admin session authentication.
 */
final class Auth
{
    private const ALGORITHM = 'HS256';
    private const TOKEN_TTL = 60 * 60 * 8; // 8 hours

    private string $secret;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? throw new RuntimeException('JWT_SECRET not configured');
    }

    /** Generate a signed JWT for the admin user. */
    public function generateToken(string $username): string
    {
        $now = time();
        $payload = [
            'iss' => $_ENV['APP_URL'] ?? 'localhost',
            'iat' => $now,
            'exp' => $now + self::TOKEN_TTL,
            'sub' => $username,
        ];

        return JWT::encode($payload, $this->secret, self::ALGORITHM);
    }

    /** Validate token from cookie; returns username or null. */
    public function validateToken(string $token): ?string
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, self::ALGORITHM));
            return $decoded->sub ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** Verify plain-text password against bcrypt hash. */
    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /** Hash a plain-text password with bcrypt. */
    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Require authentication — redirect to login if not valid.
     * Call this at the top of every protected admin controller.
     */
    public function requireAuth(): string
    {
        $token = $_COOKIE['admin_token'] ?? '';
        $user  = $token ? $this->validateToken($token) : null;

        if ($user === null) {
            header('Location: /admin/');
            exit;
        }

        return $user;
    }
}
