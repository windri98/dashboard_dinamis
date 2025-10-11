<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
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
        // Normalize endpoint - coba dengan dan tanpa trailing slash
        $endpointWithSlash = rtrim($endpoint, '/') . '/';
        $endpointWithoutSlash = rtrim($endpoint, '/');
        
        return $query->where(function($q) use ($endpointWithSlash, $endpointWithoutSlash) {
                $q->where('endpoint', $endpointWithSlash)
                  ->orWhere('endpoint', $endpointWithoutSlash);
            })
            ->where('method', $method);
    }

    // Route Model Binding - gunakan slug instead of id
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Auto generate slug saat creating/updating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::generateSlug($model->name);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = static::generateSlug($model->name);
            }
        });
    }

    // Generate slug from name
    public static function generateSlug($name, $id = null)
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}