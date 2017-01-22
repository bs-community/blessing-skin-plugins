<?php
/**
 * @Author: printempw
 * @Date:   2016-10-30 14:14:37
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-07 22:50:04
 */

namespace DataIntegration;

use DB;
use Schema;
use Option;
use App\Models\User;
use App\Models\Player;

class Utils
{
    public static function init()
    {
        // add username column
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function ($table) {
                $table->string('username')->comment = "Added by data-integration plugin.";
            });
        }

        // load options
        $items = [
            'da_adapter' => '',
            'da_connection' => serialize([
                // store serialized array to database
                'host'     => 'localhost',
                'port'     => 3306,
                'database' => '',
                'username' => '',
                'password' => '',
                'table'    => ''
            ]),
            'da_columns' => serialize([
                'username' => 'username',
                'password' => 'password',
                'ip'       => 'ip'
            ]),
            // target - overwrite user's password on skin server
            // skin   - overwrite user's password in target database
            'da_duplicated_prefer' => 'target',
            'da_bilateral'         => 'false',
            'da_verbose_log'       => 'false'
        ];

        foreach ($items as $key => $value) {
            if (!Option::has($key)) {
                Option::set($key, $value);
            }
        }
    }

    public static function getUsernameFromTargetByEmail($email)
    {
        $result = app('db.target')->select('email', $email);

        return @$result['username'];
    }

    public static function getUniquePlayer(User $user)
    {
        // do nothing if username is not defined
        if (!$user->username) return false;

        $player = $user->players->where('player_name', $user->username)->first();

        if (!$player)
            $player = self::addUniquePlayer($user);

        return $player;
    }

    public static function addUniquePlayer(User $user)
    {
        // do nothing if username is not defined
        if (!$user->username) return false;

        $player = new Player;

        $player->uid           = $user->uid;
        $player->player_name   = $user->username;
        $player->preference    = "default";
        $player->last_modified = \Utils::getTimeFormatted();
        $player->save();

        event(new \App\Events\PlayerWasAdded($player));

        return $player;
    }

    public static function checkUserExistTarget($username)
    {
        $columns = unserialize(option('da_columns'));

        if (app('db.target')->has($columns['username'], $username)) {
            return true;
        }

        return false;
    }

    public static function checkUserExistSelf($username)
    {
        $player = DB::table('players')->where('player_name', $username)->first();

        if (!$player) return false;

        return User::find($player->uid);
    }

    public static function getForms()
    {
        return require __DIR__."/forms.php";
    }
}
