<?php

namespace App\Constants;

class FileInfo {
    /*
    |--------------------------------------------------------------------------
    | File Information
    |--------------------------------------------------------------------------
    |
    | This class basically contain the path of files and size of images.
    | All information are stored as an array. Developer will be able to access
    | this info as method and property using FileManager class.
    |
    */

    public function fileInfo() {
        $data['user_profile'] = [
            'path' => 'assets/images/user/profile_image'
        ];
        $data['portfolio'] = [
            'path'      => 'assets/images/user/portfolio'
        ];
        $data['certificates'] = [
            'path'      => 'assets/images/user/certificates'
        ];
        $data['default'] = [
            'path'      => 'assets/images/default.png',
        ];
        $data['messaging'] = [
            'path'      => 'assets/images/user/messaging',
        ];
        $data['ticket'] = [
            'path'      => 'assets/support',
        ];

        $data['logoIcon'] = [
            'path'      => 'assets/images/logoIcon',
        ];
        $data['favicon'] = [
            'size'      => '128x128',
        ];
        $data['extensions'] = [
            'path'      => 'assets/images/extensions',
            'size'      => '36x36',
        ];
        $data['seo'] = [
            'path'      => 'assets/images/seo',
            'size'      => '1180x600',
        ];
        $data['userProfile'] = [
            'path'      => 'assets/images/user/profile',
            'size'      => '350x300',
        ];
        $data['adminProfile'] = [
            'path'      => 'assets/admin/images/profile',
            'size'      => '400x400',
        ];
        $data['beneficiaryTransfer'] = [
            'path' => 'assets/images/user/transfer/beneficiary'
        ];
        $data['branchStaff'] = [
            'path' => 'assets/branch/staff/resume'
        ];
        $data['branchStaffProfile'] = [
            'path'      => 'assets/branch/staff/images/profile',
            'size'      => '400x400',
        ];
        return $data;
    }
}
