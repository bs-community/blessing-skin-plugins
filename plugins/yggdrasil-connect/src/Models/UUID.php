<?php

namespace LittleSkin\YggdrasilConnect\Models;

use App\Models\Player;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid as RamseyUuid;

class UUID extends Model
{
    protected $table = 'uuid';
    public $timestamps = true;
    protected $fillable = [
        'pid',
    ];

    protected $casts = [
        'pid' => 'integer',
        'name' => 'string',
        'uuid' => 'string',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'pid', 'pid');
    }

    public static function booted(): void
    {
        static::creating(function (UUID $model) {
            $model->name = $model->player->name;
            $model->uuid = option('ygg_uuid_algorithm') === 'v3' ? static::generateUuidV3($model->name) : RamseyUuid::uuid4()->getHex()->toString();
        });

        static::updating(function (UUID $model) {
            $model->name = $model->player->name;
            if (option('ygg_uuid_algorithm') === 'v3') {
                $model->uuid = static::generateUuidV3($model->name);
            }
        });

        static::retrieved(function (UUID $model) {
            $player = $model->player;
            if ($player && $player->name !== $model->name) {
                $model->name = $player->name;
                $model->save();
            }
        });
    }

    public static function generateUuidV3(string $name): string
    {
        // @see https://gist.github.com/games647/2b6a00a8fc21fd3b88375f03c9e2e603
        $data = hex2bin(md5('OfflinePlayer:'.$name));
        $data[6] = chr(ord($data[6]) & 0x0F | 0x30);
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        return bin2hex($data);
    }
}
