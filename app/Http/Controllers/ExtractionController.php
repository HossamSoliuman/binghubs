<?php

namespace App\Http\Controllers;

use App\Models\Extraction;
use App\Http\Requests\StoreExtractionRequest;
use App\Http\Requests\UpdateExtractionRequest;
use App\Http\Resources\ExtractionResource;
use App\Models\File;
use App\Models\Table;
use Hossam\Licht\Controllers\LichtBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ExtractionController extends LichtBaseController
{
    public function index()
    {
        $tables = Table::all();
        $extractions = Extraction::all();
        $files = File::orderBy('id', 'desc')->get();
        return view('extractions.index', compact('extractions', 'tables', 'files'));
    }


    public function store(Request $request)
    {
        $filter = $request->filter;
        $type = $request->extract_from_type;
        if ($type == 'existing_file') {
            return $this->filterFile('files/output/' . $request->existing_file, $filter);
        } else if ($type == 'uploaded_file') {
            $file_path = $this->uploadFile($request->file, 'extraction_uploads');
            return $this->filterFile($file_path, $filter);
        } else {
            return  $this->filterTable($request->table, $filter);
        }

        $extraction = Extraction::create($request->validated());
        return $this->successResponse(ExtractionResource::make($extraction));
    }

    public function destroy(Extraction $extraction)
    {
        $this->deleteFile($extraction->extraction_result);
        $extraction->delete();
        return $this->index();
    }
    public function filterFile($file, $filter)
    {
        // Get the CSV data from the input file
        $csvData = array_map('str_getcsv', file(public_path($file)));

        // Extract the header row to determine the fields dynamically
        $headerRow = array_shift($csvData);

        // Initialize an array to store filtered rows
        $filteredCsvData = [];

        // Add the header row to the filtered data
        $filteredCsvData[] = $headerRow;

        // Determine the column positions dynamically
        $stateIndex = array_search('state', $headerRow);
        $dncIndex = array_search('DNC', $headerRow);
        $ageIndex = array_search('age', $headerRow);

        // Extract filter values from the request
        $stateFilter = isset($filter['states']) ? $filter['states'] : null;
        $dncFilter = isset($filter['dnc']) ? $filter['dnc'] : 'All'; // Set 'All' as the default value
        $minAgeFilter = isset($filter['min_age']) ? $filter['min_age'] : null;
        $maxAgeFilter = isset($filter['max_age']) ? $filter['max_age'] : null;


        // Loop through the CSV data and apply filters
        foreach ($csvData as $rowData) {
            // Extract values based on dynamic column positions
            $state = $stateIndex !== false ? $rowData[$stateIndex] : null;
            $dncValue = $dncIndex !== false ? $rowData[$dncIndex] : null;
            $age = $ageIndex !== false ? $rowData[$ageIndex] : null;
            //age 70 filter age 60 -->no pass
            $statePass = !$stateFilter || in_array($state, explode(',', $stateFilter));
            $dncPass = is_null($dncFilter) || $dncFilter === 'All' || $dncValue === $dncFilter;
            $minAgePass = is_null($minAgeFilter) || $age >= $minAgeFilter;
            $maxAgePass = is_null($maxAgeFilter) || $age <= $maxAgeFilter;

            // If any of the filter conditions pass, add the row to the filtered data
            if ($statePass && $dncPass && $minAgePass && $maxAgePass) {
                $filteredCsvData[] = $rowData;
            }
        }

        // Create the output CSV file
        $outputFileName = 'extracted_file_' . time() . '_.csv';
        $outputCsvFilePath = public_path('extracted_files/' . $outputFileName);
        $outputCsvFile = fopen($outputCsvFilePath, 'w');


        foreach ($filteredCsvData as $row) {
            fputcsv($outputCsvFile, $row);
        }

        fclose($outputCsvFile);
        Extraction::create([
            'extracted_from_type' => 'file',
            'extracted_from' => $file,
            'extraction_result' => 'extracted_files/' . $outputFileName,
        ]);

        // Provide a download link for the filtered CSV
        return $this->index();
    }

    public function filterTable($table, $filter)
    {
        // Get the column names of the specified database table
        $tableColumns = DB::getSchemaBuilder()->getColumnListing($table);
        $tableColumns = array_filter($tableColumns, function ($field) {
            return $field !== 'id';
        });

        // Create a temporary CSV file
        $tempCsvFile = tmpfile();

        // Write the header row to the CSV file
        fputcsv($tempCsvFile, $tableColumns);

        // Build the query dynamically based on the filter criteria
        $query = DB::table($table); // Use the specified table name

        if (in_array('state', $tableColumns)) {
            if (isset($filter['states'])) {
                $states = explode(',', $filter['states']);
                $query->whereIn('state', $states);
            }
        }
        if (in_array('dnc', $tableColumns)) {
            if (isset($filter['dnc'])) {
                $query->where('DNC', $filter['dnc']);
            }
        }
        if (in_array('age', $tableColumns)) {
            if (isset($filter['min_age'])) {
                $query->where('age', '>=', $filter['min_age']);
            }
            if (isset($filter['max_age'])) {
                $query->where('age', '<=', $filter['max_age']);
            }
        }

        // Fetch the filtered data
        $filteredData = $query->get();

        // Write the filtered data to the CSV file
        foreach ($filteredData as $row) {
            $rowData = [];
            foreach ($tableColumns as $column) {
                $rowData[] = $row->{$column};
            }
            fputcsv($tempCsvFile, $rowData);
        }

        // Reset the file pointer to the beginning
        rewind($tempCsvFile);

        // Create a unique output CSV file name
        $outputFileName = 'filtered_table_' . $table . '_' . time() . '.csv';

        // Create the output CSV file in the public directory
        $outputCsvFilePath = public_path('extracted_files/' . $outputFileName);
        $outputCsvFile = fopen($outputCsvFilePath, 'w');

        // Copy the data from the temporary CSV file to the output CSV file
        stream_copy_to_stream($tempCsvFile, $outputCsvFile);

        // Close both files
        fclose($tempCsvFile);
        fclose($outputCsvFile);

        Extraction::create([
            'extracted_from_type' => 'table',
            'extracted_from' => $table,
            'extraction_result' => 'extracted_files/' . $outputFileName,
        ]);

        // Provide a download link for the filtered CSV
        return $this->index();
    }
}
