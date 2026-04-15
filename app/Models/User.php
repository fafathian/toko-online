<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

use Filament\Panel;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'phone', 'password', 'postal_code'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

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

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    // Relasi sebagai pembeli
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // --- WAJIB UNTUK FILAMENT TENANCY ---

    // Menentukan toko mana saja yang dimiliki/bisa diakses oleh user ini
    public function getTenants(Panel $panel): array|Collection
    {
        // Mengembalikan array biasa agar sesuai dengan aturan sistem
        return $this->store ? [$this->store] : [];
    }

    // Validasi keamanan: Apakah user boleh mengakses panel toko ini?
    public function canAccessTenant(Model $tenant): bool
    {
        return $this->store()->where('id', $tenant->id)->exists();
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
