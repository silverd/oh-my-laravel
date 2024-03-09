<?php

// Excel 读取工具
namespace Silverd\OhMyLaravel\Services\Excel;

use Str;
use Storage;
use Vtiful\Kernel\Excel;
use App\Services\Excel\Types\Csv;
use App\Services\Excel\Types\Xlsx;

class Reader
{
    public $excel;
    public $fileName;

    const SUPPORT_TYPES = [
        'Xlsx',
        'Csv',
    ];

    public function __construct(string $fileUrl, int $sheet = 1, string $type = '')
    {
        if (! $type) {
            $explodeFileUrl = explode('.', $fileUrl);
            $type = end($explodeFileUrl);
        }

        if (! in_array($type = Str::title($type), self::SUPPORT_TYPES)) {
            throws('不支持该文件类型');
        }

        // 文件转存本地
        $this->fileName = \Str::random(32) . '.' . $type;

        Storage::disk('local')->put($this->fileName, fetchImg($fileUrl));

        // 文件本地路径
        $fileDir = storage_path('app/' . $this->fileName);

        $className = 'App\Services\Excel\Types\\' . $type;

        $this->excel = new $className($fileDir, $sheet);
    }

    // 获取下一行数据
    public function nextRow()
    {
        return $this->excel->nextRow();
    }

    public function __destruct()
    {
        // 删除本地临时文件
        Storage::disk('local')->delete($this->fileName);
    }
}
