<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:client');
    }

    public function index(Transaction $transaction)
    {
        $client = $this->getAuthenticatedClient();

        if (!$client || $transaction->client_id !== $client->id) {
            abort(403, 'Unauthorized access to transaction documents.');
        }

        $documents = $this->getTransactionDocuments($transaction);
        $documentCategories = $this->getDocumentCategories($transaction);

        return inertia('Client/Transactions/Documents/Index', [
            'transaction' => $transaction->load(['property', 'broker']),
            'documents' => $documents,
            'documentCategories' => $documentCategories,
        ]);
    }

    public function store(Request $request, Transaction $transaction)
    {
        $client = $this->getAuthenticatedClient();

        if (!$client || $transaction->client_id !== $client->id) {
            abort(403, 'Unauthorized access to transaction documents.');
        }

        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            'category' => 'required|string|in:financial,legal,property,personal,other',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $uploadedDocuments = [];
            $uploadPath = "transactions/{$transaction->id}/documents";

            foreach ($request->file('files') as $file) {
                $filename = $this->generateSecureFilename($file, $request->category);
                $filePath = $file->storeAs($uploadPath, $filename, 'private');

                $document = [
                    'id' => Str::uuid(),
                    'filename' => $file->getClientOriginalName(),
                    'stored_filename' => $filename,
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'category' => $request->category,
                    'description' => $request->description,
                    'uploaded_by' => 'client',
                    'uploaded_at' => now()->toISOString(),
                    'is_required' => $this->isRequiredDocument($request->category, $transaction),
                ];

                $uploadedDocuments[] = $document;
            }

            // Update transaction with new documents
            $existingDocuments = $transaction->client_documents ?? [];
            $transaction->update([
                'client_documents' => array_merge($existingDocuments, $uploadedDocuments),
                'client_last_viewed' => now(),
            ]);

            // Update engagement metrics
            $this->updateEngagementMetrics($transaction, $client, 'document_uploaded');

            Log::info('Client uploaded documents', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'document_count' => count($uploadedDocuments),
                'categories' => array_unique(array_column($uploadedDocuments, 'category')),
            ]);

            return back()->with('success', 'Documents uploaded successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to upload documents', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to upload documents. Please try again.');
        }
    }

    public function download(Transaction $transaction, string $documentId)
    {
        $client = $this->getAuthenticatedClient();

        if (!$client || $transaction->client_id !== $client->id) {
            abort(403, 'Unauthorized access to document.');
        }

        $documents = $transaction->client_documents ?? [];
        $document = collect($documents)->firstWhere('id', $documentId);

        if (!$document) {
            abort(404, 'Document not found.');
        }

        if (!Storage::disk('private')->exists($document['file_path'])) {
            abort(404, 'Document file not found.');
        }

        return Storage::disk('private')->download(
            $document['file_path'],
            $document['filename']
        );
    }

    public function destroy(Transaction $transaction, string $documentId)
    {
        $client = $this->getAuthenticatedClient();

        if (!$client || $transaction->client_id !== $client->id) {
            abort(403, 'Unauthorized access to document.');
        }

        try {
            $documents = $transaction->client_documents ?? [];
            $document = collect($documents)->firstWhere('id', $documentId);

            if (!$document) {
                return back()->with('error', 'Document not found.');
            }

            // Delete file from storage
            if (Storage::disk('private')->exists($document['file_path'])) {
                Storage::disk('private')->delete($document['file_path']);
            }

            // Remove from transaction
            $updatedDocuments = collect($documents)->reject(function ($doc) use ($documentId) {
                return $doc['id'] === $documentId;
            })->values()->toArray();

            $transaction->update(['client_documents' => $updatedDocuments]);

            Log::info('Client deleted document', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'document_id' => $documentId,
                'filename' => $document['filename'],
            ]);

            return back()->with('success', 'Document deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to delete document', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete document. Please try again.');
        }
    }

    protected function getAuthenticatedClient(): ?Client
    {
        $user = auth()->user();
        return $user ? $user->client : null;
    }

    protected function getTransactionDocuments(Transaction $transaction): array
    {
        $documents = $transaction->client_documents ?? [];
        
        // Group by category
        $grouped = collect($documents)->groupBy('category');
        
        return [
            'all' => $documents,
            'by_category' => $grouped->toArray(),
            'total_count' => count($documents),
            'required_count' => collect($documents)->where('is_required', true)->count(),
            'uploaded_count' => collect($documents)->where('uploaded_by', 'client')->count(),
        ];
    }

    protected function getDocumentCategories(Transaction $transaction): array
    {
        $categories = [
            'financial' => [
                'name' => 'Financial Documents',
                'description' => 'Bank statements, income proof, loan pre-approvals',
                'required' => true,
                'uploaded' => false,
            ],
            'legal' => [
                'name' => 'Legal Documents',
                'description' => 'Contracts, agreements, legal forms',
                'required' => true,
                'uploaded' => false,
            ],
            'property' => [
                'name' => 'Property Documents',
                'description' => 'Property photos, inspection reports, title documents',
                'required' => false,
                'uploaded' => false,
            ],
            'personal' => [
                'name' => 'Personal Documents',
                'description' => 'ID, passport, personal references',
                'required' => true,
                'uploaded' => false,
            ],
            'other' => [
                'name' => 'Other Documents',
                'description' => 'Additional supporting documents',
                'required' => false,
                'uploaded' => false,
            ],
        ];

        // Update uploaded status based on existing documents
        $existingDocuments = $transaction->client_documents ?? [];
        foreach ($categories as $key => &$category) {
            $category['uploaded'] = collect($existingDocuments)
                ->where('category', $key)
                ->where('uploaded_by', 'client')
                ->count() > 0;
        }

        return $categories;
    }

    protected function generateSecureFilename($file, string $category): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);
        
        return "{$category}_{$timestamp}_{$random}.{$extension}";
    }

    protected function isRequiredDocument(string $category, Transaction $transaction): bool
    {
        $requiredCategories = ['financial', 'legal', 'personal'];
        return in_array($category, $requiredCategories);
    }

    protected function updateEngagementMetrics(Transaction $transaction, Client $client, string $action): void
    {
        try {
            $engagement = \App\Models\ClientTransactionEngagement::where('transaction_id', $transaction->id)
                ->where('client_id', $client->id)
                ->first();

            if ($engagement) {
                $engagement->recordInteraction($action, [
                    'timestamp' => now()->toISOString(),
                    'action_type' => $action,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update engagement metrics', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
