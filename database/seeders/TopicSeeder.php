<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('topic')->insert([
            [
                'name' => 'Kampus',
                'description' => 'Topik mengenai kampus',
                'icon' => url('_topics') . '/' . 'graduate.png'
            ],
            [
                'name' => 'Komunitas',
                'description' => 'Ada banyak pembahasan tentang komunitas disini',
                'icon' => url('_topics') . '/' . 'community-manager.png'
            ],
            [
                'name' => 'Curhat',
                'description' => 'Banyak cerita yang sama dengan ceritamu disini',
                'icon' => url('_topics') . '/' . 'slight-smile.png'
            ],
            [
                'name' => 'Jokes',
                'description' => 'Balikin mood dengan baca2 jokes disini',
                'icon' => url('_topics') . '/' . 'laughing.png'
            ],
            [
                'name' => 'Olahraga',
                'description' => 'Mampir disini kalau kamu suka olahraga',
                'icon' => url('_topics') . '/' . 'person.png'
            ],
            [
                'name' => 'Pelajaran atau Tugas',
                'description' => 'Diskusikan pelajaran kamu disini',
                'icon' => url('_topics') . '/' . 'learning-idea.png'
            ]
        ]);
    }
}
