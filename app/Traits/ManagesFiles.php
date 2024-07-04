<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;

trait ManagesFiles
{
    public function uploadFile($file, $directory)
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->extension();
        $fileName = $originalName . '.' . $extension;
        $filePath = $directory . '/' . $fileName;
        $counter = 1;

        while (file_exists($filePath)) {
            $fileName = $originalName . '(' . $counter . ').' . $extension;
            $filePath = $directory . '/' . $fileName;
            $counter++;
        }

        $filePath = $file->move($directory, $fileName);
        return $filePath;
    }

    public function deleteFile($filePath)
    {

        $file = public_path($filePath);
        $result = File::delete($file);
        return $result;
    }
}
