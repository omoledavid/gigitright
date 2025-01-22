<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'id' => 1,
                'name' => 'Programming',
                'description' => 'Development jobs',
                'parent_id' => null,
                'slug' => 'programming',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Web Development',
                'description' => 'Web-based applications',
                'parent_id' => 1,
                'slug' => 'web-development',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'name' => 'Graphic Design',
                'description' => 'Creative design jobs',
                'parent_id' => null,
                'slug' => 'graphic-design',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'name' => 'Logo Design',
                'description' => 'Designing logos',
                'parent_id' => 3,
                'slug' => 'logo-design',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'name' => 'Mobile Apps',
                'description' => 'Mobile application dev',
                'parent_id' => 1,
                'slug' => 'mobile-apps',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
