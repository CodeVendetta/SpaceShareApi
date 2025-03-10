<?php

namespace App\Http\Controllers;

use App\Models\PinjamBarang;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PeminjamanBarangController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'tgl_mulai' => 'required|date|after_or_equal:today',
            'tgl_selesai' => 'required|date|after:tgl_mulai',
        ]);

        $barang = Barang::findOrFail($request->barang_id);

        if ($barang->status != 1) {
            return response()->json(['message' => 'Barang tidak tersedia untuk dipinjam'], 400);
        }

        $peminjaman = PinjamBarang::create([
            'barang_id' => $request->barang_id,
            'user_id' => Auth::id(),
            'admin_id' => null,
            'tgl_mulai' => $request->tgl_mulai,
            'tgl_selesai' => $request->tgl_selesai,
            'qty' => $request->qty,
            'status' => 1,
            'is_returned' => false,
        ]);

        return response()->json([
            'message' => 'Peminjaman barang berhasil diajukan, menunggu persetujuan admin',
            'data' => $peminjaman,
        ], 201);
    }

    public function requestReturnBarang(Request $request, $id)
    {
        $peminjaman = PinjamBarang::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 2)
            ->where('is_returned', false)
            ->first();

        if (!$peminjaman) {
            return response()->json(['message' => 'Peminjaman tidak ditemukan atau tidak bisa dikembalikan'], 404);
        }

        $peminjaman->update([
            'status' => 4,
        ]);

        return response()->json([
            'message' => 'Pengajuan pengembalian barang berhasil, menunggu konfirmasi admin',
            'data' => $peminjaman,
        ]);
    }

    public function approveRejectReturnBarang(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:2,5',
        ]);

        $peminjaman = PinjamBarang::where('id', $id)
            ->where('status', 4)
            ->first();

        if (!$peminjaman) {
            return response()->json(['message' => 'Pengembalian tidak ditemukan atau sudah diproses'], 404);
        }

        $peminjaman->update([
            'status' => $request->status,
            'is_returned' => $request->status == 5,
        ]);

        return response()->json([
            'message' => $request->status == 5 ? 'Pengembalian barang berhasil dikonfirmasi' : 'Pengembalian barang ditolak',
            'data' => $peminjaman,
        ]);
    }
}
