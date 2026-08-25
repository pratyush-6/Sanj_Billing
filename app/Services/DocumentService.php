<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class DocumentService
{
    public function store(Model $documentable, UploadedFile $file, string $type = 'Other', ?string $notes = null): Document
    {
        $path = $file->store("documents/{$documentable->getMorphClass()}/{$documentable->getKey()}", 'local');

        return $documentable->documents()->create([
            'name' => $file->getClientOriginalName(),
            'type' => $type,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
            'notes' => $notes,
        ]);
    }
}
