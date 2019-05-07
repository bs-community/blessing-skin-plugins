<?php

namespace GPlane\ShareRegistrationLink;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CodeController extends Controller
{
    public function list()
    {
        $records = Record::where('sharer', auth()->id())
            ->select('code')
            ->get()
            ->map(function ($record) {
                return [
                    'code' => $record->code,
                    'url' => url('/auth/register?share='.$record->code),
                ];
            });

        return json([
            'records' => $records,
            'sharer' => option('reg_link_sharer_score', 50),
            'sharee' => option('reg_link_sharee_score', 0),
        ]);
    }

    public function generate()
    {
        $record = new Record;
        $record->sharer = auth()->id();
        $record->code = Str::random(20);
        $record->save();

        return json('注册链接已生成。', 0, [
            'code' => $record->code,
            'url' => url('/auth/register?share='.$record->code),
        ]);
    }

    public function remove(Request $request)
    {
        $code = $request->input('code');
        $record = Record::where('code', $code)->first();
        if ($record) {
            $record->delete();
            return json('删除成功', 0);
        } else {
            return json('记录不存在', 0);
        }
    }
}
