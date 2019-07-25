<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AddSeeder::class);
    }
}

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
            'status' =>'0',
            'image' => 'default.jpg',
        ]);
    }

}


