<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    
    // Fix: konsisten pakai 'menu_id' (tanpa 's')
    protected $fillable = [
        'menu_id',  // <- Perbaiki ini
        'menu_item_id', 
        'action_id'
    ];


    // Relasi ke DynamicMenu dan DynamicMenuItem
    public function menu()
    {
        return $this->belongsTo(DynamicMenu::class, 'menu_id');
    }

    public function menuItem()
    {
        return $this->belongsTo(DynamicMenuItem::class, 'menu_item_id');
    }

    public function action()
    {
        return $this->belongsTo(Action::class, 'action_id');
    }
}