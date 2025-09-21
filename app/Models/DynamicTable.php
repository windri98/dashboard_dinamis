<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class DynamicTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'table_name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function columns()
    {
        return $this->hasMany(TableColumn::class)->orderBy('order');
    }

    public function activeColumns()
    {
        return $this->hasMany(TableColumn::class)
            ->where('is_active', true)
            ->orderBy('order');
    }

    public function menuItems()
    {
        return $this->hasMany(DynamicMenuItem::class, 'link_value')
            ->where('link_type', 'table');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods
    public function tableExists()
    {
        return Schema::hasTable($this->table_name);
    }

    public function createTable()
    {
        if (!$this->tableExists()) {
            Schema::create($this->table_name, function ($table) {
                $table->id();
                $table->timestamps();
            });
        }
    }

    public function dropTable()
    {
        if ($this->tableExists()) {
            Schema::dropIfExists($this->table_name);
        }
    }

    public function getIsUsedAttribute()
    {
        return $this->menuItems()->exists();
    }
}