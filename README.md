本扩展用于 Laravel Framework 10 的一些应用层初始化

### 如何使用

```
composer require silverd/oh-my-laravel:dev-master
php artisan oh-my-laravel:install
```

### 本地二次开发

```
cd ~/home/wwwroot/
git clone git@github.com:silverd/oh-my-laravel.git

cd ~/homw/wwwroot/sample_prcd oject
composer config repositories.silverd/oh-my-laravel path ~/home/wwwroot/oh-my-laravel
composer require silverd/oh-my-laravel:dev-master -vvv
php artisan oh-my-laravel:install
```

### 特殊说明

修改 config/app.php，在 `providers` 中加入 `Mnabialek\LaravelSqlLogger\Providers\ServiceProvider::class`。

原因：如果 `mnabialek/laravel-sql-logger` 和 `mongodb/laravel-mongodb` 同时使用，务必注意 bootstrap/cache/packages.php 中 ServiceProvider 的加载顺序，必须确保 `Mnabialek\LaravelSqlLogger\Providers\ServiceProvider` 放到 `MongoDB\Laravel\MongoDBServiceProvider`。

因为前者中的 `$this->app['db']` 会触发 resolve 回调（同时触发 resolving 事件），但 db 恰好是单例模式，resolving 事件只会触发一次，导致 `MongoDBServiceProvider` 的 resolving 事件不再触发（而 mongodb driver 需要再首次 resolving 时被 extend），所以在使用时会报错 Unsupported driver [mongodb]。

但 bootstrap/cache/packages.php（自动发现）中的各 ServiceProvider 是按字母升序排，所以 Mnabialek 会在排在 MongoDB 前面，导致上面的报错。

如何解决？将 `mnabialek/laravel-sql-logger` 设置为 dont-discover，然后在项目 config/app.php 手动引入。
