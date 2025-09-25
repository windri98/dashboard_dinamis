<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicMenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'dynamic_menu_id',
        'name',
        'icon',
        'link_type',
        'link_value',
        'permission_key',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // Relationships
    public function menu()
    {
        return $this->belongsTo(DynamicMenu::class, 'dynamic_menu_id');
    }

    public function dynamicTable()
    {
        return $this->belongsTo(DynamicTable::class, 'link_value');
    }

    // Accessors
    public function getUrlAttribute()
    {
        switch ($this->link_type) {
            case 'table':
                return route('dashboard.table', $this->link_value);
            case 'route':
                try {
                    return route($this->link_value);
                } catch (\Exception $e) {
                    return '#';
                }
            case 'url':
                return $this->link_value;
            default:
                return '#';
        }
    }

    public function getTableNameAttribute()
    {
        if ($this->link_type === 'table' && $this->link_value) {
            $table = DynamicTable::find($this->link_value);
            return $table ? $table->name : null;
        }
        return null;
    }

    public function getLinkTypeLabelAttribute()
    {
        $labels = [
            'table' => 'Tabel Dinamis',
            'route' => 'Route Laravel',
            'url' => 'URL Eksternal'
        ];
        
        return $labels[$this->link_type] ?? $this->link_type;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods
    public function hasUserPermission($userPermissions, $isSuperAdmin = false)
    {
        if ($isSuperAdmin) return true;
        
        if (empty($this->permission_key)) return true;
        
        return isset($userPermissions[$this->permission_key]) && 
            in_array('read', $userPermissions[$this->permission_key]);
    }

    //reslasi many to many ke permission
        public function permissions()
    {
        return $this->hasMany(Permission::class, 'menu_item_id'); 
    }


}