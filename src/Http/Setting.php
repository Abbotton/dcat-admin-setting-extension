<?php

namespace Dcat\Admin\Extension\Setting\Http;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasDateTimeFormatter;
    
    public $timestamps = true;
    protected $table = 'setting';
    protected $fillable = ['name', 'form_type', 'key', 'value', 'options'];
    protected $casts = [
        'options' => 'json',
        'value' => 'json'
    ];
}
