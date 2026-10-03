<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'mahasiswa@kampus.ac.id'],
            [
                'name' => 'Budi Pratama',
                'password' => Hash::make('password123'),
            ]
        );

        // Create sample study folders
        $folders = [
            ['name' => 'Kecerdasan Buatan & RAG', 'color' => '#6366f1'],
            ['name' => 'Struktur Data & Algoritma', 'color' => '#10b981'],
            ['name' => 'Sistem Basis Data Lanjut', 'color' => '#f59e0b'],
            ['name' => 'Rekayasa Perangkat Lunak', 'color' => '#ec4899'],
        ];

        foreach ($folders as $f) {
            Folder::firstOrCreate(
                ['user_id' => $user->id, 'name' => $f['name']],
                ['color' => $f['color']]
            );
        }
    }
}
