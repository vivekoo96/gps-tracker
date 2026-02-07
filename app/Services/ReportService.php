<?php

namespace App\Services;

use App\Models\ReportTemplate;
use App\Models\GeneratedReport;
use App\Services\Reports\TripReport;
use App\Services\Reports\MileageReport;
use App\Services\Reports\SpeedViolationReport;
use App\Services\Reports\IdleTimeReport;
use App\Services\Reports\GeofenceReport;
use App\Services\Reports\FuelConsumptionReport;
use App\Services\Reports\DriverActivityReport;
use App\Services\Reports\MaintenanceDueReport;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ReportService
{
    protected $reportTypes = [
        'trip' => TripReport::class,
        'mileage' => MileageReport::class,
        'speed_violation' => SpeedViolationReport::class,
        'idle_time' => IdleTimeReport::class,
        'geofence' => GeofenceReport::class,
        'fuel_consumption' => FuelConsumptionReport::class,
        'driver_activity' => DriverActivityReport::class,
        'maintenance_due' => MaintenanceDueReport::class,
    ];

    public function getAvailableReportTypes()
    {
        return [
            'trip' => 'Trip Reports',
            'mileage' => 'Mileage Reports',
            'speed_violation' => 'Speed Violation Reports',
            'idle_time' => 'Idle Time Reports',
            'geofence' => 'Geofence Reports',
            'fuel_consumption' => 'Fuel Consumption Reports',
            'driver_activity' => 'Driver Activity Reports',
            'maintenance_due' => 'Maintenance Due Reports',
        ];
    }

    public function generateReport(ReportTemplate $template, array $filters = [])
    {
        // Merge template filters with provided filters
        $mergedFilters = array_merge($template->filters ?? [], $filters);

        // Get report class
        $reportClass = $this->reportTypes[$template->type] ?? null;
        
        if (!$reportClass) {
            throw new \Exception("Unknown report type: {$template->type}");
        }

        // Instantiate and generate
        $report = new $reportClass();
        $report->setFilters($mergedFilters);
        
        $data = $report->generate();

        return [
            'data' => $data,
            'columns' => $report->getColumns(),
            'type' => $report->getType(),
            'record_count' => $data->count(),
        ];
    }

    public function saveTemplate(array $data)
    {
        return ReportTemplate::create([
            'vendor_id' => auth()->user()->vendor_id,
            'created_by' => auth()->id(),
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'columns' => $data['columns'] ?? [],
            'filters' => $data['filters'] ?? [],
            'grouping' => $data['grouping'] ?? null,
            'sorting' => $data['sorting'] ?? null,
            'schedule' => $data['schedule'] ?? null,
            'recipients' => $data['recipients'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function exportReport($data, $format, $template)
    {
        $fileName = $this->generateFileName($template, $format);
        $filePath = "reports/{$fileName}";

        switch ($format) {
            case 'csv':
                $this->exportToCsv($data, $filePath);
                break;
            case 'excel':
            case 'xlsx':
                $this->exportToExcel($data, $filePath);
                break;
            case 'pdf':
                $this->exportToPdf($data, $filePath, $template);
                break;
            default:
                throw new \Exception("Unsupported export format: {$format}");
        }

        // Save generated report record
        $generatedReport = GeneratedReport::create([
            'template_id' => $template->id,
            'generated_by' => auth()->id(),
            'file_path' => $filePath,
            'format' => $format,
            'parameters' => $template->filters,
            'record_count' => count($data['data']),
            'generated_at' => now(),
        ]);

        return [
            'path' => $filePath,
            'report' => $generatedReport
        ];
    }

    private function generateFileName($template, $format)
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $name = str_replace(' ', '_', strtolower($template->name));
        $extension = $format === 'excel' ? 'xlsx' : $format;
        return "{$name}_{$timestamp}.{$extension}";
    }

    private function exportToCsv($data, $filePath)
    {
        $fullPath = storage_path("app/public/{$filePath}");
        
        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen($fullPath, 'w');

        // Write UTF-8 BOM for Excel compatibility
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Write headers
        if (!empty($data['data']) && $data['data']->count() > 0) {
            $headers = array_values($data['columns']);
            fputcsv($file, $headers);

            // Write data
            foreach ($data['data'] as $row) {
                $rowData = [];
                foreach (array_keys($data['columns']) as $key) {
                    $rowData[] = $row[$key] ?? '';
                }
                fputcsv($file, $rowData);
            }
        }

        fclose($file);
    }

    private function exportToExcel($data, $filePath)
    {
        $fullPath = storage_path("app/public/{$filePath}");
        
        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Create simple XML-based Excel file (SpreadsheetML)
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $xml .= '<Worksheet ss:Name="Report">' . "\n";
        $xml .= '<Table>' . "\n";

        // Write headers
        if (!empty($data['data']) && $data['data']->count() > 0) {
            $xml .= '<Row>' . "\n";
            foreach ($data['columns'] as $header) {
                $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>' . "\n";
            }
            $xml .= '</Row>' . "\n";

            // Write data
            foreach ($data['data'] as $row) {
                $xml .= '<Row>' . "\n";
                foreach (array_keys($data['columns']) as $key) {
                    $value = $row[$key] ?? '';
                    $type = is_numeric($value) ? 'Number' : 'String';
                    $xml .= '<Cell><Data ss:Type="' . $type . '">' . htmlspecialchars($value) . '</Data></Cell>' . "\n";
                }
                $xml .= '</Row>' . "\n";
            }
        }

        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        $xml .= '</Workbook>';

        file_put_contents($fullPath, $xml);
    }

    private function exportToPdf($data, $filePath, $template)
    {
        $fullPath = storage_path("app/public/{$filePath}");
        
        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate HTML content
        $html = $this->generatePdfHtml($data, $template);

        // Use wkhtmltopdf if available, otherwise create HTML file
        if ($this->isWkhtmltopdfAvailable()) {
            $tempHtml = tempnam(sys_get_temp_dir(), 'report') . '.html';
            file_put_contents($tempHtml, $html);
            
            exec("wkhtmltopdf {$tempHtml} {$fullPath}");
            unlink($tempHtml);
        } else {
            // Fallback: Save as HTML with PDF extension (browsers can print to PDF)
            file_put_contents($fullPath, $html);
        }
    }

    private function generatePdfHtml($data, $template)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($template->name) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; border-bottom: 2px solid #4F46E5; padding-bottom: 10px; }
        .meta { color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #4F46E5; color: white; padding: 10px; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($template->name) . '</h1>
    <div class="meta">
        <p><strong>Report Type:</strong> ' . ucfirst(str_replace('_', ' ', $template->type)) . '</p>
        <p><strong>Generated:</strong> ' . now()->format('F d, Y H:i:s') . '</p>
        <p><strong>Total Records:</strong> ' . count($data['data']) . '</p>
    </div>';

        if (!empty($data['data']) && $data['data']->count() > 0) {
            $html .= '<table><thead><tr>';
            
            foreach ($data['columns'] as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            
            $html .= '</tr></thead><tbody>';
            
            foreach ($data['data'] as $row) {
                $html .= '<tr>';
                foreach (array_keys($data['columns']) as $key) {
                    $html .= '<td>' . htmlspecialchars($row[$key] ?? '') . '</td>';
                }
                $html .= '</tr>';
            }
            
            $html .= '</tbody></table>';
        } else {
            $html .= '<p>No data available for the selected criteria.</p>';
        }

        $html .= '
    <div class="footer">
        <p>Generated by GPS Fleet Management System</p>
    </div>
</body>
</html>';

        return $html;
    }

    private function isWkhtmltopdfAvailable()
    {
        $output = [];
        $return = 0;
        exec('wkhtmltopdf --version 2>&1', $output, $return);
        return $return === 0;
    }

    public function getTemplatesByType($type)
    {
        return ReportTemplate::where('type', $type)
            ->where('vendor_id', auth()->user()->vendor_id)
            ->active()
            ->get();
    }

    public function deleteTemplate($id)
    {
        $template = ReportTemplate::findOrFail($id);
        
        // Delete associated generated reports
        foreach ($template->generatedReports as $report) {
            Storage::disk('public')->delete($report->file_path);
        }

        return $template->delete();
    }
}
