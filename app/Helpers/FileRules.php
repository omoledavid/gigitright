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
            ]),
            'max:10240'
        ];
    }

    public static function imageOnly(): array
    {
        return ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png', 'webp']),'max:10240'];
    }

    public static function documentOnly(): array
    {
        return ['nullable', new FileTypeValidate(['pdf', 'docx']),'max:10240'];
    }
}
