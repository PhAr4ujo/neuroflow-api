<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;

    protected function paginationAmount(Request $request): int
    {
        return max(1, $request->integer(
            'pagination_amount',
            $request->integer('per_page', 15),
        ));
    }
}
