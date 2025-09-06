<?php

use App\Http\Controllers\GraduateGenerationController;
use App\Http\Controllers\ManageGraduatesController;
use App\Http\Controllers\PesoJobsController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\ManageUsersController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PesoCareerGuidanceController;
use App\Http\Controllers\InstitutionCareerGuidanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManageJobReferralsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminRegisterController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\GraduateController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\CareerOpportunityController;
use App\Http\Controllers\ManageGraduatesApprovalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DegreeController;
use App\Http\Controllers\CustomRegisteredUserController;
use App\Http\Controllers\GraduateProfileController;
use App\Http\Controllers\GraduateJobsController;
// Company 
use App\Http\Controllers\CompanyJobApplicantController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\CompanyHRRegisterController;
use App\Http\Controllers\CompanyJobsController;
use App\Http\Controllers\CompanyApplicationController;
use App\Http\Controllers\CompanyManageHRController;
use App\Http\Controllers\CompanyDepartmentController;
use App\Http\Controllers\CompanyJobBatchUploadController;
use App\Http\Controllers\Company\CompanyReportsController;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;

use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;
use Laravel\Fortify\Http\Controllers\VerifyEmailAddressController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\JobInboxController;
use App\Http\Controllers\InternshipProgramController;
use Laravel\Fortify\RoutePath;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\EmploymentPreferencesController;
use App\Http\Controllers\CareerGoalsController;
use App\Http\Controllers\JobsListController;
use App\Http\Controllers\PesoProfileController;
use App\Http\Controllers\InstitutionProfileController;
use App\Http\Controllers\Institution\InstitutionReportsController;
use App\Http\Controllers\ResumeController;

use App\Notifications\VerifyEmailWithCode;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\CareerOfficerRegisterController;


Route::get('/', function () {
    return Inertia::render('Auth/LandingPage');
})->name('landing-page.index');

// Route::get('/', function () {
//     return Inertia::render('Auth/Login');
// });


Route::get('/register', function () {
    return Inertia::render('Auth/Register');
})->name('pathway.register');


Route::middleware(['auth'])->group(function () {
    Route::get('/peso/register', [AdminRegisterController::class, 'showRegistrationForm'])->name('admin.register');
    Route::post('/peso/register', [AdminRegisterController::class, 'register'])->name('admin.register.submit');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/careerofficer/register', [CareerOfficerRegisterController::class, 'showRegistrationForm'])->name('careerofficer.register');
    Route::post('/careerofficer/register', [CareerOfficerRegisterController::class, 'register'])->name('careerofficer.submit');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->post('/notifications/mark-as-read', function () {
    $user = Auth::user();
    if ($user) {
        $user->unreadNotifications->markAsRead();
    }
    return response()->json(['success' => true]);
})->name('notifications.markAsRead');

// PESO Jobs
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('peso/jobs/{user}', [PesoJobsController::class, 'index'])
    ->name('peso.jobs');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('peso/jobs-peso/{user}', [PesoJobsController::class, 'peso'])
    ->name('peso.pesojobs');


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('peso/jobs/{user}/archivedlist', [PesoJobsController::class, 'archivedlist'])
    ->name('peso.jobs.archivedlist');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('peso/jobs/{user}/create', [PesoJobsController::class, 'create'])
    ->name('peso.jobs.create');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->post('peso/jobs/{user}', [PesoJobsController::class, 'store'])
    ->name('peso.jobs.store');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->get('peso/jobs/manage/{user}', [PesoJobsController::class, 'manage'])
    ->name('peso.jobs.manage');


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get(
    'peso/jobs/view/{job}',
    [PesoJobsController::class, 'view']
)
    ->name('peso.jobs.view');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('peso/jobs/edit/{job}', [PesoJobsController::class, 'edit'])
    ->name('peso.jobs.edit');


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->put('peso/jobs/edit/{job}', [PesoJobsController::class, 'update'])
    ->name('peso.jobs.update');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->delete('peso/jobs/edit/{job}', [PesoJobsController::class, 'delete'])
    ->name('peso.jobs.delete');

Route::post('peso/jobs/edit/{job}', [PesoJobsController::class, 'restore'])->name('peso.jobs.restore');


Route::post('peso/jobs/{job}/approve', [PesoJobsController::class, 'approve'])->name('peso.jobs.approve');
Route::post('peso/jobs/{job}/disapprove', [PesoJobsController::class, 'disapprove'])->name('peso.jobs.disapprove');





Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});




// Admin Routes
// Route::prefix('admin')->group(function () {
//     Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
//     Route::post('/login', [AdminController::class, 'login'])->name('admin.login.submit');
//     Route::get('/register', [AdminController::class, 'showRegistrationForm'])->name('admin.register');
//     Route::post('/register', [AdminController::class, 'register'])->name('admin.register.submit');
// });

// // User Routes
//     Route::get('/login', [CustomRegisteredUserController::class, 'showLoginForm'])->name('user.login');
//     Route::post('/login', [CustomRegisteredUserController::class, 'login'])->name('user.login.submit');
//     Route::get('/register', [CustomRegisteredUserController::class, 'showRegistrationForm'])->name('user.register');
//     Route::post('/register', [CustomRegisteredUserController::class, 'register'])->name('user.register.submit');
// });

// Route::middleware('admin')->group(function () {
//     Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
// });


// Route::get('/home', function () {
//     return redirect()->route('dashboard');
// })->middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified',
// ]);

// Companies Routes

//Company Register Routes
Route::middleware(['auth'])->group(function () {
    Route::get('company/hr/register', [CompanyHRRegisterController::class, 'showRegistrationForm'])->name('hr.register');
    Route::post('company/hr/register', [CompanyHRRegisterController::class, 'register'])->name('hr.register.submit');
});

// CompanyDashboard Contents 
// Route::middleware(['auth' , config('jetstream.auth_session'), 'verified',])->group(function () {
//     Route::get('/dashboard', [ApplicationController::class, 'summary'])->name('dashboard');
// });


// Company Jobs Routes
// Route::prefix('user')->group(function () {
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('company/jobs/{user}', [CompanyJobsController::class, 'index'])->name('company.jobs');
    Route::get('company/jobs/{user}/archivedlist', [CompanyJobsController::class, 'archivedlist'])->name('company.jobs.archivedlist');
    Route::get('company/jobs/{user}/create', [CompanyJobsController::class, 'create'])->name('company.jobs.create');
    Route::post('company/jobs/{user}', [CompanyJobsController::class, 'store'])->name('company.jobs.store');
    Route::get('company/jobs/manage/{user}', [CompanyJobsController::class, 'manage'])->name('company.jobs.manage');
    Route::get('company/jobs/view/{job}', [CompanyJobsController::class, 'view'])->name('company.jobs.view');
    Route::get('company/jobs/edit/{job}', [CompanyJobsController::class, 'edit'])->name('company.jobs.edit');
    Route::put('company/jobs/edit/{job}', [CompanyJobsController::class, 'update'])->name('company.jobs.update');
    Route::delete('company/jobs/edit/{job}', [CompanyJobsController::class, 'delete'])->name('company.jobs.delete');
    Route::post('company/jobs/{job}/auto-invite', [CompanyJobsController::class, 'autoInvite'])->name('company.jobs.auto-invite');
    Route::post('company/jobs/edit/{job}', [CompanyJobsController::class, 'restore'])->name('company.jobs.restore');
    Route::post('company/jobs/{job}/approve', [CompanyJobsController::class, 'approve'])->name('company.jobs.approve');
    Route::post('company/jobs/{job}/disapprove', action: [CompanyJobsController::class, 'disapprove'])->name('company.jobs.disapprove');

    Route::get('/company/post-jobs/batch-upload', [CompanyJobsController::class, 'batchPage'])->name('company.jobs.batch.page');
    Route::post('/company/post-jobs/batch-upload', [CompanyJobsController::class, 'batchUpload'])->name('company.jobs.batch.upload');
    Route::get('/company/post-jobs/download', [CompanyJobsController::class, 'downloadTemplate'])->name('company.jobs.template.download');
});

//Manage Applicants Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    // Job-level applicant management (JobApplicantController)
    Route::prefix('/company')->group(function () {
        //View all jobs
        Route::get('jobs', [CompanyJobApplicantController::class, 'index'])->name('company.job.applicants.index');
        // View all applicants for a specific job
        Route::get('{job}/applicants', [CompanyJobApplicantController::class, 'show'])->name('company.job.applicants.show');
    });

    // Individual application management (ApplicationController)
    Route::prefix('/applicants')->group(function () {
        // View details of a specific applicant/application
        Route::get('{application}', [CompanyApplicationController::class, 'show'])->name('applicants.show');
        Route::put('{application}', [CompanyApplicationController::class, 'update'])->name('applicants.update');

        Route::post('/company/applications/{application}/offer', [CompanyApplicationController::class, 'storeOffer'])->name('company.applications.offer');
        Route::put('{application}/reject', [CompanyApplicationController::class, 'reject'])->name('applicants.reject');

        // Schedule interview
        Route::post('{application}/schedule-interview', [CompanyApplicationController::class, 'scheduleInterview'])->name('applicants.scheduleInterview');
        Route::put('/company/applications/{application}/stage', [CompanyApplicationController::class, 'updateStage'])->name('company.applications.updateStage');
        Route::put('/company/applications/{application}/status', [CompanyApplicationController::class, 'updateStatus'])->name('company.applications.updateStatus');
        // View graduate portfolio
        Route::get('portfolio/{user}', [CompanyApplicationController::class, 'viewPortfolio'])->name('applicants.portfolio');
        Route::put('/applicants/{application}/note', [CompanyApplicationController::class, 'updateNote'])->name('applicants.note.update');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/applications/{application}/actions', [\App\Http\Controllers\ApplicationActionController::class, 'index'])
        ->name('applications.actions.index');
    Route::post('/applications/{application}/actions', [\App\Http\Controllers\ApplicationActionController::class, 'perform'])
        ->name('applications.actions.perform');

    Route::get('/applications/{application}/actions', [\App\Http\Controllers\ApplicationActionController::class, 'index'])->name('applications.actions.index');
    Route::post('/applications/{application}/actions', [\App\Http\Controllers\ApplicationActionController::class, 'perform'])->name('applications.actions.perform');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pipeline-stages', [\App\Http\Controllers\PipelineStageController::class, 'index'])->name('pipeline.stages.index');
    Route::put('/pipeline-stages/reorder', [\App\Http\Controllers\PipelineStageController::class, 'reorder'])->name('pipeline.stages.reorder');
});
Route::middleware(['auth'])->group(function () {
    Route::post('/request-info/send', [\App\Http\Controllers\RequestMoreInfoController::class,'send'])->name('requestInfo.send');
    Route::get('/request-info', [\App\Http\Controllers\RequestMoreInfoController::class,'index'])->name('requestInfo.index');
    Route::post('/request-info/{requestMoreInfo}/complete', [\App\Http\Controllers\RequestMoreInfoController::class,'complete'])->name('requestInfo.complete');
    Route::post('/request-info/{requestMoreInfo}/read', [\App\Http\Controllers\RequestMoreInfoController::class,'markRead'])->name('requestInfo.read');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/assessment/instructions', [\App\Http\Controllers\AssessmentController::class,'sendInstructions'])->name('assessment.instructions.send');
    Route::post('/assessment/reschedule',   [\App\Http\Controllers\AssessmentController::class,'reschedule'])->name('assessment.reschedule');
    Route::post('/assessment/result',       [\App\Http\Controllers\AssessmentController::class,'recordResult'])->name('assessment.result.record');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/interview/invite', [\App\Http\Controllers\InterviewController::class,'sendInvitation'])->name('interview.invite');
    Route::post('/interview/reschedule', [\App\Http\Controllers\InterviewController::class,'reschedule'])->name('interview.reschedule');
    Route::post('/interview/feedback', [\App\Http\Controllers\InterviewController::class,'recordFeedback'])->name('interview.feedback');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/offer/send', [\App\Http\Controllers\OfferController::class,'send'])->name('offer.send');
});

// Company Reports
Route::prefix('company')->middleware(['auth'])->group(function () {
    Route::get('/company-reports/academic-performance', [CompanyReportsController::class, 'academicPerformance'])->name('company.reports.academicPerformance');
    Route::get('/company-reports/applicant-status', [CompanyReportsController::class, 'applicantStatus'])->name('company.reports.applicantStatus');
    Route::get('/company-reports/application-analysis', [CompanyReportsController::class, 'applicationAnalysis'])->name('company.reports.applicationAnalysis');
    Route::get('/company-reports/cert-tracking', [CompanyReportsController::class, 'certificationTracking'])->name('company.reports.certificationTracking');
    Route::get('/company-reports/competency', [CompanyReportsController::class, 'skillsCompetency'])->name('company.reports.competency');
    Route::get('/company-reports/department', [CompanyReportsController::class, 'department'])->name('company.reports.department');
    Route::get('/company-reports/diversity', [CompanyReportsController::class, 'diversity'])->name('company.reports.diversity');
    Route::get('/company-reports/efficiency', [CompanyReportsController::class, 'efficiency'])->name('company.reports.efficiency');
    Route::get('/company-reports/employment-type', [CompanyReportsController::class, 'employmentType'])->name('company.reports.employmentType');
    Route::get('/company-reports/employer-feedback', [CompanyReportsController::class, 'employerFeedback'])->name('company.reports.employerFeedback');
    Route::get('/company-reports/feedback', [CompanyReportsController::class, 'feedback'])->name('company.reports.feedback');
    Route::get('/company-reports/future-potential', [CompanyReportsController::class, 'futurePotential'])->name('company.reports.futurePotential');
    Route::get('/company-reports/graduate-demographics', [CompanyReportsController::class, 'graduateDemographics'])->name('company.reports.graduateDemographics');
    Route::get('/company-reports/graduate-pool', [CompanyReportsController::class, 'graduatePool'])->name('company.reports.graduatePool');
    Route::get('/company-reports/hiring-funnel', [CompanyReportsController::class, 'hiringFunnel'])->name('company.reports.hiringFunnel');
    Route::get('/company-reports/interview-progress', [CompanyReportsController::class, 'interviewProgress'])->name('company.reports.interviewProgress');
    Route::get('/company-reports/internship', [CompanyReportsController::class, 'internship'])->name('company.reports.internship');
    Route::get('/company-reports-jobOverview', [CompanyReportsController::class, 'overview'])->name('company.reports.overview');
    Route::get('/company-reports/matching-success', [CompanyReportsController::class, 'matchingSuccess'])->name('company.reports.matchingSuccess');
    Route::get('/company-reports/performance', [CompanyReportsController::class, 'performance'])->name('company.reports.performance');
    Route::get('/company-reports/preferences', [CompanyReportsController::class, 'preferences'])->name('company.reports.preferences');
    Route::get('/company-reports/salary', [CompanyReportsController::class, 'salaryInsights'])->name('company.reports.salary');
    Route::get('/company-reports/school-wise', [CompanyReportsController::class, 'schoolEmployability'])->name('company.reports.schoolWise');
    Route::get('/company-reports/screening', [CompanyReportsController::class, 'screening'])->name('company.reports.screening');
    Route::get('/company-reports/skills', [CompanyReportsController::class, 'skills'])->name('company.reports.skills');
    Route::get('/company-reports/trends', [CompanyReportsController::class, 'trends'])->name('company.reports.trends');

    Route::get('/company-reports/{user}/reports/list', [CompanyReportsController::class, 'list'])->name('company.reports.list');
});



// Manage HR Accounts 
Route::middleware(['auth'])->group(function () {
    Route::get('/company/manage-hrs', [CompanyManageHRController::class, 'index'])->name('company.manage-hrs');
    Route::get('/company/manage-hrs/{id}/edit', [CompanyManageHRController::class, 'edit'])->name('company.manage-hrs.edit');
    Route::put('/company/manage-hrs/{id}', [CompanyManageHRController::class, 'update'])->name('company.manage-hrs.update');
    Route::delete('/company/manage-hrs/{id}', [CompanyManageHRController::class, 'destroy'])->name('company.manage-hrs.destroy');
});

//Deparment Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage users'])->group(function () {
    Route::get('/company/departments', [CompanyDepartmentController::class, 'index'])->name('company.departments.index');
    Route::post('/company/departments', [CompanyDepartmentController::class, 'store'])->name('company.departments.store');
    Route::post('/company/departments/batch', [CompanyDepartmentController::class, 'batchStore'])->name('company.departments.batch');
    Route::post('/company/departments/batch-upload', [CompanyDepartmentController::class, 'batchUpload'])->name('company.departments.batch.upload');
    Route::get('/company/departments/batch-template', [CompanyDepartmentController::class, 'downloadTemplate'])->name('company.departments.batch.template');
    Route::get('/company/departments/manage', [CompanyDepartmentController::class, 'manage'])->name('company.departments.manage');
    Route::put('/company/departments/{department}', [CompanyDepartmentController::class, 'update'])->name('company.departments.update');
    Route::delete('/company/departments/{department}', [CompanyDepartmentController::class, 'destroy'])->name('company.departments.destroy');
});
// Company Profile 
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // View Company Profile
    Route::get('/company/profile', [CompanyProfileController::class, 'profile'])->name('company.profile');
    Route::post('/company/profile', [CompanyProfileController::class, 'post'])->name('company-profile.post');
    Route::put('/company/profile', [CompanyProfileController::class, 'update'])->name('company-profile.update');
    Route::delete('/current-user-photo', [CompanyProfileController::class, 'destroyPhoto'])->name('current-user-photo.destroy');
    Route::delete('/current-user-cover-photo', [CompanyProfileController::class, 'destroyCoverPhoto'])->name('current-user-cover-photo.destroy');

    Route::get('/company/information', [CompanyProfileController::class, 'showInformationForm'])->name('company.information');
    Route::post('/company/information', [CompanyProfileController::class, 'saveInformation'])->name('company.information.save');
});




//End of Company Routes



Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // View Company Profile
    Route::get('/admin/profile', [PesoProfileController::class, 'profile'])->name('peso.profile');
    // PESO Profile Settings
    Route::get('/admin/profile/settings', [PesoProfileController::class, 'settings'])->name('peso.profile.settings');
    Route::put('/admin/profile/settings', [PesoProfileController::class, 'update'])->name('peso.profile.update');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // View Institution Profile
    Route::get('/institution/profile', [InstitutionProfileController::class, 'profile'])->name('institution.profile');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/institution/information', [InstitutionProfileController::class, 'showInformationForm'])->name('institution.information');
    Route::post('/institution/information', [InstitutionProfileController::class, 'saveInformation'])->name('institution.information.save');
    Route::post('/institution/profile/description', [InstitutionProfileController::class, 'updateDescription'])->name('institution.profile.description.update');
});



// Jobs Routes
// Route::prefix('user')->group(function () {
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('/jobs/{user}', [JobsController::class, 'index'])
    ->name('jobs');


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('/jobs/{user}/archivedlist', [JobsController::class, 'archivedlist'])
    ->name('jobs.archivedlist');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('/jobs/{user}/create', [JobsController::class, 'create'])
    ->name('jobs.create');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->post('/jobs/{user}', [JobsController::class, 'store'])
    ->name('jobs.store');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->get('/jobs/manage/{user}', [JobsController::class, 'manage'])
    ->name('jobs.manage');


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('/jobs/view/{job}', [JobsController::class, 'view'])
    ->name('jobs.view');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('/jobs/edit/{job}', [JobsController::class, 'edit'])
    ->name('jobs.edit');


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->put('/jobs/edit/{job}', [JobsController::class, 'update'])
    ->name('jobs.update');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->delete('/jobs/edit/{job}', [JobsController::class, 'delete'])
    ->name('jobs.delete');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->post('/jobs/{job}/auto-invite', [JobsController::class, 'autoInvite'])
    ->name('jobs.auto-invite');

Route::post('/jobs/edit/{job}', [JobsController::class, 'restore'])->name('jobs.restore');


Route::post('/jobs/{job}/approve', [JobsController::class, 'approve'])->name('jobs.approve');
Route::post('/jobs/{job}/disapprove', [JobsController::class, 'disapprove'])->name('jobs.disapprove');







// Company Profile 
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // View Company Profile
    Route::get('/company/profile', [CompanyProfileController::class, 'profile'])->name('company.profile');
    Route::post('/company/profile', [CompanyProfileController::class, 'post'])->name('company-profile.post');
    Route::put('/company/profile', [CompanyProfileController::class, 'update'])->name('company-profile.update');
    Route::delete('/current-user-photo', [CompanyProfileController::class, 'destroyPhoto'])->name('current-user-photo.destroy');
    Route::delete('/current-user-cover-photo', [CompanyProfileController::class, 'destroyCoverPhoto'])->name('current-user-cover-photo.destroy');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // View Company Profile
    Route::get('/admin/profile', [PesoProfileController::class, 'profile'])->name('peso.profile');
});





// Manage Users (PESO)
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage users'])->group(function () {
    Route::get('/admin/manage-users', [ManageUsersController::class, 'index'])->name('admin.manage_users');
    Route::get('/admin/manage-users/list', [ManageUsersController::class, 'list'])->name('admin.manage_users.list');
    Route::get('/admin/manage-users/archivedlist', [ManageUsersController::class, 'archivedlist'])->name('admin.manage_users.archivedlist');
    Route::get('/admin/manage-users/edit/{user}', [ManageUsersController::class, 'edit'])->name('admin.manage_users.edit');
    Route::delete('/admin/manage-users/{user}', [ManageUsersController::class, 'delete'])->name('admin.manage_users.delete');
    Route::post('/admin/manage-users/{user}/approve', [ManageUsersController::class, 'approve'])->name('admin.manage_users.approve');
    Route::post('/admin/manage-users/{user}/disapprove', [ManageUsersController::class, 'disapprove'])->name('admin.manage_users.disapprove');
    Route::post('/admin/manage-users/{user}/restore', [ManageUsersController::class, 'restore'])->name('admin.manage_users.restore');
    Route::get('/admin/manage-users/upload', [ManageUsersController::class, 'batchPage'])->name('companies.batch.page');
    Route::get('/admin/manage-users/download', [ManageUsersController::class, 'downloadTemplate'])->name('companies.template.download');
    Route::post('/admin/manage-users/batch-upload', [ManageUsersController::class, 'batchUpload'])->name('companies.batch.upload');
    Route::get('/institutions/{institution}/download-verification', [ManageUsersController::class, 'downloadVerification'])
    ->name('institutions.downloadVerification');
    Route::get('/companies/{company}/download-verification', [ManageUsersController::class, 'downloadCompanyVerification'])
    ->name('companies.downloadVerification');
});

// Sectors
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage users'])->group(function () {
    Route::get('/sectors/{user}', [SectorController::class, 'index'])->name('sectors');
    Route::get('/sectors/{user}/list', [SectorController::class, 'list'])->name('sectors.list');
    Route::get('/sectors/{user}/create', [SectorController::class, 'create'])->name('sectors.create');
    Route::post('/sectors/{user}', [SectorController::class, 'store'])->name('sectors.store');
    Route::get('/sectors/edit/{sector}', [SectorController::class, 'edit'])->name('sectors.edit');
    Route::put('/sectors/edit/{sector}', [SectorController::class, 'update'])->name('sectors.update');
    Route::delete('/sectors/edit/{sector}', [SectorController::class, 'delete'])->name('sectors.delete');
    Route::get('/sectors/{user}/archivedlist', [SectorController::class, 'archivedlist'])->name('sectors.archivedlist');
    Route::post('/sectors/edit/{sector}', [SectorController::class, 'restore'])->name('sectors.restore');
});

// Categories


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage users'])->group(function () {

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/list', [CategoryController::class, 'list'])->name('categories.list');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories/{sector}', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/edit/{category}', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/edit/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/edit/{category}', [CategoryController::class, 'delete'])->name('categories.delete');
    Route::get('/sectors/{sector}/categories', [CategoryController::class, 'index'])->name('sectors.categories.index');
    Route::get('/categories/archivedlist', [CategoryController::class, 'archivedlist'])->name('categories.archivedlist');
    Route::post('/categories/edit/{category}', [CategoryController::class, 'restore'])->name('categories.restore');
});

//Institution Reports Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage institution'])->prefix('institutions/reports')->group(function () {
    Route::get('/', [InstitutionReportsController::class, 'index'])->name('institutions.reports.index');
    Route::get('/school-year', [InstitutionReportsController::class, 'schoolYear'])->name('institutions.reports.schoolYear');
    Route::get('/degree', [InstitutionReportsController::class, 'degree'])->name('institutions.reports.degree');
    Route::get('/programs', [InstitutionReportsController::class, 'programs'])->name('institutions.reports.programs');
    Route::get('/career', [InstitutionReportsController::class, 'career'])->name('institutions.reports.career');
    Route::get('/skill', [InstitutionReportsController::class, 'skill'])->name('institutions.reports.skill');
    Route::get('/graduate', [InstitutionReportsController::class, 'graduate'])->name('institutions.reports.graduate');
    Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage institution'])
        ->get('/institutions/reports/graduate/data', [InstitutionReportsController::class, 'graduateData'])
        ->name('institutions.reports.graduate.data');
});

//Internship Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage institution'])->group(function () {
    Route::get('/internship-programs', [InternshipProgramController::class, 'index'])->name('internship-programs.index');
    Route::get('/internship-programs/list', [InternshipProgramController::class, 'list'])->name('internship-programs.list');
    Route::get('/internship-programs/archivedlist', [InternshipProgramController::class, 'archivedList'])->name('internship-programs.archivedlist');
    Route::post('/internship-programs', [InternshipProgramController::class, 'store'])->name('internship-programs.store');
    Route::get('/internship-programs/create', [InternshipProgramController::class, 'create'])->name('internship-programs.create');
    Route::get('/internship-programs/edit/{id}', [InternshipProgramController::class, 'edit'])->name('internship-programs.edit');
    Route::put('/internship-programs/{id}', [InternshipProgramController::class, 'update'])->name('internship-programs.update');
    Route::delete('/internship-programs/{id}', [InternshipProgramController::class, 'archive'])->name('internship-programs.archive');
    Route::post('/internship-programs/{id}/restore', [InternshipProgramController::class, 'restore'])->name('internship-programs.restore');
    Route::post('/internship-programs/batch-upload', [InternshipProgramController::class, 'batchUpload'])->name('internship-programs.batch-upload');
    Route::post('/internship-programs/assign', [InternshipProgramController::class, 'assignToGraduate'])->name('internship-programs.assign');
    Route::get('/institutions/internship-programs/assign', [InternshipProgramController::class, 'assignPage'])->name('internship-programs.assign-page');
    Route::post('/institutions/internship-programs/remove-graduate', [InternshipProgramController::class, 'removeGraduate'])->name('internship-programs.remove-graduate');
});

//School Year Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage institution'])->group(function () {
    Route::get('/institutions/school-years/{user}', [SchoolYearController::class, 'index'])->name('school-years');
    Route::get('/institutions/school-years/{user}/list', [SchoolYearController::class, 'list'])->name('school-years.list');
    Route::get('/institutions/school-years/{user}/create', [SchoolYearController::class, 'create'])->name('school-years.create');
    Route::post('/institutions/school-years/{user}', [SchoolYearController::class, 'store'])->name('school-years.store');
    Route::get('/institutions/school-years/edit/{schoolYear}', [SchoolYearController::class, 'edit'])->name('school-years.edit');
    Route::put('/institutions/school-years/edit/{schoolYear}', [SchoolYearController::class, 'update'])->name('school-years.update');
    Route::delete('/institutions/school-years/edit/{schoolYear}', [SchoolYearController::class, 'delete'])->name('school-years.delete');
    Route::get('/institutions/school-years/{user}/archivedlist', [SchoolYearController::class, 'archivedlist'])->name('school-years.archivedlist');
    Route::post('/institutions/school-years/edit/{schoolYear}', [SchoolYearController::class, 'restore'])->name('school-years.restore');
});

//Degree Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage institution'])->group(function () {
    Route::get('/institutions/degrees/{user}', [DegreeController::class, 'index'])->name('degrees');
    Route::get('/institutions/degrees/{user}/list', [DegreeController::class, 'list'])->name('degrees.list');
    Route::get('/institutions/degrees/{user}/create', [DegreeController::class, 'create'])->name('degrees.create');
    Route::post('/institutions/degrees/{user}', [DegreeController::class, 'store'])->name('degrees.store');
    Route::get('/institutions/degrees/edit/{institutionDegree}', [DegreeController::class, 'edit'])->name('degrees.edit');
    Route::put('/institutions/degrees/edit/{institutionDegree}', [DegreeController::class, 'update'])->name('degrees.update');
    Route::delete('/institutions/degrees/edit/{institutionDegree}', [DegreeController::class, 'delete'])->name('degrees.delete');
    Route::get('/institutions/degrees/{user}/archivedlist', [DegreeController::class, 'archivedlist'])->name('degrees.archivedlist');
    Route::post('/institutions/degrees/edit/{degree}', [DegreeController::class, 'restore'])->name('degrees.restore');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/degrees', [DegreeController::class, 'index'])->name('degrees.index');
    Route::get('/degrees/create', [DegreeController::class, 'create'])->name('degrees.create');
    Route::post('/degrees', [DegreeController::class, 'store'])->name('degrees.store');
    Route::get('/degrees/{institutionDegree}/edit', [DegreeController::class, 'edit'])->name('degrees.edit');
    Route::put('/degrees/{institutionDegree}', [DegreeController::class, 'update'])->name('degrees.update');
    Route::delete('/degrees/{institutionDegree}', [DegreeController::class, 'delete'])->name('degrees.delete');
    Route::post('/degrees/{id}/restore', [DegreeController::class, 'restore'])->name('degrees.restore');
});

// PROGRAM ROUTES
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage institution'])->group(function () {
    Route::get('/programs/{user}', [ProgramController::class, 'index'])->name('programs');
    Route::get('/programs/{user}/list', [ProgramController::class, 'list'])->name('programs.list');
    Route::get('/programs/{user}/create', [ProgramController::class, 'create'])->name('programs.create');
    Route::post('/programs/{user}', [ProgramController::class, 'store'])->name('programs.store');
    Route::get('/programs/edit/{program}', [ProgramController::class, 'edit'])->name('programs.edit');
    Route::put('/programs/edit/{program}', [ProgramController::class, 'update'])->name('programs.update');
    Route::delete('/programs/edit/{program}', [ProgramController::class, 'delete'])->name('programs.delete');
    Route::get('/programs/{user}/archivedlist', [ProgramController::class, 'archivedlist'])->name('programs.archivedlist');
    Route::post('/programs/edit/{program}', [ProgramController::class, 'restore'])->name('programs.restore');
});

//MANAGE GRADUATES APPROVAL ROUTES

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage institution'])->group(function () {
    Route::get('/graduates/manage', [ManageGraduatesApprovalController::class, 'index'])->name('graduates.manage');
    Route::get('/graduates/list', [ManageGraduatesApprovalController::class, 'list'])->name('graduates.list');
    Route::post('/graduates/approve/{user}', [ManageGraduatesApprovalController::class, 'approve'])->name('graduates.approve');
    Route::post('/graduates/disapprove/{user}', [ManageGraduatesApprovalController::class, 'disapprove'])->name('graduates.disapprove');
});

// Career Opportunities Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage institution'])->group(function () {
    Route::get('/careeropportunities/{user}', [CareerOpportunityController::class, 'index'])->name('careeropportunities');
    Route::get('/careeropportunities/{user}/list', [CareerOpportunityController::class, 'list'])->name('careeropportunities.list');
    Route::get('/careeropportunities/{user}/create', [CareerOpportunityController::class, 'create'])->name('careeropportunities.create');
    Route::post('/careeropportunities/{user}', [CareerOpportunityController::class, 'store'])->name('careeropportunities.store');
    Route::get('/careeropportunities/edit/{careeropportunity}', [CareerOpportunityController::class, 'edit'])->name('careeropportunities.edit');
    Route::put('/careeropportunities/update/{id}', [CareerOpportunityController::class, 'update'])->name('careeropportunities.update');
    Route::delete('/careeropportunities/edit/{careeropportunity}', [CareerOpportunityController::class, 'delete'])->name('careeropportunities.delete');
    Route::get('/careeropportunities/{user}/archivedlist', [CareerOpportunityController::class, 'archivedlist'])->name('careeropportunities.archivedlist');
    Route::post('/careeropportunities/edit/{careeropportunity}', [CareerOpportunityController::class, 'restore'])->name('careeropportunities.restore');
});

//Manage Skills

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage institution'])->prefix('skills')->group(function () {
    Route::get('/{user}', [SkillController::class, 'index'])->name('instiskills');
    Route::get('/{user}/list', [SkillController::class, 'list'])->name('instiskills.list');
    Route::get('/{user}/create', [SkillController::class, 'create'])->name('instiskills.create');
    Route::post('/{user}', [SkillController::class, 'store'])->name('instiskills.store');
    Route::get('/edit/{id}', [SkillController::class, 'edit'])->name('instiskills.edit');
    Route::put('/update/{id}', [SkillController::class, 'update'])->name('instiskills.update');
    Route::delete('/archive/{id}', [SkillController::class, 'archive'])->name('instiskills.archive');
    Route::get('/{user}/archivedlist', [SkillController::class, 'archivedlist'])->name('instiskills.archivedlist');
    Route::post('/restore/{id}', [SkillController::class, 'restore'])->name('instiskills.restore');

});



//Career Guidance
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage institution'])
    ->prefix('skills')
    ->group(function () {
        Route::get('/institutions/career-guidance', [InstitutionCareerGuidanceController::class, 'index'])
            ->name('institutions.career-guidance');
        Route::get('/institutions/career-counseling', [InstitutionCareerGuidanceController::class, 'seminarRequests'])->name('institutions.career-counseling');
        Route::post('/institutions/seminar-requests', [InstitutionCareerGuidanceController::class, 'storeSeminarRequest'])->name('institutions.seminar-requests.store');
        Route::post('/institutions/seminar-requests/{id}/cancel', [InstitutionCareerGuidanceController::class, 'cancelSeminarRequest'])->name('institutions.seminar-requests.cancel');
        Route::get('/institutions/seminar-requests/{id}', [InstitutionCareerGuidanceController::class, 'showSeminarRequest'])->name('institutions.seminar-requests.show');
    });


// MAIN INSITUTION GRADUATE ROUTES
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage institution'])->group(function () {

    Route::get('/graduates', [GraduateController::class, 'index'])->name('graduates.index');
    Route::get('/graduates/create', [GraduateController::class, 'create'])->name('graduates.create');
    Route::post('/graduates', [GraduateController::class, 'store'])->name('institution.graduate.store');
    Route::get('/graduates/upload', [GraduateController::class, 'batchPage'])->name('graduates.batch.page');
    Route::put('/graduates/{graduate}', [GraduateController::class, 'update'])->name('graduates.update');
    Route::delete('/graduates/{graduate}', [GraduateController::class, 'destroy'])->name('graduates.destroy');
    Route::post('/graduates/batch-upload', [GraduateController::class, 'batchUpload'])->name('graduates.batch.upload');
    Route::get('/graduates/template/download', [GraduateController::class, 'downloadTemplate'])->name('graduates.template.download');
    Route::get('/graduates/archived', [GraduateController::class, 'archivedList'])->name('graduates.archived');
    Route::put('/graduates/restore/{graduate}', [GraduateController::class, 'restore'])->name('graduates.restore');
});


Route::group(['middleware' => config('fortify.middleware', ['web'])], function () {
    $enableViews = config('fortify.views', true);

    // Authentication...
    if ($enableViews) {
        Route::get(RoutePath::for('login', '/login'), [AuthenticatedSessionController::class, 'create'])
            ->middleware(['guest:' . config('fortify.guard')])
            ->name('login');
    }

    $limiter = config('fortify.limiters.login');
    $twoFactorLimiter = config('fortify.limiters.two-factor');
    $verificationLimiter = config('fortify.limiters.verification', '6,1');

    Route::post(RoutePath::for('login', '/login'), [AuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            'guest:' . config('fortify.guard'),
            $limiter ? 'throttle:' . $limiter : null,
        ]))->name('login.store');

    Route::post(RoutePath::for('logout', '/logout'), [AuthenticatedSessionController::class, 'destroy'])
        ->middleware([config('fortify.auth_middleware', 'auth') . ':' . config('fortify.guard')])
        ->name('logout');

    // Password Reset...
    if (Features::enabled(Features::registration())) {
        if ($enableViews) {
            // Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
            //     $request->fulfill();

            //     return redirect('/dashboard'); // Redirect to the dashboard or another page after verification
            // })->middleware(['auth', 'signed'])->name('verification.verify');
            // Default registration view
            Route::post('/verify-email', [CustomRegisteredUserController::class, 'verifyEmail'])->name('verify.email');
            Route::post('/email/verify-code', [CustomRegisteredUserController::class, 'verifyCode'])->name('verify.code');

            Route::get('/email/verify', function () {
                return Inertia::render('Auth/VerifyEmail', [
                    'auth' => [
                        'user' => Auth::user(), // Pass the authenticated user's data
                    ],
                ]);
            })->middleware('auth')->name('verification.notice');

            Route::get('/email/verify-code', function () {
                return Inertia::render('Auth/VerifyEmailCode', [
                    'auth' => [
                        'user' => Auth::user(), // Pass the authenticated user's data
                    ],
                ]);
            })->middleware('auth')->name('verification.code');

            Route::post('/email/verification-notification', function (Request $request) {
                $user = $request->user();

                // Generate a new verification code
                $verificationCode = rand(100000, 999999);
                $user->verification_code = $verificationCode;
                $user->save();

                // Send the verification code via email
                $user->notify(new VerifyEmailWithCode($verificationCode));

                return back()->with('message', 'Verification code sent!');
            })->middleware(['auth', 'throttle:6,1'])->name('verification.resend');



            Route::post('/register/graduate', [CustomRegisteredUserController::class, 'store'])
                ->middleware(['guest:' . config('fortify.guard')])
                ->name('register.graduate.store');

            Route::post('/register/email', [CustomRegisteredUserController::class, 'storeEmailOnly'])
                ->middleware(['guest:' . config('fortify.guard')])
                ->name('register.email.store');


            // AlmostDone page routes
            Route::get('/graduate/almostdone', function () {
                return Inertia::render('Auth/AlmostDone');
            })->middleware(['auth'])->name('graduate.almostdone');

            Route::post('/graduate/almostdone', [GraduateController::class, 'storeAlmostDone'])
                ->middleware(['auth'])
                ->name('graduate.almostdone.store');



            Route::get('/register/graduate', [CustomRegisteredUserController::class, 'showGraduateDetails'])
                ->name('register.showGraduateDetails');


            Route::get('/register/company', [CustomRegisteredUserController::class, 'showCompanyDetails'])
                ->middleware(['guest:' . config('fortify.guard')])
                ->name('register.company');

            Route::get('/register/institution', [CustomRegisteredUserController::class, 'create'])
                ->middleware(['guest:' . config('fortify.guard')])
                ->name('register.institution');

            Route::get('/register/institution', [CustomRegisteredUserController::class, 'showGraduateDetails'])
                ->name('register.showGraduateDetails');
        }

        // Default registration submission
        Route::post('/register', [CustomRegisteredUserController::class, 'store'])
            ->middleware(['guest:' . config('fortify.guard')])
            ->name('register.store');

        // Role-specific registration submissions
        Route::post('/register/company', [CustomRegisteredUserController::class, 'store'])
            ->middleware(['guest:' . config('fortify.guard')])
            ->name('register.company.store');

        Route::post('/register/institution', [CustomRegisteredUserController::class, 'store'])
            ->middleware(['guest:' . config('fortify.guard')])
            ->name('register.institution.store');

        Route::get('/register/users', [CustomRegisteredUserController::class, 'showUsers'])
            ->name('register.users');
    }
    // Email Verification...
    // if (Features::enabled(Features::emailVerification())) {
    //     if ($enableViews) {
    //         Route::get(RoutePath::for('verification.notice', '/email/verify'), [EmailVerificationPromptController::class, '__invoke'])
    //             ->middleware([config('fortify.auth_middleware', 'auth') . ':' . config('fortify.guard')])
    //             ->name('verification.notice');
    //     }

    // Route::get(RoutePath::for('verification.verify', '/email/verify/{id}/{hash}'), [VerifyEmailController::class, '__invoke'])
    //     ->middleware([config('fortify.auth_middleware', 'auth') . ':' . config('fortify.guard'), 'signed', 'throttle:' . $verificationLimiter])
    //     ->name('verification.verify');

    //     Route::post(RoutePath::for('verification.send', '/email/verification-notification'), [EmailVerificationNotificationController::class, 'store'])
    //         ->middleware([config('fortify.auth_middleware', 'auth') . ':' . config('fortify.guard'), 'throttle:' . $verificationLimiter])
    //         ->name('verification.send');
    // }

    // Profile Information...
    if (Features::enabled(Features::updateProfileInformation())) {
        Route::put(RoutePath::for('user-profile-information.update', '/user/profile-information'), [ProfileInformationController::class, 'update'])
            ->middleware([config('fortify.auth_middleware', 'auth') . ':' . config('fortify.guard')])
            ->name('user-profile-information.update');
    }

    // Passwords...
    if (Features::enabled(Features::updatePasswords())) {
        Route::put(RoutePath::for('user-password.update', '/user/password'), [PasswordController::class, 'update'])
            ->middleware([config('fortify.auth_middleware', 'auth') . ':' . config('fortify.guard')])
            ->name('user-password.update');
    }

    // Password Confirmation...
    if ($enableViews) {
        Route::get(RoutePath::for('password.confirm', '/user/confirm-password'), [ConfirmablePasswordController::class, 'show'])
            ->middleware([config('fortify.auth_middleware', 'auth') . ':' . config('fortify.guard')])
            ->name('password.confirm');
    }

    Route::get(RoutePath::for('password.confirmation', '/user/confirmed-password-status'), [ConfirmedPasswordStatusController::class, 'show'])
        ->middleware([config('fortify.auth_middleware', 'auth') . ':' . config('fortify.guard')])
        ->name('password.confirmation');

    Route::post(RoutePath::for('password.confirm', '/user/confirm-password'), [ConfirmablePasswordController::class, 'store'])
        ->middleware([config('fortify.auth_middleware', 'auth') . ':' . config('fortify.guard')])
        ->name('password.confirm.store');

    // Two Factor Authentication...
    if (Features::enabled(Features::twoFactorAuthentication())) {
        if ($enableViews) {
            Route::get(RoutePath::for('two-factor.login', '/two-factor-challenge'), [TwoFactorAuthenticatedSessionController::class, 'create'])
                ->middleware(['guest:' . config('fortify.guard')])
                ->name('two-factor.login');
        }

        Route::post(RoutePath::for('two-factor.login', '/two-factor-challenge'), [TwoFactorAuthenticatedSessionController::class, 'store'])
            ->middleware(array_filter([
                'guest:' . config('fortify.guard'),
                $twoFactorLimiter ? 'throttle:' . $twoFactorLimiter : null,
            ]))->name('two-factor.login.store');

        $twoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? [config('fortify.auth_middleware', 'auth') . ':' . config('fortify.guard'), 'password.confirm']
            : [config('fortify.auth_middleware', 'auth') . ':' . config('fortify.guard')];

        Route::post(RoutePath::for('two-factor.enable', '/user/two-factor-authentication'), [TwoFactorAuthenticationController::class, 'store'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.enable');

        Route::post(RoutePath::for('two-factor.confirm', '/user/confirmed-two-factor-authentication'), [ConfirmedTwoFactorAuthenticationController::class, 'store'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.confirm');

        Route::delete(RoutePath::for('two-factor.disable', '/user/two-factor-authentication'), [TwoFactorAuthenticationController::class, 'destroy'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.disable');

        Route::get(RoutePath::for('two-factor.qr-code', '/user/two-factor-qr-code'), [TwoFactorQrCodeController::class, 'show'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.qr-code');

        Route::get(RoutePath::for('two-factor.secret-key', '/user/two-factor-secret-key'), [TwoFactorSecretKeyController::class, 'show'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.secret-key');

        Route::get(RoutePath::for('two-factor.recovery-codes', '/user/two-factor-recovery-codes'), [RecoveryCodeController::class, 'index'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.recovery-codes');

        Route::post(RoutePath::for('two-factor.recovery-codes', '/user/two-factor-recovery-codes'), action: [RecoveryCodeController::class, 'store'])
            ->middleware($twoFactorMiddleware);
    }
});


Route::middleware(['auth', 'verified'])->group(function () {


    // JobInbox Routes - Updated to use JobInboxController
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/job-inbox', [JobInboxController::class, 'index'])
            ->name('job.inbox')
            ->middleware(['auth', 'verified']);
        Route::get('/job-opportunities', [JobInboxController::class, 'getJobOpportunities'])->name('job-opportunities');
        Route::get('/job-applications', [JobInboxController::class, 'getJobApplications'])->name('job-applications');
        Route::get('/notifications', [JobInboxController::class, 'getNotifications'])->name('notifications');
        Route::get('/notifications/{id}', [JobInboxController::class, 'showNotification'])->name('notifications.show');
        Route::post('/applications/{application}/offer-response', [JobInboxController::class, 'offerResponse'])->name('applications.offer.response');
        Route::post('/archive-job-opportunity', [JobInboxController::class, 'archiveJobOpportunity'])->name('archive-job-opportunity');
        Route::post('/mark-notification-as-read', [JobInboxController::class, 'markNotificationAsRead'])->name('mark-notification-as-read');
    });
});

// Profile Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');

    // Education Routes
    Route::get('/profile/settings/education', [ProfileController::class, 'educationSettings'])->name('profile.settings.education');
    Route::post('/profile/education', [ProfileController::class, 'addEducation'])->name('profile.education.add');
    Route::put('/profile/education/{id}', [ProfileController::class, 'updateEducation'])->name('profile.education.update');
    Route::delete('/profile/education/{id}', [ProfileController::class, 'deleteEducation'])->name('profile.education.delete');
    Route::put('/profile/education/{id}/archive', [ProfileController::class, 'archiveEducation'])->name('profile.education.archive');
    Route::put('/profile/education/{id}/unarchive', [ProfileController::class, 'unarchiveEducation'])->name('profile.education.unarchive');
    Route::put('/profile/education/{id}/archived', [ProfileController::class, 'archivedEducation'])->name('profile.education.archived');

    // Skill Routes
    Route::post('/profile/skills', [ProfileController::class, 'addSkill'])->name('profile.skills.add');
    Route::put('/profile/skills/{id}', [ProfileController::class, 'updateSkill'])->name('profile.skills.update');
    Route::delete('/profile/skills/{id}', [ProfileController::class, 'deleteSkill'])->name('profile.skills.remove');
    Route::put('/profile/skills/{id}/archive', [ProfileController::class, 'archiveSkill'])->name('profile.skills.archive');
    Route::put('/profile/skills/{id}/unarchive', [ProfileController::class, 'unarchiveSkill'])->name('profile.skills.unarchive');
    Route::put('/profile/skills/{id}/archived', [ProfileController::class, 'archivedSkill'])->name('profile.skills.archived');

    // Experience Routes
    Route::post('/profile/experiences', [ProfileController::class, 'addExperience'])->name('profile.experience.add');
    Route::put('/profile/experiences/{id}', [ProfileController::class, 'updateExperience'])->name('profile.experience.update');
    Route::delete('/profile/experiences/{id}', [ProfileController::class, 'removeExperience'])->name('profile.experience.remove');
    Route::put('/profile/experiences/{id}/archive', [ProfileController::class, 'archiveExperience'])->name('profile.experience.archive');
    Route::put('/profile/experiences/{id}/unarchive', [ProfileController::class, 'unarchiveExperience'])->name('profile.experience.unarchive');
    Route::put('/profile/experiences/{id}/archived', [ProfileController::class, 'archivedExperience'])->name('profile.experience.archived');

    // Project Routes
    Route::post('/profile/projects', [ProfileController::class, 'addProject'])->name('profile.projects.add');
    Route::put('/profile/projects/{id}', [ProfileController::class, 'updateProject'])->name('profile.projects.update');
    Route::delete('/profile/projects/{id}', [ProfileController::class, 'removeProject'])->name('profile.projects.remove');
    Route::put('/profile/projects/{id}/archive', [ProfileController::class, 'archiveProject'])->name('profile.projects.archive');
    Route::post('/profile/projects/{id}/unarchive', [ProfileController::class, 'unarchiveProject'])->name('profile.projects.unarchive');
    Route::put('/profile/projects/{id}/archived', [ProfileController::class, 'archivedProject'])->name('profile.projects.archived');

    // Certification Routes
    Route::post('/profile/certifications', [ProfileController::class, 'addCertification'])->name('profile.certifications.add');
    Route::put('/profile/certifications/{id}', [ProfileController::class, 'updateCertification'])->name('profile.certifications.update');
    Route::delete('/profile/certifications/{id}', [ProfileController::class, 'removeCertification'])->name('profile.certifications.remove');
    Route::put('/profile/certifications/{id}/archive', [ProfileController::class, 'archiveCertification'])->name('profile.certifications.archive');
    Route::post('/profile/certifications/{id}/unarchive', [ProfileController::class, 'unarchiveCertification'])->name('profile.certifications.unarchive');
    Route::put('/profile/certifications/{id}/archived', [ProfileController::class, 'archivedCertification'])->name('profile.certifications.archived');

    // Achievement Routes
    Route::post('/profile/achievements', [ProfileController::class, 'addAchievement'])->name('profile.achievements.add');
    Route::put('/profile/achievements/{id}', [ProfileController::class, 'updateAchievement'])->name('profile.achievements.update');
    Route::delete('/profile/achievements/{id}', [ProfileController::class, 'removeAchievement'])->name('profile.achievements.remove');
    Route::put('/profile/achievements/{id}/archive', [ProfileController::class, 'archiveAchievement'])->name('profile.achievements.archive');
    Route::post('/profile/achievements/{id}/unarchive', [ProfileController::class, 'unarchiveAchievement'])->name('profile.achievements.unarchive');
    Route::put('/profile/achievements/{id}/archived', [ProfileController::class, 'archivedAchievement'])->name('profile.achievements.archived');

    // Testimonial Routes
    Route::post('/profile/testimonials', [ProfileController::class, 'addTestimonial'])->name('profile.testimonials.add');
    Route::put('/profile/testimonials/{id}', [ProfileController::class, 'updateTestimonial'])->name('profile.testimonials.update');
    Route::delete('/profile/testimonials/{id}', [ProfileController::class, 'removeTestimonial'])->name('profile.testimonials.remove');
    Route::put('/profile/testimonials/{id}/archive', [ProfileController::class, 'archiveTestimonial'])->name('profile.testimonials.archive');
    Route::post('/profile/testimonials/{id}/unarchive', [ProfileController::class, 'unarchiveTestimonial'])->name('profile.testimonials.unarchive');
    Route::put('/profile/testimonials/{id}/archived', [ProfileController::class, 'archivedTestimonial'])->name('profile.testimonials.archived');

    // Alumni Stories Routes
    Route::get('/profile/alumni-stories/settings', [ProfileController::class, 'alumniStoriesSettings'])->name('profile.alumni-stories.settings');
    Route::post('/profile/alumni-stories', [ProfileController::class, 'addAlumniStory'])->name('profile.alumni-stories.add');
    Route::post('/profile/alumni-stories/{id}', [ProfileController::class, 'updateAlumniStory'])->name('profile.alumni-stories.update');
    Route::delete('/profile/alumni-stories/{id}', [ProfileController::class, 'deleteAlumniStory'])->name('profile.alumni-stories.delete');
    Route::put('/profile/alumni-stories/{id}/restore', [ProfileController::class, 'restoreAlumniStory'])->name('profile.alumni-stories.restore');

    // Employment Preferences Routes
    Route::post('/profile/employment-preferences', [ProfileController::class, 'updateEmploymentPreferences'])->name('employment.preferences.updateEmploymentPreferences');
    Route::post('/profile/employment-preferences', [ProfileController::class, 'saveEmploymentPreferences'])->name('employment.preferences.updateEmploymentPreferences');
    Route::post('/profile/employment-preferences/reset', [ProfileController::class, 'resetEmploymentPreferences'])->name('employment.preferences.reset');
    Route::get('/employment-preferences', [ProfileController::class, 'getEmploymentPreference'])->name('employment.preferences.get');

    // Career Goals Routes
    Route::post('/profile/career-goals', [ProfileController::class, 'saveCareerGoals'])->name('career.goals.save');
    Route::post('/career-goals/add-industry', [ProfileController::class, 'addIndustry'])->name('career.goals.add.industry');
    Route::post('/career-goals/save', [ProfileController::class, 'saveCareerGoals'])->name('career.goals.save');
    Route::get('/career-goals', [ProfileController::class, 'getCareerGoals'])->name('career.goals.get');

    // Resume Routes
    Route::post('/resume/upload', [ProfileController::class, 'uploadResume'])->name('resume.upload');
    Route::delete('/resume/delete', [ProfileController::class, 'deleteResume'])->name('resume.delete');
    Route::get('/profile/resume/settings', [ProfileController::class, 'resumeSettings'])->name('profile.resume.settings');

    // Batch Upload Routes
    Route::get('/profile/batch-upload', [ProfileController::class, 'batchUploadForm'])->name('profile.batch.upload.form');
    Route::post('/profile/batch-upload', [ProfileController::class, 'batchUpload'])->name('profile.batch.upload');
    Route::post('/profile/batch-upload-text', [ProfileController::class, 'batchUploadText'])->name('profile.batch.upload.text');
    Route::get('/profile/template/download', [ProfileController::class, 'downloadTemplate'])->name('profile.template.download');
});
Route::get('/profile/resume/settings', [ProfileController::class, 'resumeSettings'])->name('profile.resume.settings');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/graduates/{id}', [GraduateProfileController::class, 'show'])->name('graduates.profile');
    Route::get('/graduate/information', [GraduateProfileController::class, 'showInformationForm'])->name('graduate.information');
    Route::post('/graduate/information', [GraduateProfileController::class, 'saveInformation'])->name('graduate.information.save');
});



Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/job-search', [GraduateJobsController::class, 'search'])->name('job.search');
    Route::get('/graduate-jobs/recommendations', [GraduateJobsController::class, 'recommendations'])->name('graduate-jobs.recommendations');
    Route::post('/apply-for-job', [GraduateJobsController::class, 'applyForJob'])->name('apply-for-job');
    Route::post('graduates-jobs/one-click-apply', [GraduateJobsController::class, 'oneClickApply'])->name('jobs.oneClickApply');

    // Graduate Portfolio+

    Route::post('/graduate/referral/request', [GraduateJobsController::class, 'requestReferral'])->name('graduate.referral.request');
    Route::get('/company/profile/{id}', [CompanyProfileController::class, 'showPublic'])->name('company.profile.public');
    Route::get('/profile/graduate-portfolio', [ProfileController::class, 'graduatePortfolio'])->name(name: 'graduate.portfolio');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::post('/certificates/store', [ManageJobReferralsController::class, 'store'])->name('certificate.store');
    Route::get('/certificates/download/{filename}', [\App\Http\Controllers\ManageJobReferralsController::class, 'download'])
        ->name('certificates.download');
});


Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/peso/job-referrals', [ManageJobReferralsController::class, 'index'])->name('peso.job-referrals.index');
    Route::get('/peso/career-guidance', [PesoCareerGuidanceController::class, 'index'])->name('peso.career-guidance');
   
    Route::post('/job-referrals/{referral}/decline', [ManageJobReferralsController::class, 'decline'])->name('peso.job-referrals.decline');
    Route::post('/job-referrals/{referral}/mark-success', [ManageJobReferralsController::class, 'markSuccess'])->name('peso.job-referrals.mark-success');

    Route::get('/peso-reports', [App\Http\Controllers\Admin\PesoReportsController::class, 'reports'])->name('peso.reports.index');
    Route::get('/home/peso-reports', [App\Http\Controllers\Admin\PesoReportsController::class, 'index'])->name('peso.reports.home');
    Route::get('/peso-reports/employment', [App\Http\Controllers\Admin\PesoReportsController::class, 'employment'])->name('peso.reports.employment');
    Route::get('/peso-reports/employment/data', [App\Http\Controllers\Admin\PesoReportsController::class, 'employmentData'])->name('peso.reports.employment.data');
    Route::get('/peso-reports/referral', [App\Http\Controllers\Admin\PesoReportsController::class, 'referral'])->name('peso.reports.referral');
    Route::get('/peso-reports/referral/data', [App\Http\Controllers\Admin\PesoReportsController::class, 'referralData'])->name('peso.reports.referral.data');

    Route::get('/admin/job-referrals/{referral}/certificate', [ManageJobReferralsController::class, 'generateCertificate'])->name('peso.job-referrals.certificate');
    Route::get('/admin/seminar-requests', [PesoCareerGuidanceController::class, 'seminarRequests'])->name('admin.seminar-requests');
    Route::post('/admin/seminar-requests/{id}/status', [PesoCareerGuidanceController::class, 'updateSeminarRequestStatus'])->name('admin.seminar-requests.update-status');
    Route::get('/admin/seminar-requests/{id}', [PesoCareerGuidanceController::class, 'showSeminarRequest'])->name('admin.seminar-requests.show');
});



Route::prefix('admin')->middleware(['auth'])->group(function () { });

Route::post('/profile/testimonials/request', [ProfileController::class, 'requestTestimonial'])->name('profile.testimonials.request');
