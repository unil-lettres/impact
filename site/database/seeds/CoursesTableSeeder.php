<?php

use Illuminate\Database\Seeder;

class CoursesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        DB::table('courses')->insert([
            'name' => 'First space',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null
        ]);

        DB::table('courses')->insert([
            'name' => 'Second space',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null
        ]);

        DB::table('courses')->insert([
            'name' => 'Deactivated space',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => $now
        ]);
    }
}
