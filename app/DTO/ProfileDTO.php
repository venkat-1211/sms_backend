<?php

namespace App\DTO;

class ProfileDTO
{
    public readonly string $name;
    public readonly string $email;
    public readonly ?string $avatar;
    public readonly ?string $bio;

    public function __construct(
        string $name,
        string $email,
        ?string $avatar = null,
        ?string $bio = null
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->avatar = $avatar;
        $this->bio = $bio;
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            avatar: $data['avatar'] ?? null,
            bio: $data['bio'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'bio' => $this->bio,
        ];
    }
}