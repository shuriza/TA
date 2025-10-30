<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dosen = User::where('role', 'dosen')->get();
        $mahasiswa = User::where('role', 'mahasiswa')->get();

        // Course 1: Pemrograman Web
        $course1 = Course::create([
            'code' => 'TI-2A',
            'name' => 'Pemrograman Web',
            'lecturer_id' => $dosen[0]->id,
            'semester' => '2024/2025 Genap',
            'class' => '2A',
            'description' => 'Mata kuliah pemrograman web menggunakan Laravel framework',
            'color' => '#3B82F6',
            'is_active' => true,
        ]);
        $course1->students()->attach($mahasiswa->pluck('id'));

        // Course 2: Basis Data
        $course2 = Course::create([
            'code' => 'TI-2B',
            'name' => 'Basis Data',
            'lecturer_id' => $dosen[1]->id,
            'semester' => '2024/2025 Genap',
            'class' => '2B',
            'description' => 'Mata kuliah dasar-dasar basis data dan SQL',
            'color' => '#10B981',
            'is_active' => true,
        ]);
        $course2->students()->attach($mahasiswa->pluck('id'));

        // Course 3: Algoritma dan Struktur Data
        $course3 = Course::create([
            'code' => 'TI-2C',
            'name' => 'Algoritma dan Struktur Data',
            'lecturer_id' => $dosen[2]->id,
            'semester' => '2024/2025 Genap',
            'class' => '2C',
            'description' => 'Mata kuliah algoritma dan struktur data lanjutan',
            'color' => '#F59E0B',
            'is_active' => true,
        ]);
        $course3->students()->attach($mahasiswa->pluck('id'));

        // Course 4: Pemrograman Mobile
        $course4 = Course::create([
            'code' => 'TI-3A',
            'name' => 'Pemrograman Mobile',
            'lecturer_id' => $dosen[0]->id,
            'semester' => '2024/2025 Genap',
            'class' => '3A',
            'description' => 'Mata kuliah pengembangan aplikasi mobile dengan Flutter',
            'color' => '#8B5CF6',
            'is_active' => true,
        ]);
        $course4->students()->attach($mahasiswa->take(3)->pluck('id'));

        // Course 5: Kecerdasan Buatan
        $course5 = Course::create([
            'code' => 'TI-3B',
            'name' => 'Kecerdasan Buatan',
            'lecturer_id' => $dosen[1]->id,
            'semester' => '2024/2025 Genap',
            'class' => '3B',
            'description' => 'Mata kuliah dasar-dasar AI dan Machine Learning',
            'color' => '#EF4444',
            'is_active' => true,
        ]);
        $course5->students()->attach($mahasiswa->skip(2)->pluck('id'));
    }
}
