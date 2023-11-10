<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Http\Requests\StoreTableRequest;
use App\Http\Requests\UpdateTableRequest;
use App\Http\Resources\TableResource;
use Doctrine\DBAL\Schema\Schema;
use Hossam\Licht\Controllers\LichtBaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as FacadesSchema;

class TableController extends controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tables = Table::all();
        $tableNames = Table::pluck('name')->toArray();
        foreach ($tables as $table) {
            $recordCount = DB::table($table->name)->count();
            $table['record_count'] = $recordCount;
        }
        return view('tables.index', compact('tables'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTableRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTableRequest $request)
    {
        $data = $request->validated();
        $fields = [];

        foreach ($data['fields'] as $key => $field) {
            $fieldType = $data['field_types'][$key];
            $fieldIndexed = $data['field_indexed'][$key];

            if ($fieldType === 'int') {
                $fieldType = 'INT';
            } elseif ($fieldType === 'text') {
                $fieldType = 'TEXT';
            } else {
                $fieldType = 'VARCHAR(255)'; // Use an appropriate string length
            }

            if ($fieldIndexed === 'indexed') {
                // If the field is marked as indexed, add an index to it
                $fields[] = "$field $fieldType";
                $fields[] = "INDEX({$field})";
            } else {
                $fields[] = "$field $fieldType";
            }
        }

        // Add the primary key ID column
        $fields[] = 'id INT AUTO_INCREMENT PRIMARY KEY';

        $table = Table::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'fields' => $fields,
        ]);

        $tableFields = implode(', ', $fields);

        DB::statement("CREATE TABLE " . $data['name'] . " ($tableFields)");

        return redirect()->route('tables.index');
    }




    public function update(UpdateTableRequest $request, Table $table)
    {
        $table->update($request->validated());
        return $this->successResponse(TableResource::make($table));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function destroy(Table $table)
    {
        FacadesSchema::dropIfExists($table->name);
        $table->delete();
        return redirect()->route('tables.index');
    }
    public function fields($table)
    {
        $fields = DB::getSchemaBuilder()->getColumnListing($table);
        return response()->json($fields);
    }
    public function removeDuplicates4(Table $table)
    {
        $table_name = $table->name;
        $fields = DB::getSchemaBuilder()->getColumnListing($table->name);
        $fields = array_filter($fields, function ($field) {
            return $field !== 'id';
        });

        $conditions = [];
        foreach ($fields as $field) {
            $conditions[] = "t1.$field = t2.$field";
        }
        $conditions = implode(' AND ', $conditions);

        DB::statement("DELETE t1 FROM $table_name t1 INNER JOIN $table_name t2 WHERE $conditions AND t1.id > t2.id");
        return 'old';

        return redirect()->route('tables.index');
    }
    
    public function removeDuplicates(Table $table)
    {
        $table_name = $table->name;
        $fields = DB::getSchemaBuilder()->getColumnListing($table_name);
        $fields = array_filter($fields, function ($field) {
            return $field !== 'id';
        });

        // Step 1: Create a temporary table to store unique records
        $temp_table_name = $table_name . '_temp';
        DB::statement("CREATE TEMPORARY TABLE $temp_table_name AS SELECT MIN(id) as id FROM $table_name GROUP BY " . implode(", ", $fields));

        // Step 2: Create an index on the temporary table to speed up the join
        DB::statement("CREATE INDEX temp_index ON $temp_table_name (id)");

        // Step 3: Use a left join to select the records to delete
        $delete_query = "DELETE t1 FROM $table_name t1 LEFT JOIN $temp_table_name t2 ON t1.id = t2.id WHERE t2.id IS NULL";
        DB::statement($delete_query);

        // Step 4: Clean up by dropping the temporary table
        DB::statement("DROP TEMPORARY TABLE $temp_table_name");

        // return 'unique';

        return redirect()->route('tables.index');
    }
}
