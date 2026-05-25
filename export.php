<?php
ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// CLEAN OUTPUT BUFFER (IMPORTANT)
if (ob_get_length()) ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Unique_Leads_29_March_2026.xlsx"');
header('Cache-Control: max-age=0');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
// Set headers to match screenshot order and names
$sheet->setCellValue('A1', 'SL NO.');
$sheet->setCellValue('B1', 'COMPANY');
$sheet->setCellValue('C1', 'COMPANY URL');
$sheet->setCellValue('D1', 'NAMES');
$sheet->setCellValue('E1', 'TITLE');
$sheet->setCellValue('F1', 'PHONE NUMBER');
$sheet->setCellValue('G1', 'E-MAILS');
$sheet->setCellValue('H1', 'POSITIONS');
$sheet->setCellValue('I1', 'LOCATIONS');
$sheet->setCellValue('J1', 'URL');
$sheet->setCellValue('K1', 'SOURCE');



// Style header row: black background, blue text, bold, centered, with border
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => '000000'], // black text ✅
        'size' => 12,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4F81BD'], // blue background ✅
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

$files = glob("cache/*.json");
if (!$files) {
    $sheet->setCellValue('A2', 'No cache files found');
} else {
    // Load and merge all jobs from all cache files
    $allJobs = [];
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if (isset($data['data']) && is_array($data['data'])) {
            $allJobs = array_merge($allJobs, $data['data']);
        } elseif (is_array($data)) {
            $allJobs = array_merge($allJobs, $data);
        }
    }

    // Filter unique jobs
    $uniqueJobs = [];
    $seenUrls = [];
    foreach ($allJobs as $job) {
        $url = $job['linkedin_url'] ?? $job['linkedin url'] ?? '';
        if (!empty($url) && !in_array($url, $seenUrls)) {
            $seenUrls[] = $url;
            $uniqueJobs[] = $job;
        } elseif (empty($url)) {
            $uniqueJobs[] = $job;
        }
    }

    if (empty($uniqueJobs)) {
        $sheet->setCellValue('A2', 'No jobs found in cache');
    } else {
        $slNo = 1;
        $row = 2;

        foreach ($uniqueJobs as $job) {
            $sheet->setCellValue('A' . $row, $slNo++);
            $sheet->setCellValue('B' . $row, $job['company'] ?? '');
            $sheet->setCellValue('C' . $row, $job['company_url'] ?? $job['company url'] ?? '');
            $sheet->setCellValue('D' . $row, $job['name'] ?? ($job['names'] ?? ''));
            $sheet->setCellValue('E' . $row, $job['title'] ?? '');
            $sheet->setCellValue('F' . $row, $job['phone'] ?? ($job['phone_number'] ?? ''));
            $sheet->setCellValue('G' . $row, $job['email'] ?? ($job['e-mails'] ?? ($job['emails'] ?? '')));
            $sheet->setCellValue('H' . $row, $job['positions'] ?? '');
            $sheet->setCellValue('I' . $row, $job['location'] ?? ($job['locations'] ?? ''));
            $sheet->setCellValue('J' . $row, $job['linkedin_url'] ?? ($job['url'] ?? ''));
            $sheet->setCellValue('K' . $row, $job['source'] ?? '');
            $row++;
        }
        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;