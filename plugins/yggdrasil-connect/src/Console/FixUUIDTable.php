<?php

namespace LittleSkin\YggdrasilConnect\Console;

use App\Models\Player;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixUUIDTable extends Command implements Isolatable
{
    protected $signature = 'yggc:fix-uuid-table';
    protected $description = 'Delete dumplicate/redundant UUID, add pid row and unique constraint to name & uuid column';

    public function handle(): void
    {

        if (!Schema::hasTable('uuid')) {
            $this->info('UUID table does not exist. Nothing to do.');
            return;
        }

        $this->warn('This command will DELETE all dumplicate/redundant UUID records in your uuid table then add pid row and unique constraint to name & uuid column.');
        $this->warn('IT\'S IRREVERSIBLE! MAKE SURE YOU HAVE YOUR UUID TABLE BACKED UP BEFORE CONTINUE!');
        if (!$this->confirm('You\'ve been warned. Continue?')) {
            $this->info('Cancelled.');
            return;
        }

        $this->info('Deleting dumplicate names...');
        $duplicateNames = DB::table('uuid')
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name')
            ->toArray();
        $this->withProgressBar($duplicateNames, function ($name) {
            $uuids = DB::table('uuid')->where('name', $name)->orderBy('id')->get();
            $uuids->shift();
            foreach ($uuids as $uuid) {
                DB::table('uuid')->where('id', $uuid->id)->delete();
            }
        });
        $this->newLine(2);

        $this->info('Deleting dumplicate UUIDs...');
        $dumplicateUUIDs = DB::table('uuid')
            ->select('uuid')
            ->groupBy('uuid')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('uuid')
            ->toArray();
        $this->withProgressBar($dumplicateUUIDs, function ($uuid) {
            $uuids = DB::table('uuid')->where('uuid', $uuid)->orderBy('id')->get();
            $uuids->shift();
            foreach ($uuids as $uuid) {
                DB::table('uuid')->where('id', $uuid->id)->delete();
            }
        });
        $this->newLine(2);

        $this->info('Adding columns...');
        Schema::table('uuid', function (Blueprint $table) {
            if (!Schema::hasColumn('uuid', 'pid')) {
                $table->unsignedInteger('pid')->after('id')->unique()->nullable();
                $table->foreign('pid')->references('pid')->on('players')->cascadeOnDelete();
            } else {
                $this->info('pid column already exists. Skipping...');
            }
            if (!Schema::hasColumn('uuid', 'created_at')) {
                $table->timestamp('created_at')->after('uuid')->nullable();
            } else {
                $this->info('created_at column already exists. Skipping...');
            }
            if (!Schema::hasColumn('uuid', 'updated_at')) {
                $table->timestamp('updated_at')->after('created_at')->nullable();
            } else {
                $this->info('updated_at column already exists. Skipping...');
            }
        });
        Schema::table('ygg_log', function (Blueprint $table) {
            $table->string('parameters', 2048)->default('')->change();
        });
        $this->newLine(2);

        $this->info('Setting up pid column & Deleting redundant names...');
        $uuids = DB::table('uuid')->whereNull('pid')->get();
        $this->withProgressBar($uuids, function ($uuid) {
            $player = Player::where('name', $uuid->name)->first();
            if ($player) {
                DB::table('uuid')->where('id', $uuid->id)->update(['pid' => $player->pid]);
            } else {
                DB::table('uuid')->where('id', $uuid->id)->delete();
            }
        });
        $this->newLine(2);

        $this->info('Adding unique constraint to name & uuid column...');
        $this->line('This step may throw an exception if you are running this command for the second time.');
        $this->line('If you are sure that the uuid table is clean and constraint has been applied before, you can ignore this exception. No more actions needed.');
        $this->line('If you are not sure, please check the uuid table and make sure there are no duplicate names or uuids, then add unique contraint to name and uuid column manually.');
        Schema::table('uuid', function (Blueprint $table) {
            $table->unique('name')->change();
            $table->unique('uuid')->change();
        });
        $this->newLine();

        $this->info('Done.');
        $this->newline();
    }
}
