<?php

namespace GPlane\ShareRegistrationLink;

use Option;

class Configuration
{
    public function render()
    {
        $form = Option::form('reg_links_share', '积分配置', function ($form) {
            $form->text('reg_link_sharer_score', '邀请者可获得的积分')
                ->placeholder('默认为 50');
            $form->text('reg_link_sharee_score', '被邀请者可获得的积分')
                ->placeholder('默认为 0');
        })->handle();

        return view('GPlane\ShareRegistrationLink::config', compact('form'));
    }
}
