<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('faculty')->insert([
            ['code' => 'FAGRI', 'name' => 'Fakultas Agroindustri'],
            ['code' => 'FAEKN', 'name' => 'Fakultas Ekonomi'],
            ['code' => 'FAPSI', 'name' => 'Fakultas Psikologi'],
            ['code' => 'FATIF', 'name' => 'Fakultas Teknologi Informasi'],
            ['code' => 'FAKIP', 'name' => 'Fakultas Keguruan dan ilmu pendidikan'],
            ['code' => 'FAIKS', 'name' => 'Fakultas Ilmu Komunikasi']
        ]);
    }
}
