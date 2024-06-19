<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelService
{
    public function readExcelFile(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true); // Preserve empty cells as null

        return $this->markEmptyCells($data);
    }

    private function markEmptyCells(array $data): array
    {
        return array_map(function ($row) {
            return array_map(function ($cell) {
                return is_null($cell) || $cell === '' ? null : $cell;
            }, $row);
        }, $data);
    }
}
