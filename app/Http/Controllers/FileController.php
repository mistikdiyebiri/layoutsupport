<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Dosya yükleme işlemi
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // maksimum 10MB
            'ticket_id' => 'required|exists:tickets,id'
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        
        // Güvenli bir dosya adı oluştur
        $fileName = 'ticket_' . $request->ticket_id . '_' . 
                    Str::random(10) . '_' . time() . '.' . $extension;
        
        // Dosyayı kaydet
        $path = $file->storeAs('ticket_files', $fileName, 'public');
        
        return response()->json([
            'success' => true,
            'file_path' => $path,
            'file_name' => $originalName,
            'ticket_id' => $request->ticket_id
        ]);
    }
    
    /**
     * Dosya indirme işlemi
     *
     * @param string $fileName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($fileName)
    {
        // Dosyanın yolu
        $path = storage_path('app/public/ticket_files/' . $fileName);
        
        // Dosya yoksa 404 hatası ver
        if (!file_exists($path)) {
            abort(404);
        }
        
        // Dosyayı indir
        return Response::download($path);
    }
    
    /**
     * Dosya silme işlemi
     *
     * @param string $fileName
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($fileName)
    {
        // Dosyayı sil
        $deleted = Storage::disk('public')->delete('ticket_files/' . $fileName);
        
        return response()->json([
            'success' => $deleted
        ]);
    }
}
