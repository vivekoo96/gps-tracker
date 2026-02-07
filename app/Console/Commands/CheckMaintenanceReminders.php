<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MaintenanceReminderService;
use App\Models\Vendor;

class CheckMaintenanceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:check-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and generate maintenance reminders for all vendors';

    protected $reminderService;

    /**
     * Create a new command instance.
     */
    public function __construct(MaintenanceReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking maintenance reminders...');

        $vendors = Vendor::all();
        $totalReminders = 0;

        foreach ($vendors as $vendor) {
            $this->info("Processing vendor: {$vendor->name}");
            
            $remindersCreated = $this->reminderService->generateReminders($vendor->id);
            $totalReminders += $remindersCreated;

            $this->info("  - Created {$remindersCreated} reminders");
        }

        $this->info("Total reminders created: {$totalReminders}");
        $this->info('Maintenance reminder check completed!');

        return 0;
    }
}
