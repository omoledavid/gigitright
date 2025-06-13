<?php

namespace App\Helpers;

use App\Rules\FileTypeValidate;

class FileRules
{
    public static function general(): array
    {
        return [
            'nullable',
            new FileTypeValidate([
                'jpg',
                'jpeg',
                'png',
                'pdf',
                'doc',
                'docx',
            ])
        ];
    }

    public static function imageOnly(): array
    {
        return ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png', 'webp'])];
    }

    public static function documentOnly(): array
    {
        return ['nullable', new FileTypeValidate(['pdf', 'docx'])];
    }
}
