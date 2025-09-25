<?php

namespace MarkMatusek74\ReportingEngine\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MarkMatusek74\ReportingEngine\Models\Report;
use MarkMatusek74\ReportingEngine\Services\ReportingEngineService;

class ReportController extends Controller
{
    protected $reportingService;

    public function __construct(ReportingEngineService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    /**
     * Display a listing of reports.
     */
    public function index()
    {
        return view('reporting-engine::pages.reports.index');
    }

    /**
     * Show the form for creating a new report.
     */
    public function create()
    {
        return view('reporting-engine::pages.reports.create');
    }

    /**
     * Display the specified report.
     */
    public function show(Report $report)
    {
        if (!$report->isActive() && !$report->isPublic()) {
            abort(404);
        }

        return view('reporting-engine::pages.reports.show', compact('report'));
    }

    /**
     * Show the form for editing the specified report.
     */
    public function edit(Report $report)
    {
        return view('reporting-engine::pages.reports.edit', compact('report'));
    }

    /**
     * Get report data via API.
     */
    public function getData(Request $request, Report $report)
    {
        if (!$report->isActive() && !$report->isPublic()) {
            abort(404);
        }

        $filters = $request->get('filters', []);
        $options = [
            'per_page' => $request->get('per_page', 25),
            'page' => $request->get('page', 1),
        ];

        if ($request->has('sort_by')) {
            $options['sorting'] = [
                [
                    'field' => $request->get('sort_by'),
                    'direction' => $request->get('sort_direction', 'asc'),
                ]
            ];
        }

        $data = $this->reportingService->generateReport($report, $filters, $options);

        return response()->json($data);
    }

    /**
     * Export report data.
     */
    public function export(Request $request, Report $report)
    {
        if (!$report->isActive() && !$report->isPublic()) {
            abort(404);
        }

        $format = $request->get('format', 'csv');
        $filters = $request->get('filters', []);

        // Generate report data without pagination for export
        $data = $this->reportingService->generateReport($report, $filters, ['per_page' => 10000]);

        switch ($format) {
            case 'csv':
                return $this->exportAsCsv($report, $data);
            case 'excel':
                return $this->exportAsExcel($report, $data);
            case 'pdf':
                return $this->exportAsPdf($report, $data);
            default:
                abort(400, 'Unsupported export format');
        }
    }

    /**
     * Export report as CSV.
     */
    protected function exportAsCsv(Report $report, array $data)
    {
        $filename = str_replace(' ', '_', strtolower($report->name)) . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data, $report) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            $fields = $report->visibleFields;
            $headers = $fields->pluck('label')->toArray();
            fputcsv($file, $headers);
            
            // Write data
            foreach ($data['data'] as $row) {
                $csvRow = [];
                foreach ($fields as $field) {
                    $csvRow[] = $row[$field->name] ?? '';
                }
                fputcsv($file, $csvRow);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export report as Excel.
     */
    protected function exportAsExcel(Report $report, array $data)
    {
        // This would require a package like PhpSpreadsheet
        // For now, return CSV format
        return $this->exportAsCsv($report, $data);
    }

    /**
     * Export report as PDF.
     */
    protected function exportAsPdf(Report $report, array $data)
    {
        // This would require a package like dompdf or tcpdf
        // For now, return CSV format
        return $this->exportAsCsv($report, $data);
    }
}