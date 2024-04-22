<?php

namespace Masterei\Signer\Tests\Controllers;

use Illuminate\Routing\Controller;

class TestController extends Controller
{
    public function successResponse()
    {
        return response(['message' => 'authorized'])->setStatusCode(200);
    }
}
