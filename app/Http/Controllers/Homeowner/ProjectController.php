<?php


namespace App\Http\Controllers\Homeowner;


use App\Http\Controllers\Controller;
use App\Http\Requests\Homeowner\StoreClaimDocumentRequest;
use App\Models\ClaimDocument;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class ProjectController extends Controller
{


    public function index(Request $request)
    {
        $homeId = $request->query('home_id') ?? optional($request->user()->homes()->first())->id;
        $projects = Project::when($homeId, fn($q) => $q->where('home_id', $homeId))->latest()->paginate(12);
        return view('homeowner.projects.index', compact('projects'));
    }


    public function store(StoreClaimDocumentRequest $request)
    {
        $data = $request->validated();
        $data['uploaded_by'] = $request->user()->id;
        $doc = ClaimDocument::create($data);
        return response()->json(['status' => 'created', 'document' => $doc], 201);
    }

    public function show(Project $project)
    {
        return view('homeowner.projects.show', compact('project'));
    }
    public function download(Project $project)
    {
        if (! $project->file_path) abort(404);
        return redirect(Storage::disk('s3')->temporaryUrl($project->file_path, now()->addMinutes(15)));
    }
}
