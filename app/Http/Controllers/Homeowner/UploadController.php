<?php


namespace App\Http\Controllers\Homeowner;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class UploadController extends Controller
{


    public function presign(Request $request)
    {
        $data = $request->validate([
            'filename' => 'required|string',
            'content_type' => 'required|string',
            'home_id' => 'required|exists:homes,id',
            'folder' => 'nullable|string',
        ]);


        $key = trim(($data['folder'] ?? 'uploads') . '/' . $data['home_id'] . '/' . uniqid() . '-' . basename($data['filename']), '/');


        $s3Client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();


        $command = $s3Client->getCommand('PutObject', [
            'Bucket' => env('AWS_BUCKET'),
            'Key' => $key,
            'ContentType' => $data['content_type'],
            'ACL' => 'private',
        ]);


        $request = $s3Client->createPresignedRequest($command, '+15 minutes');
        $presignedUrl = (string) $request->getUri();


        return response()->json(['url' => $presignedUrl, 'key' => $key]);
    }
}
