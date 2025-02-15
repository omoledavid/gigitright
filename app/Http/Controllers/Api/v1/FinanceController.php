<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    use ApiResponses;
    public function bankAccounts()
    {
        return $this->ok('view account');
    }
    public function addAccount()
    {
        return $this->ok('add account');
    }
    public function withdraw()
    {
        return $this->ok('with from account');
    }
}
