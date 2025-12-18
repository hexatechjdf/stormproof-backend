<?php

namespace App\Http\Controllers\Admin;

use App\Helper\CRM;
use App\Http\Controllers\Controller;
use App\Http\Requests\Homeowner\StorePhotoReportRequest;
use App\Models\PhotoReport;
use App\Services\CrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class PhotoReportController extends Controller
{

    public function index(Request $request)
    {
        $homeId = $request->query('home_id') ?? optional($request->user()->homes()->first())->id;
        $reports = PhotoReport::when($homeId, fn($q) => $q->where('home_id', $homeId))->latest()->paginate(12);
        return view('admin.photo_reports.index', compact('reports'));
    }


    public function show(PhotoReport $photoReport)
    {
        return view('admin.photo_reports.show', compact('photoReport'));
    }


    public function store(StorePhotoReportRequest $request)
    {
        try {
            // Only pick the fields that exist in the database
            $data = $request->only([
                'inspection_id',
                'project_id',
                'title',
                'type',
                'description',
            ]);

            $data['uploaded_by'] = $request->user()->id;

            // Prepare payload for GHL API
            $payload = [];

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $customName = $file->getClientOriginalName();

                $payload = [
                    'file'   => new \CURLFile(
                        $file->getRealPath(),
                        $file->getMimeType(),
                        $customName
                    ),
                    'hosted' => false,
                    'name'   => $customName,
                ];

                // Save file locally in Laravel storage
                $path = $file->store('photo_reports', 'public');
                $data['pdf_path'] = $path;

                // If it's an image, save thumbnail
                if (str_starts_with($file->getMimeType(), 'image')) {
                    $thumbPath = $file->store('photo_reports/thumbnails', 'public');
                    $data['thumbnail_path'] = $thumbPath;
                }
            }

            // Handle URL instead of file
            if ($request->filled('file_url')) {
                $fileUrl = $request->file_url;

                $payload = [
                    'hosted'  => true,
                    'fileUrl' => $fileUrl,
                    'name'    => basename($fileUrl),
                ];

                $data['pdf_path'] = $fileUrl;
                $data['thumbnail_path'] = null;
            }

            $report = PhotoReport::create($data);
            try {
                $user = Auth::user();
                $agency = $user->agency;
                $crmService = new CrmService($agency);
                $locationId = CRM::getDefault('primary_location', '', $agency);
                $crmService->uploadFile($payload, $locationId);
            } catch (\Exception $e) {
                Log::error('Failed to upload document to CRM: ' . $e->getMessage());
            }


            return response()->json([
                'status'  => 'success',
                'message' => 'Report uploaded successfully.',
                'report'  => $report,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }






    public function destroy(PhotoReport $photoReport)
    {
        if ($photoReport->pdf_path && !filter_var($photoReport->pdf_path, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($photoReport->pdf_path);
        }

        if ($photoReport->thumbnail_path) {
            Storage::disk('public')->delete($photoReport->thumbnail_path);
        }

        $photoReport->delete();

        return back()->with('success', 'Report deleted');
    }


    public function download($photoReport)
    {
        $photoReport = PhotoReport::findorfail($photoReport);
        if (!$photoReport->pdf_path) {
            abort(404);
        }

        // If it is an external URL
        if (filter_var($photoReport->pdf_path, FILTER_VALIDATE_URL)) {
            return redirect($photoReport->pdf_path);
        }

        // Local file
        $fullPath = storage_path('app/public/' . $photoReport->pdf_path);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        return response()->download($fullPath);
    }
}
