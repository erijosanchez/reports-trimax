<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UploadedFile;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{

    public function index()
    {
        $user = auth()->user();

        $files = UploadedFile::query()
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('is_public', true);
                });
            })
            ->latest()
            ->paginate(20);

        return view('files.index', compact('files'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,xlsx,xls,csv',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
        ]);

        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $filename, 'local');

        $uploadedFile = UploadedFile::create([
            'user_id' => auth()->id(),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getMimeType(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'is_public' => $request->boolean('is_public'),
            'description' => $request->description,
        ]);

        UserActivityLog::log(
            auth()->id(),
            'upload_file',
            'UploadedFile',
            $uploadedFile->id,
            "Subió archivo: {$uploadedFile->original_name}"
        );

        return back()->with('success', 'Archivo subido exitosamente');
    }

    public function view($id)
    {
        $file = UploadedFile::findOrFail($id);
        $user = auth()->user();

        if (!$file->canBeViewedBy($user)) {
            abort(403, 'No tienes permiso para ver este archivo');
        }

        $file->incrementViews();

        UserActivityLog::log(
            $user->id,
            'view_file',
            'UploadedFile',
            $file->id,
            "Visualizó archivo: {$file->original_name}"
        );

        $content = Storage::get($file->file_path);

        return view('files.view', compact('file', 'content'));
    }

    public function download($id)
    {
        $file = UploadedFile::findOrFail($id);
        $user = auth()->user();

        if (!$file->canBeDownloadedBy($user)) {
            abort(403, 'No tienes permiso para descargar este archivo');
        }

        $file->incrementDownloads();

        UserActivityLog::log(
            $user->id,
            'download_file',
            'UploadedFile',
            $file->id,
            "Descargó archivo: {$file->original_name}"
        );

        return Storage::download($file->file_path, $file->original_name);
    }

    public function destroy($id)
    {
        $file = UploadedFile::findOrFail($id);
        $user = auth()->user();

        if ($file->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'No tienes permiso para eliminar este archivo');
        }

        UserActivityLog::log(
            $user->id,
            'delete_file',
            'UploadedFile',
            $file->id,
            "Eliminó archivo: {$file->original_name}"
        );

        Storage::delete($file->file_path);
        $file->delete();

        return back()->with('success', 'Archivo eliminado');
    }
}
