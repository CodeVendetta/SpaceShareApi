<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruang extends Model
{
    use HasFactory;

    protected $fillable = ['admin_id', 'nomor', 'nama', 'status', 'foto'];

    protected $table = 'ruang';

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function statusRuang()
    {
        return $this->belongsTo(StatusRuang::class, 'status');
    }

    public function pinjaman()
    {
        return $this->hasMany(PinjamRuang::class);
    }
}
