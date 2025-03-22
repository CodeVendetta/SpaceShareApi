<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Auth;


class DaftarBarangController extends Controller
{
    public function index()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'message' => 'Unauthorized, please login first.'
                ], 401);
            }

            $barang = Barang::with('statusBarang')->get();

            return response()->json([
                'message' => 'Daftar barang berhasil diambil',
                'data' => $barang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function countBarang()
    {
        try {
            $totalBarang = Barang::count();

            return response()->json([
                'message' => 'Total jumlah barang berhasil diambil',
                'total_barang' => $totalBarang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getBarangById($id)
    {
        $barangbyid = Barang::get()->where('id', $id)->first();
        return response()->json([
            'message' => 'Detail berhasil diambil',
            'data' => $barangbyid
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'stok' => 'required|integer|min:1',
            'status' => 'required|exists:status_barang,id',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();
        $data['admin_id'] = auth()->id();

        $lastBarang = Barang::latest('id')->first();
        if ($lastBarang) {
            preg_match('/\d+$/', $lastBarang->nomor, $matches);
            $lastNumber = $matches ? (int) $matches[0] : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $data['nomor'] = 'BRG' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        if ($request->hasFile('foto')) {
            try {
                $uploadedFile = Cloudinary::upload($request->file('foto')->getRealPath(), [
                    'folder' => 'barang_foto'
                ])->getSecurePath();

                $data['foto'] = $uploadedFile;
            } catch (\Exception $e) {
                return response()->json(['error' => 'Gagal upload ke Cloudinary', 'message' => $e->getMessage()], 500);
            }
        }

        $barang = Barang::create($data);

        return response()->json([
            'message' => 'Barang berhasil ditambahkan',
            'data' => $barang,
        ], 201);
    }
}
