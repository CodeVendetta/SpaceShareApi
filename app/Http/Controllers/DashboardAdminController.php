<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PinjamBarang;
use App\Models\PinjamRuang;

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
            ->with(['barang', 'user'])
            ->get()
            ->groupBy('user_id')
            ->map(function ($items, $userId) {
                return [
                    'user' => $items->first()->user,
                    'total_barang_dipinjam' => $items->count(),
                    'detail' => $items
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Data jumlah barang yang sedang dipinjam per user berhasil diambil',
            'data' => $barangDipinjam
        ]);
    }

    public function ruangDipinjamPerUser()
    {
        $ruangDipinjam = PinjamRuang::whereIn('status', [1, 2])
            ->with(['ruang', 'user'])
            ->get()
            ->groupBy('user_id')
            ->map(function ($items, $userId) {
                return [
                    'user' => $items->first()->user,
                    'total_ruang_dipinjam' => $items->count(),
                    'detail' => $items
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Data jumlah ruang yang sedang dipinjam per user berhasil diambil',
            'data' => $ruangDipinjam
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
}
