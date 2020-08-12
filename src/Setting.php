<?php

namespace Dcat\Admin\Extension\Setting;

use Dcat\Admin\Extension;

class Setting extends Extension
{
    const NAME = 'setting';
    
    protected $serviceProvider = SettingServiceProvider::class;
    
    protected $composer = __DIR__.'/../composer.json';
    
    public function __construct()
    {
        $this->menu = [
            'title' => '系统配置',
            'icon' => 'feather icon-settings',
            'uri' => 'setting',
            'path' => 'setting',
        ];
    }
}
