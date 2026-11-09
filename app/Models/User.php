<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'role', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Inisial untuk avatar, cth. "Siti Rahmawati" → "SR" */
    public function getInitialsAttribute(): string
    {
        $kata = preg_split('/\s+/', trim($this->name ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: ['?'];
        $ambil = count($kata) > 1 ? [$kata[0], end($kata)] : [$kata[0]];
        return strtoupper(implode('', array_map(fn($k) => mb_substr($k, 0, 1), $ambil)));
    }

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
}
