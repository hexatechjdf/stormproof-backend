<?php

namespace App\Http\Controllers\Homeowner;

use App\Helper\CRM;
use App\Http\Controllers\Controller;
use App\Mail\HomeownerSupportMail;
use App\Models\Inspection;
use App\Services\CrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        return view('homeowner.contact_support.index');
    }
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:5',
        ]);

        Mail::to('farhanjdfunnel@gmail.com')->send(
            new HomeownerSupportMail(
                $request->user(),
                $request->subject,
                $request->message
            )
        );

        return back()->with('success', 'Your message has been sent to Customer Success!');
    }
}
