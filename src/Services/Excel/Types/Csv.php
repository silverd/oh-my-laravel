<?php

namespace Silverd\OhMyLaravel\Services\Excel\Types;

use Vtiful\Kernel\Excel;

class Csv
{
    public $excel;

    public function __construct(string $filePath, int $sheet = 1)
    {
        $this->excel = self::getRows($filePath);
    }

    public function nextRow()
    {
        $row = $this->excel->current();

        if (! $row) {
            return null;
        }

        foreach ($row as $key => $value) {

            $coding = mb_detect_encoding($value, ['ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5']);

            $row[$key] = trim(trim(mb_convert_encoding($value, 'utf-8', $coding)), '`');

            if (is_numeric($row[$key])) {
                $row[$key] = scientificToNum($row[$key]);
            }
        }

        $this->excel->next();

        return $row;
    }

    public static function getRows($file) {

        $handle = fopen($file, 'r');

        while (feof($handle) === false) {
            yield fgetcsv($handle);
        }

        fclose($handle);
    }
}
