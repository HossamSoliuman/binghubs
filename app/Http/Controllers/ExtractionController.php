<?php

namespace App\Http\Controllers;

use App\Models\Extraction;
use App\Models\File;
use App\Models\Table;
use App\Traits\ManagesFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtractionController extends Controller
{
    use ManagesFiles;

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

        switch ($type) {
            case 'existing_file':
                return $this->processExistingFile($request->existing_file, $filter);
            case 'uploaded_file':
                return $this->processUploadedFile($request->file, $filter);
            default:
                return $this->processTable($request->table, $filter);
        }
    }

    public function destroy(Extraction $extraction)
    {
        $this->deleteFile($extraction->extraction_result);
        $extraction->delete();
        return redirect()->route('extractions.index');
    }

    private function processExistingFile($existingFile, $filter)
    {
        return $this->filterFile('files/output/' . $existingFile, $filter, true);
    }

    private function processUploadedFile($file, $filter)
    {
        $filePath = $this->uploadFile($file, 'extraction_uploads');
        return $this->filterFile($filePath, $filter);
    }

    private function processTable($table, $filter)
    {
        return $this->filterTable($table, $filter);
    }

    private function filterFile($filePath, $filter, $isExistingFile = false)
    {
        $uniqueFilePath = $isExistingFile ? public_path($filePath) : $this->getUniqueFilePath($filePath);

        if (!$isExistingFile && file_exists(public_path($filePath))) {
            rename(public_path($filePath), $uniqueFilePath);
        }

        $outputFilePath = $this->generateOutputFilePath($uniqueFilePath);

        if (!$this->applyFileFilters($uniqueFilePath, $outputFilePath, $filter)) {
            return response()->json(['error' => 'Error processing file.'], 500);
        }

        Extraction::create([
            'extracted_from_type' => 'file',
            'extracted_from' => $filePath,
            'extraction_result' => 'extracted_files/' . basename($outputFilePath),
        ]);

        return $this->index();
    }

    private function getUniqueFilePath($filePath)
    {
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $fileExt = pathinfo($filePath, PATHINFO_EXTENSION);

        $uniqueFilePath = public_path($filePath);
        $counter = 1;

        while (file_exists($uniqueFilePath)) {
            $uniqueFilePath = public_path("{$fileName}({$counter}).{$fileExt}");
            $counter++;
        }

        return $uniqueFilePath;
    }

    private function generateOutputFilePath($inputFilePath)
    {
        $fileName = pathinfo($inputFilePath, PATHINFO_FILENAME);
        $outputFilePath = public_path('extracted_files/' . $fileName . '.csv');
        $counter = 1;

        while (file_exists($outputFilePath)) {
            $outputFilePath = public_path('extracted_files/' . $fileName . "({$counter}).csv");
            $counter++;
        }

        return $outputFilePath;
    }

    private function generateTableOutputFilePath($table)
    {
        $outputFilePath = public_path("extracted_files/{$table}.csv");
        $counter = 1;

        while (file_exists($outputFilePath)) {
            $outputFilePath = public_path("extracted_files/{$table}({$counter}).csv");
            $counter++;
        }

        return $outputFilePath;
    }

    private function applyFileFilters($inputFilePath, $outputFilePath, $filter)
    {
        if (!($inputFileHandle = fopen($inputFilePath, 'r')) || !($outputCsvFile = fopen($outputFilePath, 'w'))) {
            return false;
        }

        $headerRow = fgetcsv($inputFileHandle);
        fputcsv($outputCsvFile, $headerRow);

        $indices = array_flip($headerRow);
        $filters = $this->getFilters($filter, $indices);

        while (($rowData = fgetcsv($inputFileHandle)) !== false) {
            if ($this->passesFilters($rowData, $filters, $indices)) {
                fputcsv($outputCsvFile, $rowData);
            }
        }

        fclose($inputFileHandle);
        fclose($outputCsvFile);

        return true;
    }

    public function filterTable($table, $filter)
    {
        $tableColumns = DB::getSchemaBuilder()->getColumnListing($table);
        $tableColumns = array_filter($tableColumns, function ($field) {
            return $field !== 'id';
        });

        $tempCsvFile = tmpfile();
        fputcsv($tempCsvFile, $tableColumns);

        $creditScoreFilter = isset($filter['credit']) ? $filter['credit'] : null;
        $incomeFilter = isset($filter['income_range']) ? $filter['income_range'] : null;
        $genderFilter = isset($filter['gender']) ? $filter['gender'] : null;

        $query = DB::table($table);

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

        $filteredData = $query->get();

        foreach ($filteredData as $row) {
            $rowData = [];
            foreach ($tableColumns as $column) {
                $rowData[] = $row->{$column};
            }
            fputcsv($tempCsvFile, $rowData);
        }

        rewind($tempCsvFile);

        $outputFileName = $table . '.csv';
        $outputFilePath = public_path('extracted_files/' . $outputFileName);
        $counter = 1;

        while (file_exists($outputFilePath)) {
            $outputFileName =  $table . '(' . $counter . ').csv';
            $outputFilePath = public_path('extracted_files/' . $outputFileName);
            $counter++;
        }

        $outputCsvFile = fopen($outputFilePath, 'w');
        stream_copy_to_stream($tempCsvFile, $outputCsvFile);

        fclose($tempCsvFile);
        fclose($outputCsvFile);

        Extraction::create([
            'extracted_from_type' => 'table',
            'extracted_from' => $table,
            'extraction_result' => 'extracted_files/' . $outputFileName,
        ]);

        return $this->index();
    }
    private function getFilters($filter, $indices)
    {
        return [
            'state' => $this->parseFilter($filter['states'] ?? null, $indices),
            'dnc' => $this->parseFilter($filter['dnc'] ?? 'All', $indices),
            'age' => ['min' => $filter['min_age'] ?? null, 'max' => $filter['max_age'] ?? null],
            'creditscore' => $this->parseFilter($filter['credit'] ?? null, $indices),
            'income_range' => $this->parseFilter($filter['income_range'] ?? null, $indices),
            'gender' => $this->parseFilter($filter['gender'] ?? null, $indices),
        ];
    }

    private function parseFilter($filter, $indices)
    {
        if ($filter && is_string($filter)) {
            return explode(',', $filter);
        }
        return $filter;
    }

    private function passesFilters($rowData, $filters, $indices)
    {
        return $this->passesStateFilter($rowData, $filters, $indices)
            && $this->passesDncFilter($rowData, $filters, $indices)
            && $this->passesAgeFilter($rowData, $filters, $indices)
            && $this->passesCreditScoreFilter($rowData, $filters, $indices)
            && $this->passesIncomeFilter($rowData, $filters, $indices)
            && $this->passesGenderFilter($rowData, $filters, $indices);
    }

    private function passesStateFilter($rowData, $filters, $indices)
    {
        if (!isset($filters['state']) || !isset($indices['state'])) {
            return true;
        }
        return in_array($rowData[$indices['state']], $filters['state']);
    }

    private function passesDncFilter($rowData, $filters, $indices)
    {
        if (!isset($filters['dnc']) || !isset($indices['dnc'])) {
            return true;
        }
        $dncValue = $rowData[$indices['dnc']];
        return $filters['dnc'] === 'All' || $dncValue === $filters['dnc'];
    }

    private function passesAgeFilter($rowData, $filters, $indices)
    {
        if (!isset($filters['age']) || !isset($indices['age'])) {
            return true;
        }
        $ageValue = $rowData[$indices['age']];
        return ($filters['age']['min'] === null || $ageValue >= $filters['age']['min'])
            && ($filters['age']['max'] === null || $ageValue <= $filters['age']['max']);
    }

    private function passesCreditScoreFilter($rowData, $filters, $indices)
    {
        if (!isset($filters['creditscore']) || !isset($indices['creditscore'])) {
            return true;
        }
        return in_array($rowData[$indices['creditscore']], $filters['creditscore']);
    }

    private function passesIncomeFilter($rowData, $filters, $indices)
    {
        if (!isset($filters['income_range']) || !isset($indices['income_range'])) {
            return true;
        }
        return in_array($rowData[$indices['income_range']], $filters['income_range']);
    }

    private function passesGenderFilter($rowData, $filters, $indices)
    {
        if (!isset($filters['gender']) || !isset($indices['gender'])) {
            return true;
        }
        return in_array($rowData[$indices['gender']], $filters['gender']);
    }
}
