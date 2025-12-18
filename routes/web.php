<?php

use App\Http\Controllers\Admin\ClaimDocumentController as AdminClaimDocumentController;
use App\Http\Controllers\Admin\CompanyCamMappingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InspectionController;
use App\Http\Controllers\Admin\PartnerJobController;
use App\Http\Controllers\Admin\PhotoReportController as AdminPhotoReportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SuperAdmin\AgencyController;
use App\Http\Controllers\SuperAdmin\AgencySettingsController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Advisor\DashboardController as AdvisorDashboardController;
use App\Http\Controllers\Homeowner\DashboardController as HomeownerDashboardController;
use App\Http\Controllers\Homeowner\InspectionScheduleController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\Partner\DashboardController as PartnerDashboardController;
use App\Http\Controllers\Partner\JobController;
use App\Http\Controllers\SuperAdmin\SystemSettingsController;
use App\Http\Controllers\Admin\SystemSettingsController as AdminSystemSettingsController;
use App\Http\Controllers\Advisor\InspectionController as AdvisorInspectionController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\Homeowner\AccountController;
use App\Http\Controllers\Homeowner\ClaimDocumentController;
use App\Http\Controllers\Homeowner\ContactController;
use App\Http\Controllers\Homeowner\HomeQuestionnaireController;
use App\Http\Controllers\Homeowner\PhotoReportController;
use App\Http\Controllers\Homeowner\ProjectController;
use App\Http\Controllers\Homeowner\StormKitController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});
Auth::routes();
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

// --- PROTECTED ROUTE GROUPS ---

// Group for Super Admin
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('agencies', AgencyController::class);
    Route::get('agencies/{agency}/settings', [AgencySettingsController::class, 'index'])->name('agencies.settings.index');
    Route::put('agencies/{agency}/settings', [AgencySettingsController::class, 'update'])->name('agencies.settings.update');
});

// Group for Agency Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('inspections', [InspectionController::class, 'index'])->name('inspections.index');
    Route::get('inspections/{inspection}', [InspectionController::class, 'show'])->name('inspections.show');
    Route::post('inspections/{inspection}/broadcast', [InspectionController::class, 'broadcast'])->name('inspections.broadcast');
    Route::resource('users', UserController::class);
    Route::get('mappings/companycam', [CompanyCamMappingController::class, 'index'])->name('mappings.companycam.index');
    Route::post('mappings/companycam', [CompanyCamMappingController::class, 'update'])->name('mappings.companycam.update');
    Route::get('inspections/{inspection}/review', [InspectionController::class, 'review'])->name('inspections.review');
    Route::post('inspections/{inspection}/finalize', [InspectionController::class, 'finalize'])->name('inspections.finalize');
    Route::get('inspections/{inspection}/partner-jobs/create', [PartnerJobController::class, 'create'])->name('partner_jobs.create');
    Route::post('inspections/{inspection}/partner-jobs', [PartnerJobController::class, 'store'])->name('partner_jobs.store');



    Route::get('settings/{agency}', [AdminSystemSettingsController::class, 'index'])->name('settings.index');
    Route::get('setting-user-mapping/{agency}', [AdminSystemSettingsController::class, 'userMapping'])->name('settings.user-mapping');
    Route::get('setting-homeowner-menu/{agency}', [AdminSystemSettingsController::class, 'homeOwnerMenu'])->name('settings.homeowner-menu');
    Route::post('setting-homeowner-menu/{agency}/update', [AdminSystemSettingsController::class, 'homeOwnerMenuUpdate'])->name('settings.homeowner-menu.update');
    Route::put('settings/{agency}', [AdminSystemSettingsController::class, 'update'])->name('settings.update');

    Route::get('/crm/users/{locationId}', [AdminSystemSettingsController::class, 'getUsersByLocation'])
        ->name('crm.users.byLocation');
    Route::get('/crm/calendars/{locationId}', [AdminSystemSettingsController::class, 'getCalendarByLocation'])
        ->name('crm.calendars.byLocation');


    Route::resource('photo-report', AdminPhotoReportController::class)->only(['index', 'show', 'store', 'destroy']);
    Route::post('photo-report/{report}/download', [AdminPhotoReportController::class, 'download'])->name('photo-report.download');
    
    
    Route::resource('claim-documents', AdminClaimDocumentController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::post('claim-documents/{claim-documents}/download', [AdminClaimDocumentController::class, 'download'])->name('claim-documents.download');

});

// Group for Homeowners
Route::middleware(['auth', 'role:homeowner'])->prefix('homeowner')->name('homeowner.')->group(function () {
    Route::get('/dashboard', [HomeownerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/inspections', [HomeownerDashboardController::class, 'inspections'])->name('inspections.index');
    Route::get('/inspections-data', [HomeownerDashboardController::class, 'inspectionData'])->name('inspections.data');
    Route::get('/inspections/{id?}/schedule', [InspectionScheduleController::class, 'create'])->name('inspections.schedule');
    Route::post('/inspections/{id?}/schedule', [InspectionScheduleController::class, 'store'])->name('inspections.schedule.store');
    // API Routes for Calendar
    Route::prefix('api')->group(function () {
        // Get appointments for calendar
        Route::get('/inspections/{inspection}/appointments', [InspectionScheduleController::class, 'getAppointments'])
            ->name('api.inspections.appointments');

        // Get available time slots
        Route::get('/inspections/{inspection}/available-slots', [InspectionScheduleController::class, 'getAvailableSlots'])
            ->name('api.inspections.available-slots');

        // Update appointment status (for inspector)
        Route::patch('/appointments/{appointment}/status', [InspectionScheduleController::class, 'updateStatus'])
            ->name('api.appointments.update-status');

        // Cancel appointment
        Route::delete('/appointments/{appointment}', [InspectionScheduleController::class, 'cancel'])
            ->name('api.appointments.cancel');

        Route::resource('photo-reports', PhotoReportController::class)->only(['index', 'show', 'store', 'destroy']);
        Route::post('photo-reports/{report}/download', [PhotoReportController::class, 'download'])->name('photo-reports.download');

        Route::resource('claim-documents', ClaimDocumentController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::post('claim-documents/{claim-documents}/download', [ClaimDocumentController::class, 'download'])->name('claim-documents.download');

        Route::resource('projects', ProjectController::class)->only(['index', 'show']);

        Route::resource('storm-kit', StormKitController::class)->names('storm-kit');

        Route::resource('questionnaire', HomeQuestionnaireController::class)->names('questionnaire');
        Route::get('storm-session-kit', [HomeQuestionnaireController::class,'getStormSeasonKit'])->name('storm.season.index');

        Route::resource('contact-support', ContactController::class);

        Route::resource('account', AccountController::class)->names('account');
        Route::resource('interior-photo-upload', AccountController::class)->names('interior-photo-upload');
        Route::resource('referral', AccountController::class)->names('referral');
    });
});

// Group for Advisors
Route::middleware(['auth', 'role:advisor'])->prefix('advisor')->name('advisor.')->group(function () {
    Route::get('/dashboard', [AdvisorDashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/opportunities-data', [AdvisorDashboardController::class, 'opportunitiesData'])->name('dashboard.opportunities-data');
    Route::get('dashboard/assigned-data', [AdvisorDashboardController::class, 'assignedData'])->name('dashboard.assigned-data');
    Route::post('/inspections/{inspection}/claim', [AdvisorDashboardController::class, 'claim'])->name('inspections.claim');
    Route::get('/inspections/{inspection}', [AdvisorInspectionController::class, 'show'])->name('inspections.show');
    Route::put('/inspections/{inspection}', [AdvisorInspectionController::class, 'update'])->name('inspections.update');
});
Route::middleware(['auth', 'role:partner'])->prefix('partner')->name('partner.')->group(function () {
    Route::get('/dashboard', [PartnerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
    Route::put('/jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
});
Route::prefix('authorization')->name('crm.')->group(function () {
    Route::get('/{provider}/oauth/callback', [OAuthController::class, 'callback'])->name('oauth_callback');
});
Route::prefix('webhook')->name('webhook.')->group(function () {
    Route::get('/{provider}/data', [OAuthController::class, 'callback'])->name('data');
});
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// 6908a51e8211040f4c681df8-mhj5c3lp
// 9f7ee70c-bd48-43a3-8ac5-8b3d9895219f
// 96d0d0f1-b4e7-4dfb-8ff5-77434a8070de





// https://app.companycam.com/signup?affiliate=developer
// StormProof
// farhan@jdfunnel.com
// z7Gxf89WYe9#Aqj


Route::get('/sso-login', function () {
    return view('auth.auto_login');
})->name('auto_login');
Route::get('custom-logout', function () {
    if (Auth::check()) {
        Auth::logout();
    }
    return redirect('/home'); 
});

Route::get('/refresh-crm-tokens', [CrmController::class, 'refreshCrmTokens']);