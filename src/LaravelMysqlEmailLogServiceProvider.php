<?php
namespace Kaoken\LaravelMysqlEmailLog;

use Illuminate\Support\ServiceProvider;

class LaravelMysqlEmailLogServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The basic path of the library here.
     * @param string $path
     * @return string
     */
    protected function my_base_path($path='')
    {
        return __DIR__.'/../'.$path;
    }

    /**
     * The basic path of the library here.
     * @param string $path
     * @return string
     */
    protected function my_resources_path($path='')
    {
        return $this->my_base_path('resources/'.$path);
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->my_resources_path('views') => resource_path('views/vendor/confirmation'),
                $this->my_base_path('database/migrations') => database_path('migrations'),
            ], 'mysql_email_log');
        }
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        $this->app->configureMonologUsing(function($monolog) use($app) {
            $monolog->setHandler(new LaravelMysqlEmailLogHandler());
            //$monolog->pushHandler(new LaravelMysqlEmailLogHandler());
        });
    }
}