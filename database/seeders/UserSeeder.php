<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                "full_name" => "Barrie Lawfull",
                "username" => "blawfull0",
                "faculty" => "Mellivora capensis",
                "major" => "VP Sales",
                "year_class" => 2005,
                "avatar" => "https://robohash.org/recusandaequaset.png?size=50x50&set=set1",
                "gender" => "Male",
                "email" => "femaleone@mail.com",
                "password" => Hash::make('12345')
            ],
            [
                "full_name" => "Arlee Kibbey",
                "username" => "akibbey1",
                "faculty" => "Tringa glareola",
                "major" => "Paralegal",
                "year_class" => 1989,
                "avatar" => "https://robohash.org/laborumreiciendisenim.png?size=50x50&set=set1",
                "gender" => "Male",
                "email" => "femaletwo@mail.com",
                "password" => Hash::make('12345')
            ],
        ]);
    }
}
