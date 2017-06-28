<?php
/**
 * @Author: printempw
 * @Date:   2016-10-29 21:40:58
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-08 09:58:36
 */

namespace DataIntegration\Synchronizer;

use DB;
use Event;
use App\Models\User;
use App\Models\Player;
use DataIntegration\Log;
use DataIntegration\Utils;
use DataIntegration\Events\SyncTriggered;

abstract class BaseSynchronizer
{
    protected $db;

    protected $config;

    protected $columns;

    public function __construct()
    {
        $this->config  = unserialize(option('da_connection'));
        $this->columns = unserialize(option('da_columns'));

        if (!app('db.target')->hasTable($this->config['table'])) {
            exit("[数据对接] 错误：数据表 [{$this->config['table']}] 不存在");
        }
    }

    public function sync($username) {
        Event::fire(new SyncTriggered($this, $username));

        // 仅当 $username 存在于目标数据库中时进行同步
        // 从皮肤站同步用户至目标数据库（双向同步）请查看 DataIntegration\Listener\BilateralSync
        if (Utils::checkUserExistTarget($username)) {
            // 用户是否存在于皮肤站数据库中
            if (Utils::checkUserExistSelf($username)) {
                Log::info("[DataIntegration][$username][Self <=> Target] Sync triggered: ".static::class);
                // sync password if user exists in both two databases
                $this->syncPassword($username);
            } else {
                Log::info("[DataIntegration][$username][Self <== Target] Sync triggered: ".static::class);

                $this->syncFromTarget($username);
            }
        } else {
            Log::info("[DataIntegration][$username][<x> Target <x>] Waiting for BilateralSync. SyncTriggered event fired: ".static::class);
        }
    }

    public function encryptPassword($raw_passwd, $salt)
    {
        //
    }

    public function syncFromTarget($username)
    {
        //
    }

    public function syncFromSelf($username)
    {
        //
    }

    public function syncPassword($username)
    {
        $uid = DB::table('players')->where('player_name', $username)->first()->uid;

        $pwdSkin   = app('db.self')->where('uid', $uid)->first()->password;
        $pwdTarget = app('db.target')->select($this->columns['username'], $username)[$this->columns['password']];

        if ($pwdSkin == $pwdTarget) {
            Log::info("[DataIntegration][$username] Nothing to do.");
            // sync completed
            return true;
        } else {
            // sync password
            if (option('da_duplicated_prefer') == 'target') {
                // update password on skin server
                app('db.self')->where('uid', $uid)->update(['password' => $pwdTarget]);

                Log::info("[DataIntegration][$username] Update password in skin db to [$pwdTarget].");
            } else {
                app('db.target')->update($this->columns['password'], $pwdSkin, ['where' => "{$this->columns['username']}='$username'"]);

                Log::info("[DataIntegration][$username] Update password in target db to [$pwdSkin].");
            }
        }
    }
}
