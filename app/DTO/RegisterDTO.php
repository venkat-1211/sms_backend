<?php

namespace App\DTO;

class RegisterDTO
{
    public readonly string $name;
    public readonly string $email;
    public readonly string $password;
    public readonly ?string $avatar;

    public function __construct(
        string $name,
        string $email,
        string $password,
        ?string $avatar = null
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->avatar = $avatar;
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            avatar: $data['avatar'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'avatar' => $this->avatar,
        ];
    }
}