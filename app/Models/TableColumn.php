<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TableColumn extends Model
{
    use HasFactory;

    protected $fillable = [
        'dynamic_table_id',
        'name',
        'column_name',
        'type',
        'options',
        'is_required',
        'is_searchable',
        'is_sortable',
        'show_in_list',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_searchable' => 'boolean',
        'is_sortable' => 'boolean',
        'show_in_list' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
        'options' => 'array',
    ];

    // Relationships
    public function dynamicTable()
    {
        return $this->belongsTo(DynamicTable::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeShowInList($query)
    {
        return $query->where('show_in_list', true);
    }

    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    public function scopeSortable($query)
    {
        return $query->where('is_sortable', true);
    }

    // Methods
    public function addColumnToTable()
    {
        $tableName = $this->dynamicTable->table_name;
        
        if (!Schema::hasColumn($tableName, $this->column_name)) {
            Schema::table($tableName, function ($table) {
                $column = null;
                
                switch ($this->type) {
                    case 'string':
                        $column = $table->string($this->column_name);
                        break;
                    case 'text':
                        $column = $table->text($this->column_name);
                        break;
                    case 'integer':
                        $column = $table->integer($this->column_name);
                        break;
                    case 'decimal':
                        $column = $table->decimal($this->column_name, 10, 2);
                        break;
                    case 'date':
                        $column = $table->date($this->column_name);
                        break;
                    case 'time':
                        $column = $table->time($this->column_name);
                        break;
                    case 'datetime':
                        $column = $table->dateTime($this->column_name);
                        break;
                    case 'boolean':
                        $column = $table->boolean($this->column_name)->default(false);
                        break;
                    case 'enum':
                        $options = $this->options['values'] ?? ['option1', 'option2'];
                        $column = $table->enum($this->column_name, $options);
                        break;
                    default:
                        $column = $table->string($this->column_name);
                }
                
                if ($column && !$this->is_required) {
                    $column->nullable();
                }
            });
        }
    }

    public function removeColumnFromTable()
    {
        $tableName = $this->dynamicTable->table_name;
        
        if (Schema::hasColumn($tableName, $this->column_name)) {
            Schema::table($tableName, function ($table) {
                $table->dropColumn($this->column_name);
            });
        }
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'string' => 'Text Pendek',
            'text' => 'Text Panjang',
            'integer' => 'Angka Bulat',
            'decimal' => 'Angka Desimal',
            'date' => 'Tanggal',
            'datetime' => 'Tanggal & Waktu',
            'boolean' => 'Ya/Tidak',
            'enum' => 'Pilihan'
        ];
        
        return $labels[$this->type] ?? $this->type;
    }
}