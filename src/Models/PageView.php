<?php

namespace Dipantry\Analytics\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PageView
 *
 * @property int $id
 * @property string|null $session
 * @property string $uri
 * @property string|null $source
 * @property string $country
 * @property string|null $browser
 * @property string $device
 */
class PageView extends Model
{
    /** @var array */
    protected $fillable = [
        'session',
        'uri',
        'source',
        'country',
        'browser',
        'device',
    ];

    protected $table = 'page_views';

    public function __construct(array $attributes = [])
    {
        $this->table = config('analytics.table_prefix') . 'page_views';
        parent::__construct($attributes);
    }

    public function setSourceAttribute($value): void
    {
        $this->attributes['source'] = $value
            ? preg_replace('/https?:\/\/(www\.)?([a-z\-.]+)\/?.*/i', '$2', $value)
            : $value;
    }

    public function getTypeAttribute($value): string
    {
        return ucfirst($value);
    }

    public function scopeFilter($query, $period = 'today')
    {
        if (! in_array($period, ['today', 'yesterday'])) {
            [$interval, $unit] = explode('_', $period);

            return $query->where('created_at', '>=', now()->sub($unit, $interval));
        }

        if ($period === 'yesterday') {
            return $query->whereDate('created_at', today()->subDay()->toDateString());
        }

        return $query->whereDate('created_at', today());
    }
}