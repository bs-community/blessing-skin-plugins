<?php

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
        if (!$user->player_name) return false;

        $player = $user->players->where('player_name', $user->player_name)->first();

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
