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

    // Relasi ke DynamicMenu dan DynamicMenuItem
        public function menu()
    {
        return $this->belongsTo(DynamicMenu::class, 'menu_id');
    }

    public function menuItem()
    {
        return $this->belongsTo(DynamicMenuItem::class, 'menu_item_id');
    }

    
}
