<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return [
        'Project Title' => 'Gigitright',
        'Agency' => 'Albanny Technogy',
        'Developer' => 'Omole Kessiena',
        'Laravel' => app()->version()
    ];
});

require __DIR__.'/auth.php';
