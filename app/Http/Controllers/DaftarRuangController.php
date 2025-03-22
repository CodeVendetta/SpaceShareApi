<?php

namespace App\Http\Controllers;

use App\Models\Ruang;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class DaftarRuangController extends Controller
{
    public function index()
    {
        $ruang = Ruang::with('statusRuang')->get();

        return response()->json([
            'message' => 'Daftar ruang berhasil diambil',
            'data' => $ruang
        ]);
    }

    public function countRuang()
    {
        try {
            $totalRuang = Ruang::count();

            return response()->json([
                'message' => 'Total jumlah ruang berhasil diambil',
                'total_ruang' => $totalRuang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getRuangById($id)
    {
        $ruangbyid = Ruang::get()->where('id', $id)->first();
        return response()->json([
            'message' => 'Detail berhasil diambil',
            'data' => $ruangbyid
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'status' => 'required|exists:status_ruang,id',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();
        $data['admin_id'] = auth()->id();

        $lastRuang = Ruang::latest('id')->first();
        if ($lastRuang) {
            preg_match('/\d+$/', $lastRuang->nomor, $matches);
            $lastNumber = $matches ? (int)$matches[0] : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $data['nomor'] = 'RG' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        if ($request->hasFile('foto')) {
            try {
                $uploadedFile = Cloudinary::upload($request->file('foto')->getRealPath(), [
                    'folder' => 'ruang_foto'
                ])->getSecurePath();

                $data['foto'] = $uploadedFile;
            } catch (\Exception $e) {
                return response()->json(['error' => 'Gagal upload ke Cloudinary', 'message' => $e->getMessage()], 500);
            }
        }

        $ruang = Ruang::create($data);

        return response()->json([
            'message' => 'Ruang berhasil ditambahkan',
            'data' => $ruang,
        ], 201);
    }
}
