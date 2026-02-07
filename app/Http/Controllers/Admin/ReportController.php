<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportTemplate;
use App\Models\GeneratedReport;
use App\Models\Device;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display report templates list
     */
    public function index()
    {
        $templates = ReportTemplate::with(['creator', 'generatedReports'])
            ->where('vendor_id', auth()->user()->vendor_id)
            ->latest()
            ->paginate(20);

        $recentReports = GeneratedReport::with(['template', 'generator'])
            ->whereHas('template', function($q) {
                $q->where('vendor_id', auth()->user()->vendor_id);
            })
            ->latest('generated_at')
            ->limit(10)
            ->get();

        return view('admin.reports.index', compact('templates', 'recentReports'));
    }

    /**
     * Show report builder
     */
    public function builder(Request $request)
    {
        $reportTypes = $this->reportService->getAvailableReportTypes();
        $devices = Device::where('vendor_id', auth()->user()->vendor_id)->get();
        
        $selectedType = $request->get('type', 'trip');
        
        return view('admin.reports.builder', compact('reportTypes', 'devices', 'selectedType'));
    }

    /**
     * Generate report (AJAX or Export)
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'name' => 'required|string|max:100',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'devices' => 'nullable|array',
            'devices.*' => 'exists:devices,id',
            'filters' => 'nullable|array',
            'format' => 'nullable|in:csv,excel,xlsx,pdf',
        ]);

        // Create temporary template
        $template = new ReportTemplate([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'filters' => array_merge($validated['filters'] ?? [], [
                'date_from' => $validated['date_from'],
                'date_to' => $validated['date_to'],
                'devices' => $validated['devices'] ?? [],
            ]),
        ]);
        $template->id = 0; // Temporary

        try {
            $reportData = $this->reportService->generateReport($template);

            // If format is specified, export and download
            if ($request->has('format')) {
                $result = $this->reportService->exportReport($reportData, $validated['format'], $template);
                $filePath = storage_path("app/public/{$result['path']}");
                
                return response()->download($filePath)->deleteFileAfterSend(false);
            }

            // Otherwise return JSON for preview
            return response()->json([
                'success' => true,
                'data' => $reportData['data'],
                'columns' => $reportData['columns'],
                'record_count' => $reportData['record_count'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save report template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'filters' => 'required|array',
            'columns' => 'nullable|array',
            'schedule' => 'nullable|string|in:daily,weekly,monthly,none',
            'recipients' => 'nullable|array',
            'recipients.*' => 'email',
        ]);

        $template = $this->reportService->saveTemplate($validated);

        return redirect()->route('admin.reports.index')
            ->with('success', 'Report template saved successfully!');
    }

    /**
     * Export report from template
     */
    public function export(Request $request, $templateId)
    {
        $template = ReportTemplate::findOrFail($templateId);
        
        $validated = $request->validate([
            'format' => 'required|in:csv,excel,xlsx,pdf',
            'filters' => 'nullable|array',
        ]);

        try {
            $reportData = $this->reportService->generateReport($template, $validated['filters'] ?? []);
            $result = $this->reportService->exportReport($reportData, $validated['format'], $template);
            
            $filePath = storage_path("app/public/{$result['path']}");
            return response()->download($filePath);
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting report: ' . $e->getMessage());
        }
    }

    /**
     * Download generated report
     */
    public function download($reportId)
    {
        $report = GeneratedReport::findOrFail($reportId);
        
        $filePath = storage_path("app/public/{$report->file_path}");
        
        if (!file_exists($filePath)) {
            return back()->with('error', 'Report file not found.');
        }

        return response()->download($filePath);
    }

    /**
     * Delete template
     */
    public function destroy($id)
    {
        try {
            $this->reportService->deleteTemplate($id);
            
            return redirect()->route('admin.reports.index')
                ->with('success', 'Report template deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting template: ' . $e->getMessage());
        }
    }

    /**
     * View generated report
     */
    public function view($reportId)
    {
        $report = GeneratedReport::with('template')->findOrFail($reportId);
        
        return view('admin.reports.view', compact('report'));
    }
}
