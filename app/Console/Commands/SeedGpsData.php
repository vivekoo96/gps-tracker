<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\GpsDataSeeder;

class SeedGpsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gps:seed-data {--fresh : Clear existing GPS data first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed GPS tracking data for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->info('Clearing existing GPS data...');
            \App\Models\GpsData::truncate();
        }

        $this->info('Seeding GPS data...');
        
        $seeder = new GpsDataSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('GPS data seeded successfully!');
        
        $count = \App\Models\GpsData::count();
        $this->info("Total GPS records: {$count}");
        
        return 0;
    }
}
