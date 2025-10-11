<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_endpoint_id',
        'ip_address',
        'user_agent',
        'request_method',
        'request_uri',
        'request_payload',
        'response_status',
        'response_message',
        'access_granted',
        'block_reason',
        'execution_time',
    ];

    protected $casts = [
        'access_granted' => 'boolean',
        'response_status' => 'integer',
        'execution_time' => 'float',
        'request_payload' => 'array',
        'created_at' => 'datetime',
    ];

    // Custom timestamps since we only use created_at
    const UPDATED_AT = null;

    // Relationships
    public function apiEndpoint()
    {
        return $this->belongsTo(ApiEndpoint::class);
    }

    // Scopes
    public function scopeBlocked($query)
    {
        return $query->where('access_granted', false);
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}