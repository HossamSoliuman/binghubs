<?php

namespace App\Http\Controllers;

use App\Models\File as ModelsFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Traits\ManagesFiles;

class MatchController extends Controller
{
    use ManagesFiles;


    public function matchCSVWithDatabase(Request $request)
    {
        $selectedTable = $request->input('table');
        $inputFile = $this->uploadFile($request->file, 'files/input');
        $csvFilePath = public_path($inputFile);
        $csvData = array_map('str_getcsv', file($csvFilePath));
        $inputFileName = pathinfo($inputFile, PATHINFO_FILENAME);
        $outputDirectory = public_path('files/output');

        if (!File::exists($outputDirectory)) {
            File::makeDirectory($outputDirectory);
        }

        $outputFileName = $this->generateUniqueFileName($outputDirectory, $inputFileName, 'csv');
        $outputCsvFilePath = $outputDirectory . '/' . $outputFileName;
        $outputCsvData = [];

        $headerRow = array_shift($csvData);
        $dbFields = DB::getSchemaBuilder()->getColumnListing($selectedTable);
        $dbFields = array_filter($dbFields, fn ($field) => $field !== 'id');

        foreach ($csvData as $rowData) {
            $outputRow = [];
            $outputRow[] = $rowData[0];

            $whereClause = [];
            foreach ($headerRow as $field) {
                $whereClause[] = [$field, $rowData[array_search($field, $headerRow)]];
            }

            $records = DB::table($selectedTable)
                ->select($dbFields)
                ->where($whereClause)
                ->get();

            if ($records->isNotEmpty()) {
                foreach ($records as $record) {
                    $outputRow = array_values((array)$record);
                    $outputCsvData[] = $outputRow;
                }
            } else {
                $outputRow = array_merge($outputRow, array_fill(0, count($dbFields) - 1, ''));
                $outputCsvData[] = $outputRow;
            }
        }

        array_unshift($outputCsvData, $dbFields);

        $outputCsvFile = fopen($outputCsvFilePath, 'w');
        foreach ($outputCsvData as $row) {
            fputcsv($outputCsvFile, $row);
        }
        fclose($outputCsvFile);

        ModelsFile::create([
            'input_file' => $inputFile,
            'output_file' => 'files/output/' . $outputFileName,
        ]);

        session()->flash('success', 'CSV matching job is complete. You can download the result.');
        return redirect()->route('index');
    }

    private function generateUniqueFileName($directory, $baseName, $extension)
    {
        $counter = 1;
        $fileName = "{$baseName}.{$extension}";

        while (file_exists("{$directory}/{$fileName}")) {
            $fileName = "{$baseName}({$counter}).{$extension}";
            $counter++;
        }

        return $fileName;
    }




    public function upload(Request $request)
    {
        $selectedTable = $request->input('table');
        $filePublicPath = $this->uploadFile($request->file('file'), 'database_files');
        $filePath = public_path($filePublicPath);
        $file = fopen($filePath, 'r');
        $tableName = $selectedTable;
        $chunkSize = 1000;
        $header = fgetcsv($file); // Get the header row from the CSV

        $columns = DB::getSchemaBuilder()->getColumnListing($tableName);

        $records = [];

        while (($line = fgetcsv($file)) !== false) {
            $record = [];

            // Map CSV columns to database columns
            foreach ($header as $index => $columnName) {
                if (in_array($columnName, $columns)) {
                    $record[$columnName] = $line[$index];
                }
            }

            $records[] = $record;

            if (count($records) === $chunkSize) {
                $insertColumns = implode(', ', array_keys($records[0]));
                $insertValues = implode(', ', array_map(function ($record) {
                    return '(' . implode(', ', array_map(function ($value) {
                        return "'" . addslashes($value) . "'";
                    }, $record)) . ')';
                }, $records));

                $sql = "INSERT INTO {$tableName} ({$insertColumns}) VALUES {$insertValues}";

                DB::unprepared($sql);
                $records = [];
            }
        }

        if (!empty($records)) {
            $insertColumns = implode(', ', array_keys($records[0]));
            $insertValues = implode(', ', array_map(function ($record) {
                return '(' . implode(', ', array_map(function ($value) {
                    return "'" . addslashes($value) . "'";
                }, $record)) . ')';
            }, $records));
            $sql = "INSERT INTO {$tableName} ({$insertColumns}) VALUES {$insertValues}";
            DB::unprepared($sql);
        }

        fclose($file);
        $this->deleteFile($filePublicPath);
        session()->flash('success', 'CSV inserting job is complete');

        return redirect()->route('index');
    }
}
