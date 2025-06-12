<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $pages = [
            [
                'page_name' => 'About',
                'page_key' => 'about',
                'content' => json_encode([
                    'title' => 'About Us',
                    'subtitle' => 'Everything you need to know',
                    'description' => 'We are dedicated towards serving you',
                    'body' => 'body content',
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'page_name' => 'Privacy',
                'page_key' => 'privacy',
                'content' => json_encode([
                    'title' => 'Privacy',
                    'subtitle' => 'Privacy subtitle',
                    'description' => 'Privacy description',
                    'body' => 'body content',
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'page_name' => 'Terms and Condition',
                'page_key' => 'terms',
                'content' => json_encode([
                    'title' => 'Terms and Condition',
                    'subtitle' => 'Terms subtitle',
                    'description' => 'Terms description',
                    'body' => 'body content',
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        DB::table('pages')->insert($pages);
    }
}
