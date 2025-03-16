<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PinjamBarang extends Model
{
    use HasFactory;

    protected $fillable = ['barang_id', 'user_id', 'admin_id', 'tgl_mulai', 'tgl_selesai', 'qty', 'status', 'is_returned'];

    protected $table = 'pinjam_barang';

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function statusPeminjaman()
    {
        return $this->belongsTo(StatusPeminjaman::class, 'status');
    }
}
