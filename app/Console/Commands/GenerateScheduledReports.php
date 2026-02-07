<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportSchedule;
use App\Services\ReportService;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReportGenerated;
use Carbon\Carbon;

class GenerateScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate-scheduled 
                            {--force : Force generation of all active schedules}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and email scheduled reports that are due';

    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for scheduled reports...');

        // Get due schedules
        $query = ReportSchedule::with(['template', 'template.vendor'])
            ->where('is_active', true);

        if (!$this->option('force')) {
            $query->where('next_run_at', '<=', now());
        }

        $schedules = $query->get();

        if ($schedules->isEmpty()) {
            $this->info('✓ No scheduled reports due at this time.');
            return 0;
        }

        $this->info("📊 Found {$schedules->count()} scheduled report(s) to generate");

        $successCount = 0;
        $failCount = 0;

        foreach ($schedules as $schedule) {
            try {
                $this->line("  Processing: {$schedule->template->name}");
                
                // Generate report
                $reportData = $this->reportService->generateReport($schedule->template);
                
                // Export to configured format
                $format = $schedule->format ?? 'pdf';
                $result = $this->reportService->exportReport($reportData, $format, $schedule->template);
                
                // Send email to recipients
                if ($schedule->recipients && is_array($schedule->recipients)) {
                    foreach ($schedule->recipients as $recipient) {
                        Mail::to($recipient)->send(
                            new ReportGenerated($result['report'], $schedule->template)
                        );
                    }
                    $this->info("  ✓ Sent to " . count($schedule->recipients) . " recipient(s)");
                }
                
                // Update next run time
                $schedule->last_run_at = now();
                $schedule->calculateNextRun();
                $schedule->save();
                
                $successCount++;
                $this->info("  ✓ Generated: {$schedule->template->name}");
                
            } catch (\Exception $e) {
                $failCount++;
                $this->error("  ✗ Failed: {$schedule->template->name}");
                $this->error("    Error: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("📈 Summary:");
        $this->info("  ✓ Success: {$successCount}");
        if ($failCount > 0) {
            $this->error("  ✗ Failed: {$failCount}");
        }
        $this->info("✅ Scheduled report generation complete!");

        return 0;
    }
}
