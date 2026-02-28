<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->where('email','=','superadmin@gmail.com')->delete();
        $user =  User::query()->create([
            'name' => 'Administrator',
            'email'=>'superadmin@gmail.com',
            'password'=> bcrypt('w8?i1-3[GJ1('),
            'contacts'=>'0712419949',
            'profession'=>'Web Developer',
        ]);

        $user->assignRole('super-admin');


    }
}
