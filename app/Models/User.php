<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Mail\UserCredentialMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class User extends Authenticatable
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
        'username',
        'password',
        'role',
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function isEmployee(): bool
    {
        return $this->employee !== null;
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Find user by username or email for authentication.
     */
    public static function findByUsernameOrEmail(string $login): ?User
    {
        return static::where('email', $login)
            ->orWhere('username', $login)
            ->first();
    }

    /**
     * Send user credentials via email.
     */
    public function sendCredentials(?string $newPassword = null): bool
    {
        try {
            // Generate temporary password if not provided
            $temporaryPassword = $newPassword ?? Str::random(12);

            // Update user password
            $this->update([
                'password' => Hash::make($temporaryPassword),
            ]);

            // Send email with credentials
            Mail::to($this->email)->send(new UserCredentialMail($this, $temporaryPassword));

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send user credentials', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
