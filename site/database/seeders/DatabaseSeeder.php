<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            CoursesTableSeeder::class,
            FoldersTableSeeder::class,
            CardsTableSeeder::class,
            InvitationsTableSeeder::class,
            FilesTableSeeder::class,
            StatesTableSeeder::class,
            TagsTableSeeder::class,
        ]);
    }
}
