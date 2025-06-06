<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GeneralSetting extends Model
{
    protected $casts = [
        'mail_config'        => 'object',
        'sms_config'         => 'object',
        'global_shortcodes'  => 'object',
        'modules'            => 'object',
        'wire_transfer_data' => 'object',
        'push_configuration' => 'object',
        'airtime_config'     => 'object',
    ];
    protected $hidden = ['email_template','mail_config','sms_config','system_info'];

    protected static function boot() {
        parent::boot();
        static::saved(function () {
            Cache::forget('GeneralSetting');
        });
    }
}
