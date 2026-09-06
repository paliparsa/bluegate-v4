<?php
namespace App\Domain\Payments\Contracts;
interface PaymentGateway {
 public function request(float $amount, string $description, string $callbackUrl, array $metadata=[]): array;
 public function verify(string $authority, float $amount): array;
}