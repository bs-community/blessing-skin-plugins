<?php
/**
 * @Author: printempw
 * @Date:   2017-01-02 09:41:06
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-21 13:34:48
 *
 * Originally created by Matt Mullenweg as a WordPress plugin,
 * migrated to Blessing Skin Server by printempw.
 */

use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {

    $events->listen(App\Events\RenderingFooter::class, function($event) {
        // This just echoes the chosen line, we'll position it later
        $chosen = hello_dolly_get_lyric();

        $content = <<< EOT
<script>
if ($('.breadcrumb').length > 0) {
    $('.breadcrumb').append("<p class='dolly'>$chosen</p>");
} else {
    $('.content-header').append("<div class='breadcrumb'><p class='dolly'>$chosen</p></div>");
}
</script>
EOT;

        $event->addContent($content);
    });

    $events->listen(App\Events\RenderingHeader::class, function($event) {
        // We need some CSS to position the paragraph
        $event->addContent('<style> .dolly { display: inline; margin-left: 15px; } </style>');
    });

};

function hello_dolly_get_lyric() {
    /** These are the lyrics to Hello Dolly */
    $lyrics = "Hello, Dolly
Well, hello, Dolly
It's so nice to have you back where you belong
You're lookin' swell, Dolly
I can tell, Dolly
You're still glowin', you're still crowin'
You're still goin' strong
We feel the room swayin'
While the band's playin'
One of your old favourite songs from way back when
So, take her wrap, fellas
Find her an empty lap, fellas
Dolly'll never go away again
Hello, Dolly
Well, hello, Dolly
It's so nice to have you back where you belong
You're lookin' swell, Dolly
I can tell, Dolly
You're still glowin', you're still crowin'
You're still goin' strong
We feel the room swayin'
While the band's playin'
One of your old favourite songs from way back when
Golly, gee, fellas
Find her a vacant knee, fellas
Dolly'll never go away
Dolly'll never go away
Dolly'll never go away again";

    // Here we split it into lines
    $lyrics = explode( "\n", $lyrics );

    // And then randomly choose a line
    return $lyrics[ mt_rand( 0, count( $lyrics ) - 1 ) ];
}
