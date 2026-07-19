<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid()
    {
        static::creating(function ($model) {
            dd('test');
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->getBytes();
            }
        });
    }

    public function getUuidAttribute($value)
    {
        return Str::uuid($value)->toString();
    }
}