<?php
// app/Services/ReportService.php

namespace App\Services;

use Illuminate\Support\Facades\{Storage, View};
use Carbon\Carbon;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportService
{
    private string $reportsPath = 'reports';

    public function generatePDF(string $view, array $data, string $filename, string $orientation = 'portrait'): string
    {
        $pdf = PDF::loadView($view, $data);
        $pdf->setPaper('a4', $orientation);

        // Configurações do PDF
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'dpi' => 150,
            'defaultMediaType' => 'print',
            'isPhpEnabled' => true,
        ]);

        // Adicionar numeração de páginas
        $pdf->output();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $canvas->page_text(270, 820, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 10, [0, 0, 0]);

        // Salvar PDF
        $fullPath = "{$this->reportsPath}/{$filename}";
        Storage::disk('public')->put($fullPath, $pdf->output());

        return Storage::disk('public')->path($fullPath);
    }

    public function downloadPDF(string $view, array $data, string $filename, string $orientation = 'portrait')
    {
        $pdf = PDF::loadView($view, $data);
        $pdf->setPaper('a4', $orientation);

        return $pdf->download($filename);
    }

    public function streamPDF(string $view, array $data, string $filename, string $orientation = 'portrait')
    {
        $pdf = PDF::loadView($view, $data);
        $pdf->setPaper('a4', $orientation);

        return $pdf->stream($filename);
    }

    public function formatCurrency(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    public function formatDate($date, string $format = 'd/m/Y'): string
    {
        if (!$date) return '';
        return Carbon::parse($date)->format($format);
    }
}
