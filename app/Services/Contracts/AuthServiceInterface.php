<?php

namespace App\Services\Contracts;

interface AuthServiceInterface
{
    public function registerUser(array $data);
    public function loginUser(array $credentials);
    public function verifyEmail(string $email, string $code);
    public function resendCode(string $email);
    public function forgotPassword(string $email);
    public function verifyResetCode(string $email, string $code);
    public function resetPassword(string $email, string $code, string $password);
}
