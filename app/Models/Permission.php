<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $fillable = [
        'dyn_menus_id', 
        'dyn_menu_item_id', 
        'action_id'
    ];

    // Relasi ke Module
    public function permission_key()
    {
        return $this->belongsTo(DynamicMenu::class);
    }

    // Relasi ke Action
    public function action()
    {
        return $this->belongsTo(Action::class);
    }

    // Relasi ke Role (many-to-many)
    public function roles()
    {
        return $this->belongsToMany(Roles::class, 'role_permission');
    }

    public function dynamicMenu()
    {
        return $this->belongsTo(DynamicMenu::class, 'Permission_key_id');
    }

    public function DynamicMenus()
    {
        return $this->belongsTo(DynamicMenuItem::class, 'Permission_key_id');
    }
    
}
