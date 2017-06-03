<?php
/**
 * @Author: printempw
 * @Date:   2016-11-25 21:48:57
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-06-03 21:35:29
 */

namespace Blessing\ImportV2Data;

use Log;
use Utils;
use Option;
use Storage;
use Database;
use App\Models\User;
use App\Models\Player;
use App\Models\Closet;
use App\Models\Texture;

class Migration
{
    public static function import($options)
    {
        @set_time_limit(0);

        $prefix        = config('database.connections.mysql.prefix');

        $v3_users      = $prefix."users";
        $v3_players    = $prefix."players";
        $v3_closets    = $prefix."closets";
        $v3_textures   = $prefix."textures";

        $user_imported      = 0;
        $user_duplicated    = 0;
        $texture_imported   = 0;
        $texture_duplicated = 0;

        // use db helper instead of fat ORM in some operations :(
        $db = Database::table($options['table_name'], true);

        $score = Option::get('user_initial_score');

        $sql = "SELECT * FROM `{$options['table_name']}` ORDER BY `uid`";
        $result = $db->query($sql);

        while ($row = $result->fetch_array()) {
            // compile patterns
            $name = str_replace('{username}', $row['username'], $options['texture_name_pattern']);

            if (Player::where('player_name', $row['username'])->get()->isEmpty()) {
                $user = new User;

                $user->email        = '';
                $user->nickname     = $row['username'];
                $user->score        = $score;
                $user->password     = $row['password'];
                $user->avatar       = '0';
                $user->ip           = $row['ip'];
                $user->permission   = '0';
                $user->last_sign_at = Utils::getTimeFormatted(time() - 86400);
                $user->register_at  = Utils::getTimeFormatted();

                $user->save();

                $models = ['steve', 'alex', 'cape'];

                $textures = [];

                foreach ($models as $model) {
                    if ($row["hash_$model"] != "") {
                        $name = str_replace('{model}', $model, $name);

                        $res = Texture::where('hash', $row["hash_$model"])->first();

                        if (!$res) {
                            $t = new Texture;
                            // file size in bytes
                            $size = Storage::disk('textures')->has($row["hash_$model"]) ? Storage::disk('textures')->size($row["hash_$model"]) : 0;

                            $t->name      = $name;
                            $t->type      = $model;
                            $t->likes     = 1;
                            $t->hash      = $row["hash_$model"];
                            $t->size      = ceil($size / 1024);
                            $t->uploader  = $user->uid;
                            $t->public    = $options['public'];
                            $t->upload_at = $row['last_modified'] ? : Utils::getTimeFormatted();

                            $t->save();

                            $textures[$model] = $t->tid;

                            $texture_imported++;

                            Log::info("[DataImport] Texture ".$row["hash_$model"]." saved.");
                        } else {
                            $textures[$model] = $res->tid;
                            $texture_duplicated++;

                            Log::info("[DataImport] Texture ".$row["hash_$model"]." duplicated.");
                        }
                    }
                }

                $p = new Player;

                $p->uid           = $user->uid;
                $p->player_name   = $row['username'];
                $p->preference    = $row['preference'];
                $p->last_modified = $row['last_modified'] ? : Utils::getTimeFormatted();

                $c = new Closet($user->uid);

                $items = [];

                foreach ($textures as $model => $tid) {
                    $property = "tid_$model";
                    $p->$property = $tid;

                    $items[] = array(
                        'tid'    => $tid,
                        'name'   => $name,
                        'add_at' => $row['last_modified'] ? : Utils::getTimeFormatted()
                    );
                }

                $c->setTextures(json_encode($items));

                $user_imported++;

                Log::info("[DataImport] User {$row['username']} saved.");
            } else {
                $user_duplicated++;

                Log::info("[DataImport] User {$row['username']} duplicated.");
            }


        }

        return [
            'user' => [
                'imported' => $user_imported,
                'duplicated' => $user_duplicated
            ],
            'texture' => [
                'imported' => $texture_imported,
                'duplicated' => $texture_duplicated
            ]
        ];
    }
}
