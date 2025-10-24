<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    /**
     * View a private document (admin only)
     */
    public function view(Request $request, string $filename)
    {
        // Ensure only admins can access documents
        $this->authorize('viewAny', \App\Models\User::class);
        
        // Decode the filename
        $filename = urldecode($filename);
        
        // Security check: ensure the file path is within allowed directories
        $allowedPaths = ['credentials/', 'properties/', 'documents/'];
        $isAllowed = false;
        
        foreach ($allowedPaths as $allowedPath) {
            if (str_starts_with($filename, $allowedPath)) {
                $isAllowed = true;
                break;
            }
        }
        
        if (!$isAllowed) {
            abort(403, 'Access denied to this file path');
        }
        
        // Check if file exists in private storage
        if (!Storage::disk('local')->exists($filename)) {
            abort(404, 'Document not found');
        }
        
        // Get file path and info
        $filePath = Storage::disk('local')->path($filename);
        $mimeType = Storage::disk('local')->mimeType($filename);
        $fileSize = Storage::disk('local')->size($filename);
        
        // Log document access for audit
        \Log::info('Document accessed', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'document' => $filename,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // Return file response
        return Response::file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
}