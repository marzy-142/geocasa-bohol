<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientViewedProperty extends Model
{
    protected $fillable = [
        'client_id',
        'property_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * Get the client that viewed the property
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the property that was viewed
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
