<?php

if (! function_exists('crazylogin_get_columns')) {
    /**
     * @see https://github.com/ST-DDT/CrazyLogin/blob/master/src/main/resources/config.yml
     */
    function crazylogin_get_columns()
    {
        return [
            // 'password',
            'name',
            'ips',
            'lastAction',
            'loginFails',
            'passwordExpired'
        ];
    }
}

if (! function_exists('crazylogin_init_table')) {

    function crazylogin_init_table()
    {
        $exists = [];
        $initialized = true;

        foreach (crazylogin_get_columns() as $column) {
            $exists[$column] = Schema::hasColumn('users', $column);

            if (! $exists[$column]) {
                $initialized = false;
            }
        }

        if ($initialized) {
            return;
        }

        $dbh = DB::connection()->getPdo();

        // 允许以下字段为空，防止登录插件 INSERT 时出现问题
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
            $exists['name']            || $table->string('name')->default('');
            $exists['ips']             || $table->string('ips')->default('');
            $exists['lastAction']      || $table->timestamp('lastAction')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $exists['loginFails']      || $table->integer('loginFails')->default(0);
            $exists['passwordExpired'] || $table->tinyInteger('passwordExpired')->default(0);
        });

        try {
            DB::table('users')->update(['name' => DB::raw('`player_name`')]);
        } catch (Exception $e) {
            app(Illuminate\Contracts\Debug\ExceptionHandler::class)->report($e);
        }
    }
}
