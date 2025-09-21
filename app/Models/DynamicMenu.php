<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'permission_key',
        'category',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(DynamicMenuItem::class)
        ->orderBy('order');
    }

    public function activeItems()
    {
        return $this->hasMany(DynamicMenuItem::class)
            ->where('is_active', true)
            ->orderBy('order');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Methods
    public function hasUserPermission($userPermissions, $isSuperAdmin = false)
    {
        if ($isSuperAdmin) return true;
        
        if (empty($this->permission_key)) return true;
        
        return isset($userPermissions[$this->permission_key]) && 
            in_array('read', $userPermissions[$this->permission_key]);
    }

    public function getCategoryLabelAttribute()
    {
        return $this->category === 'main' ? 'Menu Utama' : 'Pengaturan';
    }


    public function DynamicMenus()
    {
        return $this->hasMany(Permission::class, 'Permission_key_id');
    }
}