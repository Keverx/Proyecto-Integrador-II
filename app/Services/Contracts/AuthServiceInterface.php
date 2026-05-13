<?php

namespace App\Services\Contracts;

interface AuthServiceInterface
{
    public function registerUser(array $data);
    public function loginUser(array $credentials);
}
