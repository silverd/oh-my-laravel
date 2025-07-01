<?php

namespace Silverd\OhMyLaravel\Services\Excel\Types;

use Vtiful\Kernel\Excel;

class Xlsx
{
    public $excel;

    public function __construct(string $filePath, int $sheet = 1)
    {
        $_filePath = explode('/', $filePath);

        $fileName = end($_filePath);
        $fileDir = rtrim(str_replace($fileName, '', $filePath), '/');

        $this->excel = (new Excel(['path' => $fileDir]))->openFile($fileName);

        $sheetName = '';

        if (! $this->excel->sheetList()) {
            throws(__('File :file failed to read worksheet list', ['file' => $fileName]));
        }

        foreach ($this->excel->sheetList() as $id => $name) {
            if ($sheet == $id + 1) {
                $sheetName = $name;
                break;
            }
        }

        if (! $sheetName) {
            throws(__('File :file specified worksheet does not exist', ['file' => $fileName]));
        }

        $this->excel->openSheet($sheetName);
    }

    public function sheetList()
    {
        return $this->excel->sheetList();
    }

    public function nextRow()
    {
        $row = $this->excel->nextRow();

        if (! $row) {
            return null;
        }

        foreach ($row as $key => $value) {

            $row[$key] = trim(trim($value), '`');

            if (is_numeric($row[$key])) {
                $row[$key] = scientificToNum($row[$key]);
            }
        }

        return $row;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->excel->{$name}(...$arguments);
    }
}
