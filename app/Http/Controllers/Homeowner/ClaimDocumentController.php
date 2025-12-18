<?php

namespace App\Http\Controllers\Homeowner;

use App\Helper\CRM;
use App\Http\Controllers\Controller;
use App\Models\ClaimDocument;
use App\Services\CrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClaimDocumentController extends Controller
{
    public function index(Request $request)
    {
        $homeId = $request->query('home_id') ?? optional($request->user()->homes()->first())->id;

        $docs = ClaimDocument::when($homeId, fn($q) => $q->where('home_id', $homeId))
            ->latest()
            ->paginate(12);

        return view('homeowner.claim_documents.index', compact('docs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'home_id'          => 'required|integer',
            'title'            => 'required|string|max:255',
            'doc_type'         => 'required|string',
            'notes'            => 'nullable|string',
            'date_of_document' => 'required|date',
            'file'             => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx',
            'file_url'         => 'nullable|url',
        ]);

        try {
            $data = $request->only([
                'home_id',
                'title',
                'doc_type',
                'notes',
                'date_of_document'
            ]);

            $data['uploaded_by'] = $request->user()->id;

            /** FILE UPLOAD */
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $customName = $file->getClientOriginalName();
                $path = $file->store('claim_docs', 'public');
                $data['file_path'] = $path;

                // Build CRM payload for local file
                $mediaStoragePayload = [
                    'file'   => new \CURLFile($file->getRealPath(), $file->getMimeType(), $customName),
                    'hosted' => false,
                    'fileUrl' => null,
                    'name'   => $customName,
                ];
            }

            /** URL AS DOCUMENT */
            if ($request->filled('file_url')) {
                $fileUrl = $request->file_url;
                $data['file_path'] = $fileUrl;

                // Build CRM payload for hosted file
                $mediaStoragePayload = [
                    'file'   => null,
                    'hosted' => true,
                    'fileUrl' => $fileUrl,
                    'name'   => $data['title'] ?? 'Document',
                ];
            }

            // Save in local DB
            $doc = ClaimDocument::create($data);

            // Send to CRM
            try {
                $user = Auth::user();
                $agency = $user->agency;
                $crmService = new CrmService($agency);
                $locationId = CRM::getDefault('primary_location', '', $agency);
                $crmService->uploadFile($mediaStoragePayload, $locationId);
            } catch (\Exception $e) {
                Log::error('Failed to upload document to CRM: ' . $e->getMessage());
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Document uploaded successfully.',
                'doc'     => $doc,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function destroy(ClaimDocument $claimDocument)
    {
        if ($claimDocument->file_path && !filter_var($claimDocument->file_path, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($claimDocument->file_path);
        }

        $claimDocument->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    public function download($id)
    {
        $doc = ClaimDocument::findOrFail($id);

        if (!$doc->file_path) {
            abort(404);
        }

        // External URL
        if (filter_var($doc->file_path, FILTER_VALIDATE_URL)) {
            return redirect($doc->file_path);
        }

        // Local file download
        $fullPath = storage_path('app/public/' . $doc->file_path);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        return response()->download($fullPath);
    }
    public function show(ClaimDocument $claimDocument)
    {
        return view('homeowner.claim_documents.view', compact('claimDocument'));
    }
}
