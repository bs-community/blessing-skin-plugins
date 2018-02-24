<?php

namespace Blessing\ImportV2Data;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use App\Http\Controllers\Controller;
use App\Services\Repositories\UserRepository;

class ImportController extends Controller
{
    public function __construct()
    {
        if (app('user.current')->getPermission() != User::SUPER_ADMIN) {
            abort(403, '此页面仅超级管理员可访问');
        }
    }

    public function welcome()
    {
        return view('Blessing\ImportV2Data::index');
    }

    public function import()
    {
        return view('Blessing\ImportV2Data::import');
    }

    public function finish(Request $request)
    {
        $this->validate($request, [
            'v2_table_name'        => 'required|no_special_chars',
            'texture_name_pattern' => 'required'
        ]);

        if (! app('legacy_db_helper')->hasTable($request->input('v2_table_name'))) {
            return back()->withErrors("数据表 {$_POST['v2_table_name']} 不存在");
        }

        $result = Migration::import([
            'table_name' => $request->input('v2_table_name'),
            'texture_name_pattern' => $request->input('texture_name_pattern'),
            'public' => $request->input('import_as_private') ? '0' : '1'
        ]);

        return view('Blessing\ImportV2Data::finish')->with('result', $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function formatValidationErrors(Validator $validator)
    {
        return $validator->errors()->all();
    }

}
