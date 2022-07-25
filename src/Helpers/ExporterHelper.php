<?php

namespace App\Helpers;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;

class ExporterHelper
{
    // @see https://docs.laravel-excel.com/3.1/exports
    public static function downloadExcel(
        string $fileName,
        array $data,
        array $headings,
        array $columnFormats = []
    )
    {
        $sheet = self::getSheetExporter($data, $headings, $columnFormats);

        return $sheet->download($fileName);
    }

    public static function saveExcel(
        string $fileName,
        array $data,
        array $headings,
        array $columnFormats = [],
        string $disk = 'public'
    )
    {
        $sheet = self::getSheetExporter($data, $headings, $columnFormats);

        \Excel::store($sheet, $fileName, $disk);

        return \Storage::disk($disk)->url($fileName);
    }

    // @see https://docs.laravel-excel.com/3.1/exports/multiple-sheets.html
    public static function downloadMultipleSheets(string $fileName, array $sheetDatas)
    {
        $sheets = [];

        foreach ($sheetDatas as $sheetData) {
            $sheets[] = self::getSheetExporter(...$sheetData);
        }

        $exporter = new class($sheets) implements WithMultipleSheets {

            use Exportable;

            public function __construct(array $sheets)
            {
                $this->sheets = $sheets;
            }

            public function sheets(): array
            {
                return $this->sheets;
            }

        };

        return $exporter->download($fileName);
    }

    private static function getSheetExporter(...$args)
    {
        return new class(...$args) implements
            FromArray,
            WithTitle,
            WithHeadings,
            WithColumnFormatting,
            WithStrictNullComparison,
            ShouldAutoSize
        {
            use Exportable;

            public function __construct(
                array $data,
                array $headings,
                array $columnFormats = [],
                string $sheetTitle = ''
            )
            {
                $this->data          = $data;
                $this->headings      = $headings;
                $this->columnFormats = $columnFormats;
                $this->sheetTitle    = $sheetTitle ?: '工作表';
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return $this->headings;
            }

            public function columnFormats(): array
            {
                return $this->columnFormats;
            }

            public function title(): string
            {
                return $this->sheetTitle;
            }
        };
    }
}
