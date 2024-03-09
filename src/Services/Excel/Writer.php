<?php

// @see https://xlswriter-docs.viest.me/zh-cn
namespace Silverd\OhMyLaravel\Services\Excel;

use Illuminate\Http\File;

class Writer
{
    public $excel;
    public $fileName;

    public function __construct(string $fileName, bool $useConstMemory = false, string $sheetName = '工作表')
    {
        $this->fileName = $fileName . '.xlsx';

        $config = [
            'path' => storage_path('app/'),
        ];

        $this->excel = new \Vtiful\Kernel\Excel($config);

        $uniqName = \Str::random(32) . '.xlsx';

        // 固定内存模式
        // @see https://xlswriter-docs.viest.me/zh-cn/nei-cun/gu-ding-nei-cun-mo-shi
        if ($useConstMemory) {
            $this->excel->constMemory($uniqName, $sheetName);
        } else {
            $this->excel->fileName($uniqName, $sheetName);
        }
    }

    public function download()
    {
        $filePath = $this->excel->output();

        return response()->download($filePath, $this->fileName);
    }

    public function output(string $disk = 'public')
    {
        $filePath = $this->excel->output();

        $disk = \Storage::disk($disk);

        $fileKey = $disk->putFileAs('excel', new File($filePath), $this->fileName);

        return $disk->url($fileKey);
    }

    public function getHandle()
    {
        return $this->excel->getHandle();
    }

    public function __call(string $name, array $args)
    {
        $this->excel->{$name}(...$args);

        return $this;
    }

    public function __destruct()
    {
        // 删除本地临时文件
        \Storage::disk('local')->delete($this->fileName);
    }

    public function insertTexts(array $list, int $startRowNo = 0, $gridStyle = null, callable $callback = null)
    {
        $rowNo = $startRowNo;

        foreach ($list as $row) {

            $colNo = 0;

            foreach ($row as $value) {

                // 单元格的值如果为数字，则一律转为数值型
                // 因为如果 $value 值为字符串，XLSWriter 会将单元格自动识别为带小绿标的字符型单元格
                $value = isNumeric($value) ? floatval($value) : $value;

                // 注意：行列的起始下标都为0
                if ($gridStyle) {
                    $this->excel->insertText($rowNo, $colNo++, $value, '', $gridStyle);
                }
                else {
                    $this->excel->insertText($rowNo, $colNo++, $value);
                }
            }

            if ($callback) {
                $callback($row);
            }

            $rowNo++;
        }

        return $rowNo;
    }
}
