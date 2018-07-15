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

        Schema::table('users', function ($table) use ($exists) {
            $exists['name']            || $table->string('name')->default('');
            $exists['ips']             || $table->string('ips')->default('');
            $exists['lastAction']      || $table->timestamp('lastAction')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $exists['loginFails']      || $table->integer('loginFails')->default(0);
            $exists['passwordExpired'] || $table->tinyInteger('passwordExpired')->default(0);
        });

        Schema::table('users', function ($table) {
            $table->string('email', 100)->nullable()->change();
            $table->integer('score')->nullable()->change();
            $table->string('ip', 32)->nullable()->change();
            $table->dateTime('last_sign_at')->nullable()->change();
            $table->dateTime('register_at')->nullable()->change();
        });

        try {
            DB::table('users')->update(['name' => DB::raw('`player_name`')]);
        } catch (Exception $e) {
            app(Illuminate\Foundation\Exceptions\Handler::class)->report($e);
        }
    }
}

if (! function_exists('crazylogin_deinit_table')) {

    function crazylogin_deinit_table()
    {
        // SQLite 的行为有点不一样，这里可能会出问题
        try {
            Schema::table('users', function ($table) {
                $table->dropColumn(crazylogin_get_columns());
            });
        } catch (Exception $e) {
            app(Illuminate\Foundation\Exceptions\Handler::class)->report($e);
        }
    }
}
