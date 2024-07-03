<?php

namespace App\Http\Controllers;

use App\Models\Extraction;
use App\Http\Requests\StoreExtractionRequest;
use App\Http\Requests\UpdateExtractionRequest;
use App\Http\Resources\ExtractionResource;
use App\Http\Traits\ManagesFiles;
use App\Models\File;
use App\Models\Table;
use Hossam\Licht\Controllers\LichtBaseController;
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
        $inputFilePath = public_path($file);
        $inputFileName = pathinfo($inputFilePath, PATHINFO_FILENAME);
        $inputFileExt = pathinfo($inputFilePath, PATHINFO_EXTENSION);

        $counter = 1;
        $uniqueInputFilePath = $inputFilePath;

        while (file_exists($uniqueInputFilePath)) {
            $uniqueInputFilePath = public_path($inputFileName . '(' . $counter . ').' . $inputFileExt);
            $counter++;
        }

        rename($inputFilePath, $uniqueInputFilePath);

        $outputFileName = $inputFileName . '.csv';
        $outputFilePath = public_path('extracted_files/' . $outputFileName);
        $counter = 1;

        while (file_exists($outputFilePath)) {
            $outputFileName = $inputFileName . '(' . $counter . ').csv';
            $outputFilePath = public_path('extracted_files/' . $outputFileName);
            $counter++;
        }

        $inputFileHandle = fopen($uniqueInputFilePath, 'r');
        if ($inputFileHandle === false) {
            return "Error opening the input file.";
        }

        $outputCsvFile = fopen($outputFilePath, 'w');
        if ($outputCsvFile === false) {
            return "Error creating the output file.";
        }

        $headerRow = fgetcsv($inputFileHandle);

        $stateIndex = array_search('state', $headerRow);
        $dncIndex = array_search('dnc', $headerRow);
        $ageIndex = array_search('age', $headerRow);
        $creditScoreIndex = array_search('creditscore', $headerRow);
        $incomeIndex = array_search('income_range', $headerRow);
        $genderIndex = array_search('gender', $headerRow);

        $stateFilter = isset($filter['states']) ? $filter['states'] : null;
        $dncFilter = isset($filter['dnc']) ? $filter['dnc'] : 'All';
        $minAgeFilter = isset($filter['min_age']) ? $filter['min_age'] : null;
        $maxAgeFilter = isset($filter['max_age']) ? $filter['max_age'] : null;
        $creditScoreFilter = isset($filter['credit']) ? $filter['credit'] : null;
        $incomeFilter = isset($filter['income_range']) ? $filter['income_range'] : null;
        $genderFilter = isset($filter['gender']) ? $filter['gender'] : null;

        $writeHeader = true;

        while (($rowData = fgetcsv($inputFileHandle)) !== false) {
            if ($writeHeader) {
                fputcsv($outputCsvFile, $headerRow);
                $writeHeader = false;
                continue;
            }

            $state = $stateIndex !== false ? $rowData[$stateIndex] : null;
            $dncValue = $dncIndex !== false ? $rowData[$dncIndex] : null;
            $age = $ageIndex !== false ? $rowData[$ageIndex] : null;
            $creditScore = $creditScoreIndex !== false ? $rowData[$creditScoreIndex] : null;
            $income = $incomeIndex !== false ? $rowData[$incomeIndex] : null;
            $gender = $genderIndex !== false ? $rowData[$genderIndex] : null;

            $statePass = !$stateFilter || in_array($state, explode(',', $stateFilter));
            $dncPass = is_null($dncFilter) || $dncFilter === 'All' || $dncValue === $dncFilter;
            $minAgePass = is_null($minAgeFilter) || $age >= $minAgeFilter;
            $maxAgePass = is_null($maxAgeFilter) || $age <= $maxAgeFilter;
            $creditScorePass = !$creditScoreFilter || in_array($creditScore, $creditScoreFilter);
            $incomePass = !$incomeFilter || in_array($income, $incomeFilter);
            $genderPass = !$genderFilter || in_array($gender, $genderFilter);

            if ($statePass && $dncPass && $minAgePass && $maxAgePass && $creditScorePass && $incomePass && $genderPass) {
                fputcsv($outputCsvFile, $rowData);
            }
        }

        fclose($inputFileHandle);
        fclose($outputCsvFile);

        Extraction::create([
            'extracted_from_type' => 'file',
            'extracted_from' => $file,
            'extraction_result' => 'extracted_files/' . $outputFileName,
        ]);

        return $this->index();
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
            $outputFileName = 'filtered_table_' . $table . '(' . $counter . ').csv';
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
}
