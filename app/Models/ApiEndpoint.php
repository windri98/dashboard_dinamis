<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'endpoint',
        'method',
        'table_name',
        'permission_id',
        'is_active',
        'use_ip_restriction',
        'ip_whitelist',
        'ip_blacklist',
        'use_rate_limit',
        'rate_limit_max',
        'rate_limit_period',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'use_ip_restriction' => 'boolean',
        'use_rate_limit' => 'boolean',
        'ip_whitelist' => 'array',
        'ip_blacklist' => 'array',
        'rate_limit_max' => 'integer',
        'rate_limit_period' => 'integer',
    ];

    // Relationships
    public function accessLogs()
    {
        return $this->hasMany(ApiAccessLog::class);
    }

    public function rateLimits()
    {
        return $this->hasMany(ApiRateLimit::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function dynamicTable()
    {
        return $this->belongsTo(DynamicTable::class, 'table_name', 'table_name');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByEndpoint($query, $endpoint, $method)
    {
        return $query->where('endpoint', $endpoint)
                     ->where('method', $method);
    }
}