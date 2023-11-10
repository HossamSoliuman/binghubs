<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Table;
use Illuminate\Http\Request;
use App\Traits\ManagesFiles;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Builder;

class FileController extends Controller
{
    use ManagesFiles;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        $tables = Table::all();
        $files = File::orderBy('id', 'desc')->paginate(5);
        return view('files.upload')->with(['files' => $files, 'tables' => $tables]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        $this->deleteFile($file->input_file);
        $this->deleteFile($file->output_file);
        $file->delete();
        return redirect()->route('files.create');
    }
    public function filter(Request $request, File $file)
    {
        // Get the CSV data from the input file
        $csvData = array_map('str_getcsv', file(public_path($file->output_file)));

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
        $stateFilter = $request->input('states');
        $dncFilter = $request->input('dnc');
        $minAgeFilter = $request->input('min_age');
        $maxAgeFilter = $request->input('max_age');

        // Loop through the CSV data and apply filters
        foreach ($csvData as $rowData) {
            // Extract values based on dynamic column positions
            $state = $stateIndex !== false ? $rowData[$stateIndex] : null;
            $dncValue = $dncIndex !== false ? $rowData[$dncIndex] : null;
            $age = $ageIndex !== false ? $rowData[$ageIndex] : null;

            // phone,first_name,last_name,address,city,state,zip_code,age,income_range,id
            // did not pass 
            // 9146361897,,,,,,,,,
            // pass 
            // 9044830009,Carlene,Moody,"6211 Jack Wright Island Rd","St Augustine",FL,32092,70,"$60000 - $64999",324818

            // Apply filters (ignoring null values)
            // 0 0
            $statePass = !$stateFilter || in_array($state, explode(',', $stateFilter));
            $dncPass = is_null($dncFilter) || $dncFilter === 'All' || $dncValue === $dncFilter;
            $minAgePass = is_null($minAgeFilter) || $age >= $minAgeFilter;
            $maxAgePass = is_null($maxAgeFilter) || $age >= $maxAgeFilter;

            // If any of the filter conditions pass, add the row to the filtered data
            if ($statePass && $dncPass && $minAgePass && $maxAgePass) {
                $filteredCsvData[] = $rowData;
            }
        }

        // Create the output CSV file
        $outputCsvFilePath = public_path('files/output/filtered_output.csv');
        $outputCsvFile = fopen($outputCsvFilePath, 'w');

        // Write the filtered data to the output file
        foreach ($filteredCsvData as $row) {
            fputcsv($outputCsvFile, $row);
        }

        fclose($outputCsvFile);

        // Provide a download link for the filtered CSV
        return response()->download($outputCsvFilePath)->deleteFileAfterSend(true);
    }
}
