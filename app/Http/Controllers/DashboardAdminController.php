<?php

namespace App\Http\Controllers;

use App\Models\User;

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
    
    public function allBarangDipinjam()
    {
        $barangDipinjam = PinjamBarang::whereIn('status', [1, 2])
            ->with(['barang', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Data semua barang yang sedang dipinjam berhasil diambil',
            'data' => $barangDipinjam
        ]);
    }

    public function allRuangDipinjam()
    {
        $ruangDipinjam = PinjamRuang::whereIn('status', [1, 2]) 
            ->with(['ruang', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Data semua ruang yang sedang dipinjam berhasil diambil',
            'data' => $ruangDipinjam
        ]);
    }
}

