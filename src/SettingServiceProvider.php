<?php

namespace Dcat\Admin\Extension\Setting;

use Dcat\Admin\Admin;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $extension = Setting::make();
        
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database' => database_path()
            ], 'migrations');
        }
        
        $this->app->booted(function () use ($extension) {
            $extension->routes(__DIR__.'/../routes/web.php');
            if (!$this->app->runningInConsole()) {
                $this->loadSettings();
            }
        });
    }
    
    private function loadSettings()
    {
        if (\Schema::hasTable('setting')) {
            \Dcat\Admin\Extension\Setting\Http\Setting::all()->each(function ($item) {
                config([$item->key => $item->value]);
            });
        }
    }
}
