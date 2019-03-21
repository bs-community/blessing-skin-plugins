<?php

namespace Blessing\ExamplePlugin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function welcome($name)
    {
        return "Welcome! $name";
    }

}
