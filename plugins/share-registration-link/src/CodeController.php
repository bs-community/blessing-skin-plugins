<?php

namespace GPlane\ShareRegistrationLink;

use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class CodeController extends Controller
{
    public function list()
    {
        $records = Record::where('sharer', auth()->id())
            ->get()
            ->map(function ($record) {
                $record->url = route('auth.register', ['share' => $record->code]);

                return $record;
            });

        return json([
            'records' => $records,
            'sharer' => (int) option('reg_link_sharer_score', 50),
            'sharee' => (int) option('reg_link_sharee_score', 0),
        ]);
    }

    public function generate()
    {
        $record = new Record();
        $record->sharer = auth()->id();
        $record->code = Str::random(20);
        $record->save();

        $record->url = route('auth.register', ['share' => $record->code]);

        return json('注册链接已生成。', 0, compact('record'));
    }

    public function remove($id)
    {
        Record::destroy($id);
    }
}
