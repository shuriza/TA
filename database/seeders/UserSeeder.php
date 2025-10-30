<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@polinema.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'timezone' => 'Asia/Jakarta',
        ]);
        $admin->assignRole('admin');

        // Create Dosen
        $dosen1 = User::create([
            'name' => 'Dr. Budi Santoso, M.Kom',
            'email' => 'budi.santoso@polinema.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'nip' => '198501152010121001',
            'timezone' => 'Asia/Jakarta',
        ]);
        $dosen1->assignRole('dosen');

        $dosen2 = User::create([
            'name' => 'Dewi Kartika, S.Kom, M.T',
            'email' => 'dewi.kartika@polinema.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'nip' => '199003202015042001',
            'timezone' => 'Asia/Jakarta',
        ]);
        $dosen2->assignRole('dosen');

        $dosen3 = User::create([
            'name' => 'Ahmad Rizky, S.T, M.Sc',
            'email' => 'ahmad.rizky@polinema.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'nip' => '198808152012121002',
            'timezone' => 'Asia/Jakarta',
        ]);
        $dosen3->assignRole('dosen');

        // Create Mahasiswa
        $mahasiswa1 = User::create([
            'name' => 'Andi Pratama',
            'email' => 'andi.pratama@students.polinema.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'nim' => '2241760001',
            'timezone' => 'Asia/Jakarta',
        ]);
        $mahasiswa1->assignRole('mahasiswa');

        $mahasiswa2 = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti.nurhaliza@students.polinema.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'nim' => '2241760002',
            'timezone' => 'Asia/Jakarta',
        ]);
        $mahasiswa2->assignRole('mahasiswa');

        $mahasiswa3 = User::create([
            'name' => 'Rizki Ramadan',
            'email' => 'rizki.ramadan@students.polinema.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'nim' => '2241760003',
            'timezone' => 'Asia/Jakarta',
        ]);
        $mahasiswa3->assignRole('mahasiswa');

        $mahasiswa4 = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi.lestari@students.polinema.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'nim' => '2241760004',
            'timezone' => 'Asia/Jakarta',
        ]);
        $mahasiswa4->assignRole('mahasiswa');

        $mahasiswa5 = User::create([
            'name' => 'Fajar Nugroho',
            'email' => 'fajar.nugroho@students.polinema.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'nim' => '2241760005',
            'timezone' => 'Asia/Jakarta',
        ]);
        $mahasiswa5->assignRole('mahasiswa');
    }
}
