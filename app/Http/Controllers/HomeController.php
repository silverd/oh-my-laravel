<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function __invoke()
    {
        exit('Hello ' . config('app.name'));
    }
}
