<?php

namespace App\Http\Controllers\Homeowner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Homeowner\StorePhotoReportRequest;
use App\Models\PhotoReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class PhotoReportController extends Controller
{

    public function index(Request $request)
    {
        $reports = PhotoReport::latest()->paginate(12);
        return view('homeowner.photo_reports.index', compact('reports'));
    }


    public function show(PhotoReport $photoReport)
    {
        return view('homeowner.photo_reports.show', compact('photoReport'));
    }


    public function store(StorePhotoReportRequest $request)
    {
        $data = $request->validated();
        $data['uploaded_by'] = $request->user()->id;
        $report = PhotoReport::create($data);
        return response()->json(['status' => 'created', 'report' => $report], 201);
    }


    public function destroy(PhotoReport $photoReport)
    {
        if ($photoReport->pdf_path) Storage::disk('s3')->delete($photoReport->pdf_path);
        if ($photoReport->thumbnail_path) Storage::disk('s3')->delete($photoReport->thumbnail_path);
        $photoReport->delete();
        return back()->with('success', 'Report deleted');
    }


    public function download($photoReport)
    {
        $photoReport = PhotoReport::findorfail($photoReport);
        // Check if pdf_path exists
        if (! $photoReport->pdf_path) {
            abort(404, 'File not found.');
        }

        // If pdf_path is a URL, redirect to it
        if (filter_var($photoReport->pdf_path, FILTER_VALIDATE_URL)) {
            return redirect($photoReport->pdf_path);
        }

        // Otherwise, serve the local file from storage/app/public
        $filePath = storage_path('app/public/' . $photoReport->pdf_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found on server.');
        }

        return response()->download($filePath);
    }
}
