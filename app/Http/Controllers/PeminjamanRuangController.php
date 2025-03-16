<?php

namespace App\Http\Controllers;

use App\Models\PinjamRuang;
use App\Models\Ruang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PeminjamanRuangController extends Controller
{
    public function create(Request $request)
    {
        try {
            $request->validate([
                'ruang_id' => 'required|exists:ruang,id',
                'tgl_mulai' => 'required|date|after_or_equal:today',
                'tgl_selesai' => 'required|date|after:tgl_mulai',
            ]);

            $ruang = Ruang::findOrFail($request->ruang_id);

            if ($ruang->status != 1) {
                return response()->json(['message' => 'Ruang tidak tersedia untuk dipinjam'], 400);
            }

            $peminjaman = PinjamRuang::create([
                'ruang_id' => $request->ruang_id,
                'user_id' => Auth::id(),
                'admin_id' => 1,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'qty' => $request->qty,
                'status' => 1,
                'is_returned' => false,
            ]);

            return response()->json([
                'message' => 'Peminjaman ruang berhasil diajukan, menunggu persetujuan admin',
                'data' => $peminjaman,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function requestReturnRuang(Request $request, $id)
    {
        $peminjaman = PinjamRuang::where('id', $id)
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
            'message' => 'Pengajuan pengembalian ruang berhasil, menunggu konfirmasi admin',
            'data' => $peminjaman,
        ]);
    }

    public function approveRejectReturnRuang(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:2,5',
        ]);

        $peminjaman = PinjamRuang::where('id', $id)
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
            'message' => $request->status == 5 ? 'Pengembalian ruang berhasil dikonfirmasi' : 'Pengembalian ruang ditolak',
            'data' => $peminjaman,
        ]);
    }

    public function approveRejectPeminjamanRuang(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:2,3',
        ]);

        $peminjaman = PinjamRuang::where('id', $id)
            ->where('status', 1)
            ->first();

        if (!$peminjaman) {
            return response()->json(['message' => 'Peminjaman tidak ditemukan atau sudah diproses'], 404);
        }

        $peminjaman->update([
            'status' => $request->status,
            'admin_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => $request->status == 2 ? 'Peminjaman ruang disetujui' : 'Peminjaman ruang ditolak',
            'data' => $peminjaman,
        ]);
    }

    public function historyPeminjamanRuang()
    {
        $userId = Auth::id();

        $history = PinjamRuang::where('user_id', $userId)
            ->with(['ruang', 'statusPeminjaman'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Riwayat peminjaman ruang berhasil diambil',
            'data' => $history,
        ]);
    }
}
