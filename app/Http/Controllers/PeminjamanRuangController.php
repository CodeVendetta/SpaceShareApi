<?php

namespace App\Http\Controllers;

use App\Models\PinjamRuang;
use App\Models\Ruang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeminjamanRuangController extends Controller
{
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ruang_id' => 'required|exists:ruang,id',
            ]);

            if (empty($request->tgl_mulai) || empty($request->tgl_selesai)) {
                return response()->json(['message' => 'Tanggal tidak boleh kosong'], 400);
            }

            $today = now()->format('Y-m-d');
            if ($request->tgl_mulai < $today || $request->tgl_selesai < $today) {
                return response()->json(['message' => 'Tanggal tidak boleh kurang dari hari ini'], 400);
            }

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
                'status' => 1,
                'is_returned' => false,
            ]);

            $ruang->save();

            DB::commit();

            return response()->json([
                'message' => 'Peminjaman ruang berhasil diajukan, menunggu persetujuan admin',
                'data' => $peminjaman,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
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

        DB::beginTransaction();
        try {
            $peminjaman = PinjamRuang::where('id', $id)
                ->where('status', 4)
                ->first();

            if (!$peminjaman) {
                return response()->json(['message' => 'Pengembalian tidak ditemukan atau sudah diproses'], 404);
            }

            $ruang = Ruang::findOrFail($peminjaman->ruang_id);

            if ($request->status == 5) {
                $ruang->stok += $peminjaman->qty;

                if ($ruang->status == 3) {
                    $ruang->status = 1;
                }

                $ruang->save();
            }

            $peminjaman->update([
                'status' => $request->status,
                'is_returned' => $request->status == 5,
            ]);

            DB::commit();

            return response()->json([
                'message' => $request->status == 5 ? 'Pengembalian ruang berhasil dikonfirmasi' : 'Pengembalian ruang ditolak',
                'data' => $peminjaman,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approveRejectPeminjamanRuang(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:2,3',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = PinjamRuang::where('id', $id)
                ->where('status', 1)
                ->first();

            if (!$peminjaman) {
                return response()->json(['message' => 'Peminjaman tidak ditemukan atau sudah diproses'], 404);
            }

            $ruang = Ruang::findOrFail($peminjaman->ruang_id);

            if ($request->status == 3) {
                $ruang->stok += $peminjaman->qty;

                if ($ruang->status == 3) {
                    $ruang->status = 1;
                }

                $ruang->save();
            }

            $peminjaman->update([
                'status' => $request->status,
                'admin_id' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => $request->status == 2 ? 'Peminjaman ruang disetujui' : 'Peminjaman ruang ditolak',
                'data' => $peminjaman,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
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

    public function historyPeminjamanRuangReturned()
    {
        $userId = Auth::id();

        $history = PinjamRuang::where('user_id', $userId)
            ->where('status', 5)
            ->with(['ruang', 'statusPeminjaman'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Riwayat ruang yang telah dikembalikan berhasil diambil',
            'data' => $history,
        ]);
    }
}
