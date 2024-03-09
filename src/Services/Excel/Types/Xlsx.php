<?php

namespace Silverd\OhMyLaravel\Services\Excel\Types;

use Vtiful\Kernel\Excel;

class Xlsx
{
    public $excel;

    public function __construct(string $filePath, int $sheet = 1)
    {
        $explodeFilePath = explode('/', $filePath);

        $fileName = end($explodeFilePath);
        $fileDir = str_replace($fileName, '', $filePath);

        $this->excel = (new Excel(['path' => $fileDir]))->openFile($fileName);

        $sheetName = '';

        if (! $this->excel->sheetList()) {
            throws('文件「' . $fileName . '」读取工作表列表失败');
        }

        foreach ($this->excel->sheetList() as $id => $name) {
            if ($sheet == $id + 1) {
                $sheetName = $name;
                break;
            }
        }

        if (! $sheetName) {
            throws('文件「' . $fileName .'」指定工作表不存在');
        }

        $this->excel->openSheet($sheetName);
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
