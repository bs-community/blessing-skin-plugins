<?php

namespace GPlane\ShareRegistrationLink;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CodeController extends Controller
{
    public function list()
    {
        return Record::where('sharer', auth()->id())
            ->select('code')
            ->get()
            ->map(function ($record) {
                return [
                    'code' => $record->code,
                    'url' => url('/auth/register?share='.$record->code),
                ];
            });
    }

    public function generate()
    {
        $record = new Record;
        $record->sharer = auth()->id();
        $record->code = Str::random(120);
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
