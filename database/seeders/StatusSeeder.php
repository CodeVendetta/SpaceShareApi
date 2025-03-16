<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('status_barang')->insert([
            ['id' => 1, 'nama' => 'Tersedia'],
            ['id' => 2, 'nama' => 'Dipinjam'],
            ['id' => 3, 'nama' => 'Rusak'],
            ['id' => 4, 'nama' => 'Hilang'],
        ]);

        DB::table('status_ruang')->insert([
            ['id' => 1, 'nama' => 'Tersedia'],
            ['id' => 2, 'nama' => 'Sedang Digunakan'],
            ['id' => 3, 'nama' => 'Dalam Perbaikan'],
        ]);

        DB::table('status_peminjaman')->insert([
            ['id' => 1, 'nama' => 'Menunggu Persetujuan'],
            ['id' => 2, 'nama' => 'Disetujui'],
            ['id' => 3, 'nama' => 'Ditolak'],
            ['id' => 4, 'nama' => 'Menunggu Konfirmasi Pengembalian'],
            ['id' => 5, 'nama' => 'Sudah Dikembalikan'],
        ]);
    }
}
