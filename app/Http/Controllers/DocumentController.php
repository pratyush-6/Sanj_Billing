<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function download(Document $document)
    {
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->name);
    }
}
