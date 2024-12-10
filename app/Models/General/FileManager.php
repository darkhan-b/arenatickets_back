<?php

namespace App\Models\General;

use Illuminate\Support\Facades\Storage;

class FileManager {

    public static function deleteOldExcels() {
        $files = Storage::files('excel');
        foreach($files as $file) {
            $timestamp = Storage::lastModified($file);
            if ($timestamp < (time() - 20*60)) {
                Storage::delete($file);
            }
        }
    }

}
