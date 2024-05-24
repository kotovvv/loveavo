<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// use DB;
use Illuminate\Support\Facades\DB;
// use Hash;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (!DB::table('roles')->where('name', 'Administrator')->value('id')) {
            DB::table('roles')->insert([
                ['name' => 'Administrator'],
                ['name' => 'CRM manager'],
                ['name' => 'Manager']
            ]);
        }
        $role_id = DB::table('roles')->where('name', 'Administrator')->value('id');

        DB::table('users')->insert([
            [
                'name' => 'lara',
                'password' => Hash::make('lara'),
                'role_id' => $role_id,
                'fio' => 'Admin Adminich',
                'active' => 1
            ],
            // [
            //     'name' => 'larac',
            //     'password' => Hash::make('lara'),
            //     'role_id' => 2,
            //     'fio' => 'CRM Manager',
            //     'active' => 1
            // ],
            // [
            //     'name' => 'laram',
            //     'password' => Hash::make('lara'),
            //     'role_id' => 3,
            //     'fio' => 'Manager',
            //     'active' => 1
            // ],

        ]);
        // DB::table('providers')->insert([
        //     [
        //         'name' => 'Provider 1',
        //         'active' => 1
        //     ],
        //     [
        //         'name' => 'Provider 2',
        //         'active' => 1
        //     ],
        //     [
        //         'name' => 'Provider 3',
        //         'active' => 1
        //     ],
        // ]);
        // DB::table('statuses')->insert([
        //     [
        //         'name' => 'Interesting',
        //         'active' => 1
        //     ],
        //     [
        //         'name' => 'Don`t disturb',
        //         'active' => 1
        //     ],
        //     [
        //         'name' => 'No ansver',
        //         'active' => 1
        //     ],
        //     [
        //         'name' => 'Call next time',
        //         'active' => 1
        //     ],
        //     [
        //         'name' => 'ReCall beasy',
        //         'active' => 1
        //     ],
        // ]);
    }
}
