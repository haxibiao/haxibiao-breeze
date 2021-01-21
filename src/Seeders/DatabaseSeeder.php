<?php
namespace Haxibiao\Breeze\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return   void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(SiteSeeder::class);
        $this->call(SeoSeeder::class);
        $this->call(AsoSeeder::class);
    }
}
