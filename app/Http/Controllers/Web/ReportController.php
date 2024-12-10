<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller {

    public function excel(Request $request) {
        return Storage::download('excel/'.$request->filename);
    }

}
