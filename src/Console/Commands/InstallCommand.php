<?php

namespace Silverd\OhMyLaravel\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'oh-my-laravel:install';

    public function handle()
    {
        $this->call('vendor:publish', [
            '--tag' => 'oh-my-laravel',
            '--force' => true,
        ]);

        $this->call('migrate');

        $this->replaceSomeFiles();
    }

    protected function replaceSomeFiles()
    {
        file_put_contents(base_path('.gitignore'), '/public/js/app.js' . PHP_EOL, FILE_APPEND);
        file_put_contents(base_path('.gitignore'), '/public/css/app.css' . PHP_EOL, FILE_APPEND);
        file_put_contents(base_path('.gitignore'), '/public/mix-manifest.json' . PHP_EOL, FILE_APPEND);

        $this->replaceInFile(base_path('.gitignore'), '.env' . PHP_EOL, '');
        $this->replaceInFile(config_path('app.php'), 'UTC', 'Asia/Shanghai');
        $this->replaceInFile(config_path('app.php'), "'locale' => 'en'", "'locale' => 'zh-CN'");
        $this->replaceInFile(public_path('docs/index.html'), '__APP_NAME__', config('app.name'));
    }

    /**
     * Replace a given string in a given file.
     *
     * @param  string  $path
     * @param  string  $search
     * @param  string  $replace
     * @return void
     */
    protected function replaceInFile($path, $search, $replace)
    {
        file_put_contents(
            $path,
            str_replace($search, $replace, file_get_contents($path))
        );
    }
}
