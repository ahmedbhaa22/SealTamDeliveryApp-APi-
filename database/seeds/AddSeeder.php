<?php

use Illuminate\Database\Seeder;

class AddSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = \App\Add::create([
            'name' => 'First Add',
            'status' =>'1',
            'image' => 'default.jpg',
        ]);
    }
}
