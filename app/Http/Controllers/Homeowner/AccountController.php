<?php

namespace App\Http\Controllers\Homeowner;

use App\Helper\CRM;
use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Services\CrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
  public function index(Request $request)
  {
    return view('homeowner.account.index');
  }
  public function update(Request $request)
  {
    $user = auth()->user();

    $validated = $request->validate([
        'name'  => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
    ]);

    $user->name  = $validated['name'];
    $user->email = $validated['email'];
    $user->save();

    return redirect()
      ->back()
      ->with('success', 'Your account information has been updated successfully.');
  }

}
