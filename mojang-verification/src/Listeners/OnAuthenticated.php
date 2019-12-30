<?php

namespace GPlane\Mojang\Listeners;

use App\Services\Hook;
use Blessing\Filter;
use GPlane\Mojang\MojangVerification;
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
        if (MojangVerification::where('user_id', $uid)->count() == 1) {
            Hook::addUserBadge(trans('GPlane\Mojang::general.pro'), 'purple');
            if (Schema::hasTable('uuid')) {
                $this->filter->add('grid:user.profile', function ($grid) {
                    array_unshift($grid['widgets'][0][1], 'GPlane\Mojang::uuid');

                    return $grid;
                });
                Hook::addScriptFileToPage(plugin_assets('mojang-verification', 'update-uuid.js'), ['user/profile']);
            }
        } else {
            $this->filter->add('grid:user.index', function ($grid) {
                $grid['widgets'][0][1][] = 'GPlane\Mojang::bind';

                return $grid;
            });
        }
    }
}
