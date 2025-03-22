<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PinjamBarang;
use App\Models\PinjamRuang;
use App\Models\StatusBarang;
use App\Models\StatusRuang;
use App\Models\StatusPeminjaman;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $user = User::get();

        return response()->json([
            'message' => 'Data berhasil diambil',
            'data' => $user
        ]);
    }

    public function barangDipinjamPerUser()
    {
        $barangDipinjam = PinjamBarang::whereIn('status', [1, 2])
            ->with(['barang', 'user', 'statusPeminjaman'])
            ->get()
            ->groupBy('user_id')
            ->map(function ($items, $userId) {
                return [
                    'user' => $items->first()->user,
                    'total_barang_dipinjam' => $items->count(),
                    'detail' => $items->map(function ($item) {
                        return [
                            'barang_id' => $item->barang->id,
                            'nama_barang' => $item->barang->nama,
                            'status_peminjaman' => $item->statusPeminjaman->nama_status ?? 'Tidak diketahui',
                            'tgl_mulai' => $item->tgl_mulai,
                            'tgl_selesai' => $item->tgl_selesai,
                            'qty' => $item->qty,
                            'is_returned' => $item->is_returned,
                        ];
                    })
                ];
            })
            ->values();
    
        return response()->json([
            'message' => 'Data jumlah ruang yang sedang dipinjam per user berhasil diambil',
            'data' => $ruangDipinjam
        ]);
    }
    
    public function ruangDipinjamPerUser()
    {
        $ruangDipinjam = PinjamRuang::whereIn('status', [1, 2])
            ->with(['ruang', 'user', 'statusPeminjaman'])
            ->get()
            ->groupBy('user_id')
            ->map(function ($items, $userId) {
                return [
                    'user' => $items->first()->user,
                    'total_ruang_dipinjam' => $items->count(),
                    'detail' => $items->map(function ($item) {
                        return [
                            'ruang_id' => $item->ruang->id,
                            'nama_ruang' => $item->ruang->nama,
                            'status_peminjaman' => $item->statusPeminjaman->nama_status ?? 'Tidak diketahui',
                            'tgl_mulai' => $item->tgl_mulai,
                            'tgl_selesai' => $item->tgl_selesai,
                            'is_returned' => $item->is_returned,
                        ];
                    })
                ];
            })
            ->values();
    
        return response()->json([
            'message' => 'Data jumlah ruang yang sedang dipinjam per user berhasil diambil',
            'data' => $ruangDipinjam
        ]);
    }
    
    public function barangDanRuangDipinjamPerUser()
    {
        $barangDipinjam = PinjamBarang::whereIn('status', [1, 2])
            ->with(['barang', 'user', 'statusPeminjaman']) 
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->user_id,
                    'user' => $item->user,
                    'jenis' => 'barang',
                    'nama' => $item->barang->nama,
                    'status_peminjaman' => $item->statusPeminjaman->nama_status ?? 'Tidak diketahui', 
                    'detail' => $item
                ];
            });
    
        $ruangDipinjam = PinjamRuang::whereIn('status', [1, 2])
            ->with(['ruang', 'user', 'statusPeminjaman']) 
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->user_id,
                    'user' => $item->user,
                    'jenis' => 'ruang',
                    'nama' => $item->ruang->nama,
                    'status_peminjaman' => $item->statusPeminjaman->nama_status ?? 'Tidak diketahui', 
                    'detail' => $item
                ];
            });
    
        $dataDipinjam = $barangDipinjam->merge($ruangDipinjam)
            ->groupBy('user_id')
            ->map(function ($items, $userId) {
                $totalBarang = $items->where('jenis', 'barang')->count();
                $totalRuang = $items->where('jenis', 'ruang')->count();
    
                return [
                    'user' => $items->first()['user'],
                    'total_peminjaman' => [
                        'total_barang' => $totalBarang,
                        'total_ruang' => $totalRuang,
                        'total_semua' => $totalBarang + $totalRuang
                    ],
                    'detail' => $items->values()
                ];
            })
            ->values();
    
        return response()->json([
            'message' => 'Data jumlah barang dan ruang yang sedang dipinjam per user berhasil diambil',
            'data' => $dataDipinjam
        ]);
    }
    
    public function totalDipinjam()
    {
        $totalBarangDipinjam = PinjamBarang::whereIn('status', [1, 2])->count();
        $totalRuangDipinjam = PinjamRuang::whereIn('status', [1, 2])->count();

        return response()->json([
            'message' => 'Total barang dan ruang yang sedang dipinjam berhasil dihitung',
            'total_barang_dipinjam' => $totalBarangDipinjam,
            'total_ruang_dipinjam' => $totalRuangDipinjam,
            'total_semua' => $totalBarangDipinjam + $totalRuangDipinjam
        ]);
    }

    public function getStatus()
    {
        $statusBarang = StatusBarang::all(['id', 'nama_status']);
        $statusRuang = StatusRuang::all(['id', 'nama_status']);
        $statusPeminjaman = StatusPeminjaman::all(['id', 'nama_status']);

        return response()->json([
            'message' => 'Data status berhasil diambil',
            'status_barang' => $statusBarang,
            'status_ruang' => $statusRuang,
            'status_peminjaman' => $statusPeminjaman
        ]);
    }

}
