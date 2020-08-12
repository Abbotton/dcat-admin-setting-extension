<?php

namespace Dcat\Admin\Extension\Setting\Repositories;

use Dcat\Admin\Extension\Setting\Http\Setting as SettingModel;
use Dcat\Admin\Repositories\EloquentRepository;

class Setting extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = SettingModel::class;
}
