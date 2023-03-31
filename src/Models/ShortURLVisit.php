<?php

namespace SpecialYellow\ShortURL\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ShortURLVisit.
 *
 * @property int $id
 * @property int $short_url_id
 * @property string $ip_address
 * @property string $operating_system
 * @property string $operating_system_version
 * @property string $browser
 * @property string $browser_version
 * @property string $device_type
 * @property Carbon $visited_at
 * @property string $referer_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ShortURLVisit extends Model
{
    use HasFactory;

    const DEVICE_TYPE_MOBILE = 'mobile';

    const DEVICE_TYPE_DESKTOP = 'desktop';

    const DEVICE_TYPE_TABLET = 'tablet';

    const DEVICE_TYPE_ROBOT = 'robot';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'short_url_visits';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @deprecated This field is no longer used in Laravel 10 and above.
     *             It will be removed in a future release.
     *
     * @var array
     */
    protected $dates = [
        'visited_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'short_url_id' => 'integer',
        'visited_at'   => 'datetime',
    ];

    /**
     * @return Factory<ShortURLVisit>
     */
    protected static function newFactory()
    {
        $factoryConfig = config('short-url.factories');

        $modelFactory = app($factoryConfig[__CLASS__]);

        return $modelFactory::new();
    }

    /**
     * A URL visit belongs to one specific shortened URL.
     *
     * @return BelongsTo<ShortURL, ShortURLVisit>
     */
    public function shortURL(): BelongsTo
    {
        return $this->belongsTo(ShortURL::class, 'short_url_id');
    }

    public function colour( $key )
    {
        $md5 = md5( $key );
        $md5 = preg_replace( '/[^0-9a-fA-F]/', '', $md5 );
        $color = substr( $md5, 0, 6 );
        $hex = str_split( $color, 1 );
        $rgbd = array_map( 'hexdec', $hex );
        $rgba = array(
            ( $rgbd[0] * $rgbd[1] ),
            ( $rgbd[2] * $rgbd[3] ),
            ( $rgbd[4] * $rgbd[5] ),
        );
        return $rgba;
    }

    public function scopeUrlKey($query, $urlKey)
    {
        if ($urlKey) {
            return $query->whereUrlKey($urlKey);
        }
        return $query;
    }
}
