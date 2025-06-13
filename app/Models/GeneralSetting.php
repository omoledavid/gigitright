<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class GeneralSetting extends Model
{
    protected $fillable = [
        'site_name',
        'cur_text',
        'cur_sym',
        'email_form',
        'email_template',
        'mail_config',
        'global_shortcodes',
        'kv',
        'ev',
        'sm',
        'register_status',
        'deposit_status',
        'withdraw_status',
        'en',
        'phone_number',
        'alt_phone_number',
        'address',
        'gft_rate',
        'site_description',
        'site_keywords',
        'logo',
        'dark_logo',
        'favicon',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'youtube',
        'maintenance_mode',
        'login_status'
    ];
    protected $casts = [
        'mail_config'        => 'object',
        'sms_config'         => 'object',
        'modules'            => 'object',
        'wire_transfer_data' => 'object',
        'push_configuration' => 'object',
        'airtime_config'     => 'object',
        'kv' => 'boolean',
        'ev' => 'boolean',
        'sm' => 'boolean',
        'register_status'    => 'boolean',
        'deposit_status'     => 'boolean',
        'withdraw_status'    => 'boolean',
        'en' => 'boolean',
        'maintenance_mode' => 'boolean',
        'login_status' => 'boolean',
        'gft_rate' => 'integer',
    ];
    // protected $hidden = ['email_template','mail_config','sms_config','system_info'];

    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            Cache::forget('GeneralSetting');
            Artisan::call('optimize:clear');
        });
    }
}
