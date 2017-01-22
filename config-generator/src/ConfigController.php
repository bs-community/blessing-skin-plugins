<?php
/**
 * @Author: printempw
 * @Date:   2017-01-08 14:33:05
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-08 14:36:09
 */

namespace Blessing\ConfigGenerator;

use App\Http\Controllers\Controller;

class ConfigController extends Controller
{
    public function show()
    {
        return view('Blessing\ConfigGenerator::config')->with('user', app('user.current'));
    }
}
