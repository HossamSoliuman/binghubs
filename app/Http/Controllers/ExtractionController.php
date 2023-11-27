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
        // Define the output file path
        $outputFileName = 'extracted_file_' . time() . '_.csv';
        $outputCsvFilePath = public_path('extracted_files/' . $outputFileName);

        // Open the input file as a read-only stream
        $inputFileHandle = fopen(public_path($file), 'r');
        if ($inputFileHandle === false) {
            return "Error opening the input file.";
        }

        // Open the output file as a write-only stream
        $outputCsvFile = fopen($outputCsvFilePath, 'w');
        if ($outputCsvFile === false) {
            return "Error creating the output file.";
        }

        // Get the CSV header row to determine dynamic column positions
        $headerRow = fgetcsv($inputFileHandle);

        // Determine the column positions dynamically
        $stateIndex = array_search('state', $headerRow);
        $dncIndex = array_search('dnc', $headerRow);
        $ageIndex = array_search('age', $headerRow);

        $creditScoreIndex = array_search('creditscore', $headerRow);
        $incomeIndex = array_search('income_range', $headerRow);
        $genderIndex = array_search('gender', $headerRow);

        // Define your filter variables
        $stateFilter = isset($filter['states']) ? $filter['states'] : null;
        $dncFilter = isset($filter['dnc']) ? $filter['dnc'] : 'All';
        $minAgeFilter = isset($filter['min_age']) ? $filter['min_age'] : null;
        $maxAgeFilter = isset($filter['max_age']) ? $filter['max_age'] : null;

        $creditScoreFilter = isset($filter['credit']) ? $filter['credit'] : null;
        $incomeFilter = isset($filter['income_range']) ? $filter['income_range'] : null;
        $genderFilter = isset($filter['gender']) ? $filter['gender'] : null;

        // Initialize a flag to write the header row
        $writeHeader = true;

        // Process each line in the input file
        while (($rowData = fgetcsv($inputFileHandle)) !== false) {
            // Check if it's the header row
            if ($writeHeader) {
                fputcsv($outputCsvFile, $headerRow); // Write the header
                $writeHeader = false;
                continue;
            }

            // Extract values based on dynamic column positions
            $state = $stateIndex !== false ? $rowData[$stateIndex] : null;
            $dncValue = $dncIndex !== false ? $rowData[$dncIndex] : null;
            $age = $ageIndex !== false ? $rowData[$ageIndex] : null;
            //
            $creditScore = $creditScoreIndex !== false ? $rowData[$creditScoreIndex] : null;
            $income = $incomeIndex !== false ? $rowData[$incomeIndex] : null;
            $gender = $genderIndex !== false ? $rowData[$genderIndex] : null;

            // Apply your filter conditions here
            $statePass = !$stateFilter || in_array($state, explode(',', $stateFilter));
            $dncPass = is_null($dncFilter) || $dncFilter === 'All' || $dncValue === $dncFilter;
            $minAgePass = is_null($minAgeFilter) || $age >= $minAgeFilter;
            $maxAgePass = is_null($maxAgeFilter) || $age <= $maxAgeFilter;
            //
            $creditScorePass = !$creditScoreFilter || in_array($creditScore, $creditScoreFilter);
            $incomePass = !$incomeFilter || in_array($income, $incomeFilter);
            $genderPass = !$genderFilter || in_array($gender, $genderFilter);


            // If all filter conditions pass, write the row to the output file
            if ($statePass && $dncPass && $minAgePass && $maxAgePass && $creditScorePass && $incomePass && $genderPass) {
                fputcsv($outputCsvFile, $rowData);
            }
        }

        // Close the input and output files
        fclose($inputFileHandle);
        fclose($outputCsvFile);

        // Create a record in the database (if needed)
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
        $tableColumns = DB::getSchemaBuilder()->getColumnListing($table);
        $tableColumns = array_filter($tableColumns, function ($field) {
            return $field !== 'id';
        });

        // Create a temporary CSV file
        $tempCsvFile = tmpfile();

        // Write the header row to the CSV file
        fputcsv($tempCsvFile, $tableColumns);

        $creditScoreFilter = isset($filter['credit']) ? $filter['credit'] : null;
        $incomeFilter = isset($filter['income_range']) ? $filter['income_range'] : null;
        $genderFilter = isset($filter['gender']) ? $filter['gender'] : null;
        // Build the query dynamically based on the filter criteria
        $query = DB::table($table); // Use the specified table name

        if (in_array('credit', $tableColumns)) {
            if (isset($filter['credit'])) {
                $query->whereIn('credit', $creditScoreFilter);
            }
        }

        if (in_array('income_range', $tableColumns)) {
            if (isset($filter['income_range'])) {
                $query->whereIn('income_range', $incomeFilter);
            }
        }

        if (in_array('gender', $tableColumns)) {
            if (isset($filter['gender'])) {
                $query->whereIn('gender', $genderFilter);
            }
        }

        if (in_array('state', $tableColumns)) {
            if (isset($filter['states'])) {
                $states = explode(',', $filter['states']);
                $query->whereIn('state', $states);
            }
        }
        if (in_array('dnc', $tableColumns)) {
            if (isset($filter['dnc']) && $filter['dnc'] != "All") {
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
