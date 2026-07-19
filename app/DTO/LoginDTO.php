<?php

namespace App\DTO;

class LoginDTO
{
    public readonly string $email;
    public readonly string $password;
    public readonly bool $rememberMe;

    public function __construct(
        string $email,
        string $password,
        bool $rememberMe = false
    ) {
        $this->email = $email;
        $this->password = $password;
        $this->rememberMe = $rememberMe;
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            rememberMe: $data['remember_me'] ?? false
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'remember_me' => $this->rememberMe,
        ];
    }
}