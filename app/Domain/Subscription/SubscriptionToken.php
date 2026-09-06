<?php
namespace App\Domain\Subscription;

final class SubscriptionToken
{
    public static function generate(): array
    {
        $plain = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        return ['plain' => $plain, 'hash' => self::hash($plain)];
    }

    public static function hash(string $plain): string
    {
        return hash('sha256', $plain.'|'.config('bluegate.token_pepper'));
    }
}
