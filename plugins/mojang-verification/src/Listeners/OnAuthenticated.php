<?php

namespace GPlane\Mojang\Listeners;

use App\Services\Hook;
use Blessing\Filter;
use GPlane\Mojang\MojangVerification;
use Illuminate\Support\Facades\DB;
use Schema;

class OnAuthenticated
{
    /** @var Filter */
    protected $filter;

    public function __construct(Filter $filter)
    {
        $this->filter = $filter;
    }

    public function handle($event)
    {
        $uid = $event->user->uid;
        $verificationData = MojangVerification::where('user_id', $uid)->first();
        if ($verificationData) {
            if (Schema::hasTable('uuid') && DB::table('uuid')->where('uuid', $verificationData->uuid)->count() === 0) {
                $this->filter->add('grid:user.profile', function ($grid) {
                    array_unshift($grid['widgets'][0][1], 'GPlane\Mojang::uuid');

                    return $grid;
                });
                Hook::addScriptFileToPage(plugin('mojang-verification')->assets('update-uuid.js'), ['user/profile']);
            }
        } else {
            $this->filter->add('grid:user.index', function ($grid) {
                $grid['widgets'][0][1][] = 'GPlane\Mojang::bind';

                return $grid;
            });
        }
    }
}
