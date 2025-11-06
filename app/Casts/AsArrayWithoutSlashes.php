<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;

class AsArrayWithoutSlashes implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        return json_decode($value, true);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Serialize the attribute when the model is serialized to array or JSON.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // Return the already-decoded array, not a JSON string
        return $value;
    }
}
