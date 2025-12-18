<?php


namespace App\Http\Controllers\Homeowner;


use App\Http\Controllers\Controller;
use App\Http\Requests\Homeowner\UpdateQuestionnaireRequest;
use App\Models\Home;
use App\Models\HomeQuestionnaire;
use App\Models\Setting;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;


class HomeQuestionnaireController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();
        $settings = Setting::where('agency_id', $user->agency_id)->pluck('value', 'key');
        $openMode     = $settings['questionnaire_open_mode'] ?? 'redirect';
        $baseUrl     = $settings['questionnaire_url'] ?? '';

        $queryParams = $request->query();
        $fullUrl = $baseUrl . '?' . http_build_query($queryParams);
        switch ($openMode) {
            case 'new_tab':
                return view('homeowner.open-new-tab', [
                    'url' => $fullUrl
                ]);

            case 'iframe':
                return view('homeowner.iframe-view', [
                    'url' => $fullUrl
                ]);

            case 'redirect':
            default:
                return redirect()->away($fullUrl);
        }
    }



    public function getStormSeasonKit(Request $request)
    {
        $user = $request->user();
        $settings = Setting::where('agency_id', $user->agency_id)->pluck('value', 'key');
        $openMode     = $settings['storm_season_open_mode'] ?? 'redirect';
        $baseUrl     = $settings['storm_season_url'] ?? '';

        $queryParams = $request->query();
        $fullUrl = $baseUrl . '?' . http_build_query($queryParams);

        switch ($openMode) {
            case 'new_tab':
                return view('homeowner.open-new-tab', [
                    'url' => $fullUrl
                ]);

            case 'iframe':
                return view('homeowner.iframe-view', [
                    'url' => $fullUrl
                ]);

            case 'redirect':
            default:
                return redirect()->away($fullUrl);
        }
    }
}
