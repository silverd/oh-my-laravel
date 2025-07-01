<?php

// Excel 读取工具
namespace Silverd\OhMyLaravel\Services\Excel;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Reader
{
    public $excel;
    public $fileName;

    const SUPPORT_TYPES = [
        'xlsx',
        'csv',
    ];

    public function __construct(string $fileUrl, int $sheet = 1, string $extension = '')
    {
        // 文件扩展名
        if (! $extension) {
            $_fileUrl = explode('.', $fileUrl);
            $extension = end($_fileUrl);
        }

        if (! in_array($extension, self::SUPPORT_TYPES)) {
            throws(__('Unsupported file type'));
        }

        // 远程地址转为本地文件
        if (filter_var($fileUrl, FILTER_VALIDATE_URL)) {

            // 文件转存本地
            $disk = Storage::disk('local');

            $this->fileName = Str::random(32) . '.' . $extension;

            $disk->put($this->fileName, fetchImg($fileUrl));

            $fileUrl = storage_path('app/' . $this->fileName);
        }

        $className = 'Silverd\OhMyLaravel\Services\Excel\Types\\' . ucfirst($extension);

        $this->excel = new $className($fileUrl, $sheet);
    }

    public function __destruct()
    {
        // 删除本地临时文件
        if ($this->fileName) {
            Storage::disk('local')->delete($this->fileName);
        }
    }

    public function __call(string $name, array $args)
    {
        return $this->excel->{$name}(...$args);
    }
}
