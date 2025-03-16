<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PinjamRuang extends Model
{
    use HasFactory;

    protected $fillable = ['ruang_id', 'user_id', 'admin_id', 'tgl_mulai', 'tgl_selesai', 'status', 'is_returned'];

    protected $table = 'pinjam_ruang';

    public function ruang()
    {
        return $this->belongsTo(Ruang::class);
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
