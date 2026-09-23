<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComplianceItem;
use App\Models\Contract;
use App\Models\HsseMeeting;
use App\Models\Plan;
use App\Models\RiskRegister;
use App\Models\Unit;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->integer('year', now()->year);
        $unitId = $request->integer('unit_id');
        $zoneId = $request->integer('zone_id');
        $siteId = $request->integer('site_id');

        $units = Unit::where('type', 'department')->orderBy('name')->get(['id', 'name']);
        $rows = [];

        foreach ($units as $unit) {
            $planQ = Plan::where('year', $year)->where('unit_id', $unit->id);
            $riskQ = RiskRegister::where('year', $year)->where('unit_id', $unit->id);
            $contractQ = Contract::where('year', $year)->where('unit_id', $unit->id);
            $hsseQ = HsseMeeting::where('year', $year)->where('unit_id', $unit->id);
            $compQ = ComplianceItem::where('year', $year)->where('unit_id', $unit->id);

            if ($siteId) {
                $riskQ->where('site_id', $siteId);
                $contractQ->where('site_id', $siteId);
                $hsseQ->where('site_id', $siteId);
            } elseif ($zoneId) {
                $riskQ->whereHas('site', fn ($q) => $q->where('zone_id', $zoneId));
                $contractQ->whereHas('site', fn ($q) => $q->where('zone_id', $zoneId));
                $hsseQ->where('zone_id', $zoneId);
            }

            $rows[] = [
                'unit' => $unit->name,
                'plans_total' => (clone $planQ)->count(),
                'plans_done' => (clone $planQ)->where('status', 'done')->count(),
                'plan_progress' => round((clone $planQ)->avg('progress') ?? 0, 1),
                'risks_total' => (clone $riskQ)->count(),
                'risks_high' => (clone $riskQ)->whereIn('risk_level', ['high', 'critical'])->count(),
                'risks_closed' => (clone $riskQ)->where('status', 'closed')->count(),
                'contract_value' => (clone $contractQ)->sum('contract_value'),
                'contract_used' => (clone $contractQ)->sum('used_value'),
                'hsse_target' => (clone $hsseQ)->sum('target_count'),
                'hsse_realized' => (clone $hsseQ)->where('status', 'done')->count(),
                'compliance_total' => (clone $compQ)->count(),
                'compliance_compliant' => (clone $compQ)->where('status', 'compliant')->count(),
                'compliance_non' => (clone $compQ)->where('status', 'non_compliant')->count(),
            ];
        }

        return response()->json(['data' => $rows, 'meta' => ['year' => $year]]);
    }

    public function export(Request $request)
    {
        $data = $this->index($request)->getData(true);
        $rows = $data['data'];
        $year = $data['meta']['year'];

        $headers = [
            'Unit', 'Plan Total', 'Plan Done', 'Plan Progress %', 'Risk Total', 'Risk High/Critical',
            'Risk Closed', 'Contract Value (IDR)', 'Contract Used (IDR)', 'HSSE Target',
            'HSSE Realized', 'Compliance Total', 'Compliance Compliant', 'Compliance Non-Compliant',
        ];

        $navy = '1E3A8A';
        $zebra = 'F1F5F9';
        $totalFill = 'DBEAFE';
        $borderColor = 'CBD5E1';
        $white = 'FFFFFF';

        $lastCol = count($headers);
        $lastColLetter = Coordinate::stringFromColumnIndex($lastCol);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary');

        $sheet->setCellValue('A1', 'CMP - Performance Summary Report');
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A2', sprintf('Year %d  -  Generated %s  -  All Departments (Amounts in IDR)', $year, now()->format('d M Y H:i')));
        $sheet->mergeCells("A2:{$lastColLetter}2");

        foreach ($headers as $i => $header) {
            $coord = Coordinate::stringFromColumnIndex($i + 1).'4';
            $sheet->setCellValueExplicit($coord, $header, DataType::TYPE_STRING);
        }

        $startRow = 5;
        $rowIdx = $startRow;

        foreach ($rows as $row) {
            $values = [
                $row['unit'],
                (int) $row['plans_total'], (int) $row['plans_done'], (float) $row['plan_progress'],
                (int) $row['risks_total'], (int) $row['risks_high'], (int) $row['risks_closed'],
                (float) $row['contract_value'], (float) $row['contract_used'],
                (int) $row['hsse_target'], (int) $row['hsse_realized'],
                (int) $row['compliance_total'], (int) $row['compliance_compliant'], (int) $row['compliance_non'],
            ];
            foreach ($values as $i => $value) {
                $coord = Coordinate::stringFromColumnIndex($i + 1).$rowIdx;
                $sheet->setCellValueExplicit(
                    $coord,
                    $value,
                    is_string($value) ? DataType::TYPE_STRING : DataType::TYPE_NUMERIC
                );
            }
            $rowIdx++;
        }

        $lastDataRow = $rowIdx - 1;
        $totalRow = $rowIdx;

        // Totals
        $sheet->setCellValueExplicit('A'.$totalRow, 'TOTAL', DataType::TYPE_STRING);
        $totalKeys = [
            2 => 'plans_total', 3 => 'plans_done', 4 => 'plan_progress', 5 => 'risks_total',
            6 => 'risks_high', 7 => 'risks_closed', 8 => 'contract_value', 9 => 'contract_used',
            10 => 'hsse_target', 11 => 'hsse_realized', 12 => 'compliance_total',
            13 => 'compliance_compliant', 14 => 'compliance_non',
        ];
        foreach ($totalKeys as $col => $key) {
            $coord = Coordinate::stringFromColumnIndex($col).$totalRow;
            $total = ($key === 'plan_progress')
                ? round(array_sum(array_column($rows, $key)) / max(count($rows), 1), 1)
                : array_sum(array_column($rows, $key));
            $sheet->setCellValueExplicit($coord, $total, DataType::TYPE_NUMERIC);
        }

        // Title styling
        $sheet->getStyle("A1:{$lastColLetter}1")->getFont()->setBold(true)->setSize(16)->getColor()->setRGB($white);
        $sheet->getStyle("A1:{$lastColLetter}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($navy);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('64748B');

        // Header styling
        $headerRange = "A4:{$lastColLetter}4";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(11)->getColor()->setRGB($white);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($navy);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Number formats
        $sheet->getStyle("H5:I{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D5:D{$lastDataRow}")->getNumberFormat()->setFormatCode('0.0');

        // Zebra + borders
        $zebraRange = "A5:{$lastColLetter}{$lastDataRow}";
        foreach (range($startRow, $lastDataRow) as $r) {
            if (($r - $startRow) % 2 === 1) {
                $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($zebra);
            }
        }
        $sheet->getStyle($zebraRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($borderColor);
        $sheet->getStyle($zebraRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A5:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("H5:I{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Totals row styling
        $sheet->getStyle("A{$totalRow}:{$lastColLetter}{$totalRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$totalRow}:{$lastColLetter}{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$totalRow}:{$lastColLetter}{$totalRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB($navy);
        $sheet->getStyle("D{$totalRow}")->getNumberFormat()->setFormatCode('0.0');
        $sheet->getStyle("H{$totalRow}:{$lastColLetter}{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Widths
        $widths = [30, 10, 10, 13, 10, 14, 11, 20, 20, 11, 13, 12, 12, 12];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col + 1))->setWidth($width);
        }

        $sheet->freezePane('B5');

        $filename = 'cmp-report-'.$year.'.xlsx';

        return response()->streamDownload(
            function () use ($spreadsheet) {
                (new Xlsx($spreadsheet))->save('php://output');
            },
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
