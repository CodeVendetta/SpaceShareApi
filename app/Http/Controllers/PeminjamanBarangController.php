<?php

namespace App\Http\Controllers;

use App\Models\PinjamBarang;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeminjamanBarangController extends Controller
{
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            // $request->validate([
            //     'barang_id' => 'required|exists:barang,id',
            // ]);

            $today = now()->format('Y-m-d');
            
            if ($request->tgl_mulai < $today || $request->tgl_selesai < $today && $request->qty < 1) {
                return response()->json(['message' => 'Tanggal tidak boleh kurang dari hari ini dan Jumlah tidak valid'], 400);
            }

            if (empty($request->tgl_mulai) || empty($request->tgl_selesai) && $request->qty < 1) {
                return response()->json(['message' => 'Tanggal tidak boleh kosong dan Jumlah tidak valid'], 400);
            }

            if (empty($request->tgl_mulai) || empty($request->tgl_selesai)) {
                return response()->json(['message' => 'Tanggal tidak boleh kosong'], 400);
            }

            if ($request->tgl_mulai < $today || $request->tgl_selesai < $today) {
                return response()->json(['message' => 'Tanggal tidak boleh kurang dari hari ini'], 400);
            }

            if ($request->barang_id <= 0) {
                return response()->json(['message' => 'Barang tidak valid/tidak ada'], 404);
            }

            $barang = Barang::findOrFail($request->barang_id);

            if ($barang->stok < $request->qty) {
                return response()->json(['message' => 'Stok tidak mencukupi'], 400);
            }

            if ($request->qty < 1) {
                return response()->json(['message' => 'Jumlah tidak valid'], 400);
            }

            if ($barang->status != 1) {
                return response()->json(['message' => 'Barang tidak tersedia untuk dipinjam'], 400);
            }

            $existingPeminjaman = PinjamBarang::where('barang_id', $request->barang_id)
                ->whereIn('status', [1, 2]) 
                ->where(function ($query) use ($request) {
                    $query->whereBetween('tgl_mulai', [$request->tgl_mulai, $request->tgl_selesai])
                        ->orWhereBetween('tgl_selesai', [$request->tgl_mulai, $request->tgl_selesai])
                        ->orWhere(function ($query) use ($request) {
                            $query->where('tgl_mulai', '<=', $request->tgl_mulai)
                                ->where('tgl_selesai', '>=', $request->tgl_selesai);
                        });
                })
                ->sum('qty');

            $returnedStock = PinjamBarang::where('barang_id', $request->barang_id)
                ->where('status', 5) 
                ->whereBetween('tgl_selesai', [$request->tgl_mulai, $request->tgl_selesai])
                ->sum('qty');

            $rejectedStock = PinjamBarang::where('barang_id', $request->barang_id)
                ->where('status', 3)
                ->whereBetween('tgl_mulai', [$request->tgl_mulai, $request->tgl_selesai])
                ->sum('qty');

            $availableStock = ($barang->stok - $existingPeminjaman) + $returnedStock + $rejectedStock;

            if ($availableStock < $request->qty) {
                return response()->json(['message' => existingPeminjaman], 400);
            }

            $peminjaman = PinjamBarang::create([
                'barang_id' => $request->barang_id,
                'user_id' => Auth::id(),
                'admin_id' => 1,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'qty' => $request->qty,
                'status' => 1,
                'is_returned' => false,
            ]);

            $barang->stok -= $request->qty;

            if ($barang->stok == 0) {
                $barang->status = 3;
            }

            $barang->save();

            DB::commit();

            return response()->json([
                'message' => 'Peminjaman barang berhasil diajukan, menunggu persetujuan admin',
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

        DB::beginTransaction();
        try {
            $peminjaman = PinjamBarang::where('id', $id)
                ->where('status', 4)
                ->first();

            if (!$peminjaman) {
                return response()->json(['message' => 'Pengembalian tidak ditemukan atau sudah diproses'], 404);
            }

            $barang = Barang::findOrFail($peminjaman->barang_id);

            if ($request->status == 5) {
                $barang->stok += $peminjaman->qty;

                if ($barang->status == 3) {
                    $barang->status = 1;
                }

                $barang->save();
            }

            $peminjaman->update([
                'status' => $request->status,
                'is_returned' => $request->status == 5,
            ]);

            DB::commit();

            return response()->json([
                'message' => $request->status == 5 ? 'Pengembalian barang berhasil dikonfirmasi' : 'Pengembalian barang ditolak',
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

    public function approveRejectPeminjamanBarang(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:2,3',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = PinjamBarang::where('id', $id)
                ->where('status', 1)
                ->first();

            if (!$peminjaman) {
                return response()->json(['message' => 'Peminjaman tidak ditemukan atau sudah diproses'], 404);
            }

            $barang = Barang::findOrFail($peminjaman->barang_id);

            if ($request->status == 3) {
                $barang->stok += $peminjaman->qty;

                if ($barang->status == 3) {
                    $barang->status = 1;
                }

                $barang->save();
            }

            $peminjaman->update([
                'status' => $request->status,
                'admin_id' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => $request->status == 2 ? 'Peminjaman barang disetujui' : 'Peminjaman barang ditolak',
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

    public function historyPeminjamanBarang()
    {
        $userId = Auth::id();

        $history = PinjamBarang::where('user_id', $userId)
            ->with(['barang', 'statusPeminjaman'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Riwayat peminjaman barang berhasil diambil',
            'data' => $history,
        ]);
    }

    public function historyPeminjamanBarangReturned()
    {
        $userId = Auth::id();

        $history = PinjamBarang::where('user_id', $userId)
            ->where('status', 5)
            ->with(['barang', 'statusPeminjaman'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Riwayat barang yang telah dikembalikan berhasil diambil',
            'data' => $history,
        ]);
    }
}
