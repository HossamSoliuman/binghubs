<?php

use App\Http\Controllers\ExtractionController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\Iterator\FilecontentFilterIterator;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes([
    'register' => false
]);
Route::get('t', function () {
    // Input and output file paths
    $inputFile = 'input.csv';
    $outputFile = 'output.csv';

    // Read the input CSV file
    $rows = array_map('str_getcsv', file($inputFile));

    // Output CSV file header
    $outputHeader = ['phone'];

    // Initialize the output data array
    $outputData = [$outputHeader];

    // Iterate through each row and extract the "phone" column
    foreach ($rows as $row) {
        $phone = $row[0]; // Assuming "phone" is the first column (index 0)
        $outputData[] = [$phone];
    }

    // Write the output data to the new CSV file
    $fp = fopen($outputFile, 'w');
    foreach ($outputData as $fields) {
        fputcsv($fp, $fields);
    }
    fclose($fp);

    echo "Output CSV file created successfully.\n";
});
Route::middleware('auth')->group(function () {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/', [FileController::class, 'create'])->name('index');

    Route::get('table/{table}/fields', [TableController::class, 'fields'])->name('tables.fields');
    Route::get('table/{table}/remove-duplicates', [TableController::class, 'removeDuplicates'])->name('tables.duplicates');
    Route::post('files/{file}/filter', [FileController::class, 'filter'])->name('files.filter');
    Route::resource('files', FileController::class);
    Route::post('/match-csv', [MatchController::class, 'matchCSVWithDatabase']);
    Route::post('upload', [MatchController::class, 'upload']);
    Route::resource('tables', TableController::class);
    Route::resource('extractions', ExtractionController::class);
});
