<?php

return [
    App\Events\PluginWasEnabled::class => function () {
        // 创建 AuthMe 所需的字段
        $columns = [
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
        $exists = [];
        $initialized = true;

        foreach ($columns as $column) {
            $exists[$column] = Schema::hasColumn('users', $column);

            if (!$exists[$column]) {
                $initialized = false;
            }
        }

        if ($initialized) {
            return;
        }

        Schema::table('users', function ($table) use ($exists) {
            // 允许以下字段为空，防止登录插件 INSERT 时出现问题
            $table->string('email')->nullable()->change();
            $table->integer('score')->nullable()->change();
            $table->string('ip')->nullable()->change();
            $table->dateTime('last_sign_at')->nullable()->change();
            $table->dateTime('register_at')->nullable()->change();

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
            App\Models\User::all()->each(function ($user) {
                $playerName = $user->player_name;
                $user->realname = $playerName;
                $user->username = strtolower($playerName);
                $user->regip = $user->ip;
                $user->save();
            });
        } catch (Exception $e) {
            app(Illuminate\Contracts\Debug\ExceptionHandler::class)->report($e);
        }
    },
];
