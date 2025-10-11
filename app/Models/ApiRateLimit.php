<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRateLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_endpoint_id',
        'ip_address',
        'request_count',
        'window_start',
    ];

    protected $casts = [
        'request_count' => 'integer',
        'window_start' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = false;

    // Relationships
    public function apiEndpoint()
    {
        return $this->belongsTo(ApiEndpoint::class);
    }
}