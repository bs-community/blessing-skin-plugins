<?php

if (! function_exists('authme_get_columns')) {
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
            'hasSession'
        ];
    }
}

if (! function_exists('authme_init_table')) {

    function authme_init_table()
    {
        $exists = [];
        $initialized = true;

        foreach (authme_get_columns() as $column) {
            $exists[$column] = Schema::hasColumn('users', $column);

            if (! $exists[$column]) {
                $initialized = false;
            }
        }

        if ($initialized) {
            return;
        }

        Schema::table('users', function ($table) use ($exists) {
            $exists['username']   || $table->string('username')->nullable();
            $exists['realname']   || $table->string('realname')->nullable();
            $exists['lastlogin']  || $table->bigInteger('lastlogin')->nullable();
            $exists['x']          || $table->double('x')->default(0);
            $exists['y']          || $table->double('y')->default(0);
            $exists['z']          || $table->double('z')->default(0);
            $exists['world']      || $table->string('world')->default('world');
            $exists['regdate']    || $table->bigInteger('regdate')->default(0);
            $exists['regip']      || $table->string('regip', 40)->nullable();
            $exists['yaw']        || $table->float('yaw')->nullable();
            $exists['pitch']      || $table->float('pitch')->nullable();
            $exists['isLogged']   || $table->smallInteger('isLogged')->default(0);
            $exists['hasSession'] || $table->smallInteger('hasSession')->default(0);
        });

        try {
            DB::table('users')->update(['realname' => DB::raw('`player_name`')]);
            DB::table('users')->update(['username' => DB::raw('LOWER(`player_name`)')]);
            DB::table('users')->update(['regip' => DB::raw('`ip`')]);
        } catch (Exception $e) {
            app(Illuminate\Foundation\Exceptions\Handler::class)->report($e);
        }
    }
}

if (! function_exists('authme_deinit_table')) {

    function authme_deinit_table()
    {
        // SQLite 的行为有点不一样，这里可能会出问题
        try {
            Schema::table('users', function ($table) {
                $table->dropColumn(authme_get_columns());
            });
        } catch (Exception $e) {
            app(Illuminate\Foundation\Exceptions\Handler::class)->report($e);
        }
    }
}
