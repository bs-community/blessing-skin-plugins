<?php

if (!function_exists('authme_get_columns')) {
    /**
     * @see https://github.com/AuthMe/AuthMeReloaded/blob/master/docs/config.md
     */
    function authme_get_columns()
    {
        return [
            // 'id', 'password', 'ip', 'email'
            'username',
            'realname',
            'lastlogin',
            'x', 'y', 'z',
            'world',
            'regdate',
            'regip',
            'yaw',
            'pitch',
            'isLogged',
            'hasSession',
        ];
    }
}

if (!function_exists('authme_init_table')) {
    function authme_init_table()
    {
        $exists = [];
        $initialized = true;

        foreach (authme_get_columns() as $column) {
            $exists[$column] = Schema::hasColumn('users', $column);

            if (!$exists[$column]) {
                $initialized = false;
            }
        }

        if ($initialized) {
            return;
        }

        $dbh = DB::connection()->getPdo();

        // 允许以下字段为空，防止登录插件 INSERT 时出现问题
        // 已知 Authme 5.3.2 及以下会出现此问题，5.4.0 及以上正常
        $statements = [
            'ALTER TABLE `users` MODIFY `email` varchar(100) NULL',
            'ALTER TABLE `users` MODIFY `score` int(11) NULL',
            'ALTER TABLE `users` MODIFY `ip` varchar(32) NULL',
            'ALTER TABLE `users` MODIFY `last_sign_at` datetime NULL',
            'ALTER TABLE `users` MODIFY `register_at` datetime NULL',
        ];

        foreach ($statements as $sql) {
            $prefix = get_db_config()['prefix'];
            $sql = str_replace('users', "{$prefix}users", $sql);
            $dbh->exec($sql);
        }

        Schema::table('users', function ($table) use ($exists) {
            $exists['username'] || $table->string('username')->nullable();
            $exists['realname'] || $table->string('realname')->nullable();
            $exists['lastlogin'] || $table->bigInteger('lastlogin')->nullable();
            $exists['x'] || $table->double('x')->default(0);
            $exists['y'] || $table->double('y')->default(0);
            $exists['z'] || $table->double('z')->default(0);
            $exists['world'] || $table->string('world')->default('world');
            $exists['regdate'] || $table->bigInteger('regdate')->default(0);
            $exists['regip'] || $table->string('regip', 40)->nullable();
            $exists['yaw'] || $table->float('yaw')->nullable();
            $exists['pitch'] || $table->float('pitch')->nullable();
            $exists['isLogged'] || $table->smallInteger('isLogged')->default(0);
            $exists['hasSession'] || $table->smallInteger('hasSession')->default(0);
        });

        try {
            DB::table('users')->update(['realname' => DB::raw('`player_name`')]);
            DB::table('users')->update(['username' => DB::raw('LOWER(`player_name`)')]);
            DB::table('users')->update(['regip' => DB::raw('`ip`')]);
        } catch (Exception $e) {
            app(Illuminate\Contracts\Debug\ExceptionHandler::class)->report($e);
        }
    }
}
