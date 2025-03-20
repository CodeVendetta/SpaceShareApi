<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use  HasApiTokens, HasFactory;
    protected $table = 'admin';
    protected $fillable = ['email', 'password', 'role',];
    protected $hidden = ['password'];
    protected $casts = ['password' => 'hashed'];

    public function barang()
    {
        return $this->hasMany(Barang::class);
    }

    public function ruang()
    {
        return $this->hasMany(Ruang::class);
    }

    public function pinjamanBarang()
    {
        return $this->hasMany(PinjamBarang::class);
    }

    public function pinjamanRuang()
    {
        return $this->hasMany(PinjamRuang::class);
    }
}
