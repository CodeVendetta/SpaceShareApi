<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as FacadesDB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FacadesDB::table('users')->insert([
            'nama' => 'Nur Bashori',
            'nim' => '222410102036',
            'prodi' => 'Teknologi Informasi',
            'email' => 'user1@email.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
        FacadesDB::table('users')->insert([
            'nama' => 'Azril cengo',
            'nim' => '222410102037',
            'prodi' => 'Teknologi Informasi',
            'email' => 'user2@email.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }
}
