<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
// We will create this Mailable later
// use App\Mail\InspectionReadyForReview;
// use Illuminate\Support\Facades\Mail;

class CompanyCamWebhookController extends Controller
{
    /**
     * Handle incoming webhooks from CompanyCam.
     */
    public function handle(Request $request)
    {
        // TODO: In production, verify the webhook signature to ensure it's from CompanyCam.
        // $signature = $request->header('X-CompanyCam-Signature');
        // if (!$this->isValidSignature($signature, $request->getContent())) {
        //     abort(403, 'Invalid signature.');
        // }

        $payload = $request->all();
        $eventType = $payload['event']['type'] ?? 'unknown';

        // Log every incoming event for debugging
        $log = WebhookLog::create([
            'source' => 'companycam',
            'event_type' => $eventType,
            'payload' => json_encode($payload),
        ]);

        // Process only the event we care about: 'report.completed'
        if ($eventType === 'report.completed') {
            try {
                $this->handleReportCompleted($payload);
                WebhookLog::where('id', $log->id)->update(['status' => 'processed']);
            } catch (\Exception $e) {
                WebhookLog::where('id', $log->id)->update(['status' => 'failed', 'notes' => $e->getMessage()]);
                return response()->json(['error' => 'Webhook processing failed.'], 500);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Process the 'report.completed' event.
     *
     * @param array $payload The full webhook payload.
     */
    protected function handleReportCompleted(array $payload)
    {
        $projectId = $payload['event']['resource']['project_id'] ?? null;

        if (!$projectId) {
            throw new \Exception('Project ID not found in webhook payload.');
        }

        // Find the inspection in our system linked to this CompanyCam project
        $inspection = Inspection::where('companycam_project_id', $projectId)->first();

        if (!$inspection) {
            // This might happen if it's a project not created by our app. It's safe to ignore.
            return;
        }

        // Update the inspection status to 'ready_for_review'
        $inspection->update(['status' => 'ready_for_review']);

        // TODO: Notify the agency admin that the inspection is ready for review.
        // $admin = ... find admin user ...
        // Mail::to($admin->email)->send(new InspectionReadyForReview($inspection));
    }
}
