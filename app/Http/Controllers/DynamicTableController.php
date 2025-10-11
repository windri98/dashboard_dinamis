<?php
namespace App\Http\Controllers;

use App\Models\TableColumn;
use Illuminate\Support\Str;
use App\Models\DynamicTable;
use Illuminate\Http\Request;
use App\Models\DynamicMenuItem;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DynamicTableController extends Controller
{
    public function index()
    {
        $tables = DynamicTable::with(['columns', 'menuItems'])->get();
        return view('settings.dynamic-tables.index', [
            'tables' => $tables
        ]);
    }

    public function create()
    {
        return view('settings.dynamic-tables.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Generate unique table name
        $baseName = 'dyn_' . Str::slug($validated['name'], '_');
        $tableName = $baseName;
        $counter = 1;
        
        while (DynamicTable::where('table_name', $tableName)->exists()) {
            $tableName = $baseName . '_' . $counter;
            $counter++;
        }

        $validated['table_name'] = $tableName;

        DB::beginTransaction();
        try {
            DB::transaction(function () use ($validated) {
                $table = DynamicTable::create($validated);
                $table->createTable();
            });

            return redirect()->route('settings.dynamic-tables.index')
                ->with('success', 'Tabel berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->route('settings.dynamic-tables.index')
                ->with('error', 'Gagal membuat tabel: ' . $e->getMessage());
        }
    }

    public function show(DynamicTable $dynamicTable)
    {
        $dynamicTable->load(['columns', 'menuItems']);
        return view('settings.dynamic-tables.show', compact('dynamicTable'));
    }

    public function edit(DynamicTable $dynamicTable)
    {
        return view('settings.dynamic-tables.edit', compact('dynamicTable'));
    }

    public function update(Request $request, DynamicTable $dynamicTable)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $dynamicTable->update($validated);
        
        return redirect()->route('settings.dynamic-tables.index')
            ->with('success', 'Tabel berhasil diperbarui');
    }

    public function destroy(DynamicTable $dynamicTable)
    {

        // Check if table is being used
        $isUsed = DynamicMenuItem::where('link_type', 'table')
        ->where('link_value', $dynamicTable->id)
        ->count() > 0;
        
        if ($isUsed) {
            return back()->with('error', 'Tabel sedang digunakan oleh menu item. Hapus menu item terlebih dahulu.');
        }

        DB::beginTransaction();
        
        try {
            $dynamicTable->dropTable();
            $dynamicTable->delete();
            
            DB::commit();
            
            return redirect()->route('settings.dynamic-tables.index')
                ->with('success', 'Tabel berhasil dihapus');
                
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return redirect()->route('settings.dynamic-tables.index')
                ->with('error', 'Gagal menghapus tabel: ' . $e->getMessage());
        }
        
    }

    public function columns(DynamicTable $dynamicTable)
    {
        $dynamicTable->load('columns');
        return view('settings.dynamic-tables.columns', [
            'dynamicTable' => $dynamicTable
        ]);
    }

        public function storeColumn(Request $request, DynamicTable $dynamicTable)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:string,text,integer,decimal,boolean,date,datetime,select,radio,checkbox,file,image',
            'is_required' => 'boolean',
            'is_searchable' => 'boolean',
            'is_sortable' => 'boolean',
            'show_in_list' => 'boolean',
            'order' => 'required|integer|min:0',
            'options' => 'nullable|array',
        ]);
        
        if (in_array($validated['type'], ['select', 'radio', 'checkbox'])) {
            $request->validate([
                'options.values' => 'required|array|min:1',
                'options.values.*' => 'required|string|max:255',
            ]);
        }
        
        $validated['dynamic_table_id'] = $dynamicTable->id;
        $validated['column_name'] = Str::snake(Str::slug($validated['name'], '_'));

        // Simpan record kolom di DB
        DB::beginTransaction();
        try {
            $column = TableColumn::create($validated);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menambah kolom: ' . $e->getMessage());
        }

        // Tambah kolom fisik ke tabel (di luar transaction)
        try {
            $column->addColumnToTable();
        } catch (\Exception $e) {
            // Kalau gagal di schema, hapus record supaya konsisten
            $column->delete();
            return back()->with('error', 'Gagal menambah kolom: ' . $e->getMessage());
        }

        return back()->with('success', 'Kolom berhasil ditambahkan');
    }



    // public function updateColumn(Request $request, TableColumn $column)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'is_required' => 'boolean',
    //         'is_searchable' => 'boolean',
    //         'is_sortable' => 'boolean',
    //         'show_in_list' => 'boolean',
    //         'order' => 'required|integer|min:0',
    //         'is_active' => 'boolean',
    //         'options' => 'nullable|array',
    //     ]);

    //     $column->update($validated);
        
    //     return back()->with('success', 'Kolom berhasil diperbarui');
    // }

    public function updateColumn(Request $request, TableColumn $column)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_required' => 'boolean',
            'is_searchable' => 'boolean',
            'is_sortable' => 'boolean',
            'show_in_list' => 'boolean',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
        ]);

        // Validasi khusus untuk select, radio, checkbox (kalau type-nya butuh options)
        if (in_array($column->type, ['select', 'radio', 'checkbox'])) {
            $request->validate([
                'options.values' => 'required|array|min:1',
                'options.values.*' => 'required|string|max:255',
            ]);
        }

        // Generate column_name baru dari name
        $newColumnName = Str::snake(Str::slug($validated['name'], '_'));
        $oldColumnName = $column->column_name;
        
        // Cek apakah nama kolom berubah
        $columnNameChanged = $oldColumnName !== $newColumnName;
        
        if ($columnNameChanged) {
            $validated['column_name'] = $newColumnName;
        }

        // Update dengan transaction
        DB::beginTransaction();
        try {
            $column->update($validated);
            
            // Kalau nama kolom berubah, rename kolom fisik di database
            if ($columnNameChanged) {
                Schema::table($column->dynamicTable->table_name, function (Blueprint $table) use ($oldColumnName, $newColumnName) {
                    $table->renameColumn($oldColumnName, $newColumnName);
                });
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memperbarui kolom: ' . $e->getMessage());
        }
        
        return back()->with('success', 'Kolom berhasil diperbarui');
    }


        public function destroyColumn(TableColumn $column)
    {
        // Hapus kolom fisik dulu (di luar transaksi)
        try {
            $column->removeColumnFromTable();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus kolom fisik: ' . $e->getMessage());
        }

        // Hapus record kolom di DB
        DB::beginTransaction();
        try {
            $column->delete();
            DB::commit();
            return back()->with('success', 'Kolom berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus record kolom: ' . $e->getMessage());
        }
    }


}