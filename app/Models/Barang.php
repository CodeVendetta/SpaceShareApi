<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = ['admin_id', 'nomor', 'nama', 'stok', 'status', 'foto'];

    protected $table = 'barang';

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function statusBarang()
    {
        return $this->belongsTo(StatusBarang::class, 'status');
    }

    public function pinjaman()
    {
        return $this->hasMany(PinjamBarang::class);
    }
}
