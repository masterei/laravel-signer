<?php

namespace Masterei\Signer\Models;

use Masterei\Signer\Config;
use Masterei\Signer\URLParser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Signed extends Model
{
    protected $fillable = [
        'path',
        'signature',
        'expired_at',
        'parameters'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = Config::connection();

        $this->table = Config::tableName();
    }

    protected function parameters(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => json_decode(unserialize($value)),
            set: fn (array | object $value) => serialize(json_encode($value)),
        );
    }

    /**
     * Converting timestamp into datetime format.
     */
    protected function expiredAt(): Attribute
    {
        return Attribute::make(
            get: fn (int | null $value) => $value ? Carbon::createFromTimestamp($value) : null,
        );
    }

    /**
     * Reconstruct model data into signed URL.
     * E.g: Signed::find(1)->url();
     */
    public function scopeUrl($query)
    {
        $model = $query->getModel();

        return URLParser::createSignedRoute(
            $model->parameters->data->route,
            (array) $model->parameters->query,
            $model->expired_at,
            $model->parameters->data->absolute
        )->url($model->parameters->data->prefix_domain);
    }

    /**
     * Retrieving un-expired signed data through its signature;
     * or can be filtered with path the included.
     */
    public static function findValidSignature(string $signature, string | null $path = null)
    {
        $query = self::query();

        // include indexed column
        if(!empty($path)){
            $query->wherePath($path);
        }

        $query->whereSignature($signature)
            ->where(function ($query){
                $query->where('expired_at', '>=', now()->timestamp)
                    ->orWhere('expired_at', null);
            });

        return $query->first();
    }
}
