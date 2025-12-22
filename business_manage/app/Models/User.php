<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'roles',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Tự định nghĩa hàm hasRole để khớp với file header.blade.php
     */
    public function hasRole($role)
    {
        // Lưu ý: Cột của bạn tên là 'roles' (có chữ s)
        return $this->roles === $role;
    }

    /**
     * Tự định nghĩa hàm hasAnyRole
     */
    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            return in_array($this->roles, $roles);
        }
        return $this->roles === $roles;
    }
}
