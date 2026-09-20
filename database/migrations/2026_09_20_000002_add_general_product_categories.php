<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            ['name' => 'Elektronik', 'slug' => 'elektronik'],
            ['name' => 'Rumah Tangga', 'slug' => 'rumah-tangga'],
            ['name' => 'Kesehatan & Kecantikan', 'slug' => 'kesehatan-kecantikan'],
            ['name' => 'Hobi & Mainan', 'slug' => 'hobi-mainan'],
            ['name' => 'Makanan & Minuman', 'slug' => 'makanan-minuman'],
            ['name' => 'Kantor & Sekolah', 'slug' => 'kantor-sekolah'],
        ];

        foreach ($categories as $category) {
            if (!DB::table('categories')->where('slug', $category['slug'])->exists()) {
                DB::table('categories')->insert([
                    'id' => (string) Str::uuid(),
                    'slug' => $category['slug'],
                    'name' => $category['name'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('categories')->whereIn('slug', [
            'elektronik',
            'rumah-tangga',
            'kesehatan-kecantikan',
            'hobi-mainan',
            'makanan-minuman',
            'kantor-sekolah',
        ])->delete();
    }
};
