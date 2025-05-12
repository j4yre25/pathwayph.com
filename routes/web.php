<?php

use App\Http\Controllers\GraduateGenerationController;
use App\Http\Controllers\ManageGraduatesController;
use App\Http\Controllers\PesoJobsController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\ManageUsersController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminRegisterController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\InstitutionProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\GraduateController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\CareerOpportunityController;
use App\Http\Controllers\ManageGraduatesApprovalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DegreeController;
use App\Http\Controllers\InstiSkillController;
use App\Http\Controllers\CustomRegisteredUserController;
use App\Http\Controllers\JobSearchController;
// Company 
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\HRRegisterController;
use App\Http\Controllers\CompanyJobsController;
use App\Http\Controllers\CompanyManageHRController;

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
use App\Http\Controllers\ResumeController;

use App\Notifications\VerifyEmailWithCode;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\CareerOfficerRegisterController;



Route::get('/', function () {
    return Inertia::render('Auth/Login');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/peso/register', [AdminRegisterController::class, 'showRegistrationForm'])->name('admin.register');
    Route::post('/peso/register', [AdminRegisterController::class, 'register'])->name('admin.register.submit');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/careerofficer/register', [CareerOfficerRegisterController::class, 'showRegistrationForm'])->name('careerofficer.register');
    Route::post('/careerofficer/register', [CareerOfficerRegisterController::class, 'register'])->name('careerofficer.submit');
});

// PESO Jobs
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('peso/jobs/{user}', [PesoJobsController::class, 'index'])
    ->name('peso.jobs');


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('peso/jobs/{user}/archivedlist', [PesoJobsController::class, 'archivedlist'])
    ->name('peso.jobs.archivedlist');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('peso/jobs/{user}/create', [PesoJobsController::class, 'create'])
    ->name('peso.jobs.create');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->post('peso/jobs/{user}', [PesoJobsController::class, 'store'])
    ->name('peso.jobs.store');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->get('peso/jobs/manage/{user}', [PesoJobsController::class, 'manage'])
    ->name('peso.jobs.manage');


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('peso/jobs/view/{job}', [PesoJobsController::class, 'view'])
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


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Education routes
    Route::post('/profile/education', [ProfileController::class, 'addEducation'])->name('profile.education.store');
    Route::put('/education/{id}', [ProfileController::class, 'updateEducation'])->name('education.update');
    Route::delete('/education/{id}', [ProfileController::class, 'removeEducation'])->name('education.remove');

    // Experience routes
    Route::post('/profile/experience', [ProfileController::class, 'addExperience'])->name('profile.experience.store');
    Route::put('/experience/{id}', [ProfileController::class, 'updateExperience'])->name('experience.update');
    Route::delete('/experience/{id}', [ProfileController::class, 'removeExperience'])->name('experience.remove');

    // Certification routes
    Route::post('/profile/certification', [ProfileController::class, 'addCertification'])->name('profile.certification.store');
    Route::put('/certification/{id}', [ProfileController::class, 'updateCertification'])->name('certification.update');
    Route::delete('/certification/{id}', [ProfileController::class, 'removeCertification'])->name('certification.remove');

    // Achievement routes
    Route::delete('/achievement/{id}', [ProfileController::class, 'removeAchievement'])->name('achievement.remove');

    // Project routes
    Route::post('/profile/project', [ProfileController::class, 'addProject'])->name('profile.project.store');
    Route::put('/project/{id}', [ProfileController::class, 'updateProject'])->name('project.update');
    Route::delete('/project/{id}', [ProfileController::class, 'removeProject'])->name('project.delete');
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
    Route::get('company/hr/register', [HRRegisterController::class, 'showRegistrationForm'])->name('hr.register');
    Route::post('company/hr/register', [HRRegisterController::class, 'register'])->name('hr.register.submit');
});

// CompanyDashboard Contents 
// Route::middleware(['auth' , config('jetstream.auth_session'), 'verified',])->group(function () {
//     Route::get('/dashboard', [ApplicationController::class, 'summary'])->name('dashboard');
// });


// Company Jobs Routes
// Route::prefix('user')->group(function () {
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('company/jobs/{user}', [CompanyJobsController::class, 'index'])
    ->name('company.jobs');


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('company/jobs/{user}/archivedlist', [CompanyJobsController::class, 'archivedlist'])
    ->name('company.jobs.archivedlist');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('company/jobs/{user}/create', [CompanyJobsController::class, 'create'])
    ->name('company.jobs.create');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->post('company/jobs/{user}', [CompanyJobsController::class, 'store'])
    ->name('company.jobs.store');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->get('company/jobs/manage/{user}', [CompanyJobsController::class, 'manage'])
    ->name('company.jobs.manage');


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('company/jobs/view/{job}', [CompanyJobsController::class, 'view'])
    ->name('company.jobs.view');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->get('company/jobs/edit/{job}', [CompanyJobsController::class, 'edit'])
    ->name('company.jobs.edit');


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->put('company/jobs/edit/{job}', [CompanyJobsController::class, 'update'])
    ->name('company.jobs.update');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->delete('company/jobs/edit/{job}', [CompanyJobsController::class, 'delete'])
    ->name('company.jobs.delete');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->post('company/jobs/{job}/auto-invite', [CompanyJobsController::class, 'autoInvite'])
    ->name('company.jobs.auto-invite');

Route::post('company/jobs/edit/{job}', [CompanyJobsController::class, 'restore'])->name('company.jobs.restore');
Route::post('company/jobs/{job}/approve', [CompanyJobsController::class, 'approve'])->name('company.jobs.approve');
Route::post('company/jobs/{job}/disapprove', [CompanyJobsController::class, 'disapprove'])->name('company.jobs.disapprove');


//Manage Applicants Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // View all applicants for a specific job
    Route::get('/jobs/{job}/applicants', [ApplicantController::class, 'index'])->name('applicants');

    // View details of a specific applicant
    Route::get('/applicants/{applicant}', [ApplicantController::class, 'show'])->name('applicants.show');

    // Update an applicant's status (e.g., mark as hired)
    Route::put('/applicants/{applicant}', [ApplicantController::class, 'update'])->name('applicants.update');

    // Delete an applicant
    Route::delete('/applicants/{applicant}', [ApplicantController::class, 'delete'])->name('applicants.delete');
});

// Manage HR Accounts 
Route::middleware(['auth'])->group(function () {
    Route::get('/company/manage-hrs', [CompanyManageHRController::class, 'index'])->name('company.manage-hrs');
    Route::get('/company/manage-hrs/{id}/edit', [CompanyManageHRController::class, 'edit'])->name('company.manage-hrs.edit');
    Route::put('/company/manage-hrs/{id}', [CompanyManageHRController::class, 'update'])->name('company.manage-hrs.update');
    Route::delete('/company/manage-hrs/{id}', [CompanyManageHRController::class, 'destroy'])->name('company.manage-hrs.destroy');
});
// Company Profile 
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // View Company Profile
    Route::get('/company/profile', [CompanyProfileController::class, 'profile'])->name('company.profile');
    Route::post('/company/profile', [CompanyProfileController::class, 'post'])->name('company-profile.post');
    Route::put('/company/profile', [CompanyProfileController::class, 'update'])->name('company-profile.update');
    Route::delete('/current-user-photo', [CompanyProfileController::class, 'destroyPhoto'])->name('current-user-photo.destroy');
    Route::delete('/current-user-cover-photo', [CompanyProfileController::class, 'destroyCoverPhoto'])->name('current-user-cover-photo.destroy');
});

//End of Company Routes



Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // View Company Profile
    Route::get('/admin/profile', [PesoProfileController::class, 'profile'])->name('peso.profile');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // View Institution Profile
    Route::get('/institution/profile', [InstitutionProfileController::class, 'profile'])->name('institution.profile');
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




Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Education routes
    Route::post('/profile/education', [ProfileController::class, 'addEducation'])->name('profile.education.store');
    Route::put('/education/{id}', [ProfileController::class, 'updateEducation'])->name('education.update');
    Route::delete('/education/{id}', [ProfileController::class, 'removeEducation'])->name('education.remove');

    // Experience routes
    Route::post('/profile/experience', [ProfileController::class, 'addExperience'])->name('profile.experience.store');
    Route::put('/experience/{id}', [ProfileController::class, 'updateExperience'])->name('experience.update');
    Route::delete('/experience/{id}', [ProfileController::class, 'removeExperience'])->name('experience.remove');

    // Certification routes
    Route::post('/profile/certification', [ProfileController::class, 'addCertification'])->name('profile.certification.store');
    Route::put('/certification/{id}', [ProfileController::class, 'updateCertification'])->name('certification.update');
    Route::delete('/certification/{id}', [ProfileController::class, 'removeCertification'])->name('certification.remove');

    // Achievement routes
    Route::delete('/achievement/{id}', [ProfileController::class, 'removeAchievement'])->name('achievement.remove');

    // Project routes
    Route::post('/profile/project', [ProfileController::class, 'addProject'])->name('profile.project.store');
    Route::put('/project/{id}', [ProfileController::class, 'updateProject'])->name('project.update');
    Route::delete('/project/{id}', [ProfileController::class, 'removeProject'])->name('project.delete');
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
    Route::get('/institutions/degrees/edit/{degree}', [DegreeController::class, 'edit'])->name('degrees.edit');
    Route::put('/institutions/degrees/edit/{degree}', [DegreeController::class, 'update'])->name('degrees.update');
    Route::delete('/institutions/degrees/edit/{degree}', [DegreeController::class, 'delete'])->name('degrees.delete');
    Route::get('/institutions/degrees/{user}/archivedlist', [DegreeController::class, 'archivedlist'])->name('degrees.archivedlist');
    Route::post('/institutions/degrees/edit/{degree}', [DegreeController::class, 'restore'])->name('degrees.restore');
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

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'can:manage institution'])->group(function () {
    Route::get('/graduates/manage', [ManageGraduatesApprovalController::class, 'index'])->name('graduates.manage');
    Route::get('/graduates/list', [ManageGraduatesApprovalController::class, 'list'])->name('graduates.list');
    Route::get('/graduates/archived', [ManageGraduatesApprovalController::class, 'archivedList'])->name('graduates.archived');
    Route::put('/graduates/approve/{user}', [ManageGraduatesApprovalController::class, 'approve'])->name('graduates.approve');
    Route::put('/graduates/disapprove/{user}', [ManageGraduatesApprovalController::class, 'disapprove'])->name('graduates.disapprove');
    Route::delete('/graduates/archive/{user}', [ManageGraduatesApprovalController::class, 'archive'])->name('graduates.archive');
    Route::put('/graduates/restore/{user}', [ManageGraduatesApprovalController::class, 'restore'])->name('graduates.restore');
});


// MAIN INSITUTION GRADUATE ROUTES
Route::middleware(['auth:sanctum',config('jetstream.auth_session'),'verified','can:manage institution'])->group(function () {
    
    Route::get('/graduates', [GraduateController::class, 'index'])->name('graduates.index');
    Route::get('/graduates/create', [GraduateController::class, 'create'])->name('graduates.create');
    Route::post('/graduates', [GraduateController::class, 'store'])->name('institution.graduate.store');
    Route::get('/graduates/upload', [GraduateController::class, 'batchPage'])->name('graduates.batch.page');
    Route::put('/graduates/{graduate}', [GraduateController::class, 'update'])->name('graduates.update');
    Route::delete('/graduates/{graduate}', [GraduateController::class, 'destroy'])->name('graduates.destroy');
    Route::post('/graduates/batch-upload', [GraduateController::class, 'batchUpload'])->name('graduates.batch.upload');
    Route::get('/graduates/template/download', [GraduateController::class, 'downloadTemplate'])->name('graduates.template.download');
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

            Route::get('/register/graduate', [CustomRegisteredUserController::class, 'create'])
                ->middleware(['guest:' . config('fortify.guard')])
                ->name('register.graduate');

            Route::post('/register/graduate', [CustomRegisteredUserController::class, 'store'])
                ->middleware(['guest:' . config('fortify.guard')])
                ->name('register.graduate.store');



            Route::get('/register/graduate', [CustomRegisteredUserController::class, 'showGraduateDetails'])
                ->name('register.showGraduateDetails');



            Route::get('/register/company', [CustomRegisteredUserController::class, 'create'])
                ->middleware(['guest:' . config('fortify.guard')])
                ->name('register.company');

            Route::get('/register/institution', [CustomRegisteredUserController::class, 'create'])
                ->middleware(['guest:' . config('fortify.guard')])
                ->name('register.institution');
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

        Route::post(RoutePath::for('two-factor.recovery-codes', '/user/two-factor-recovery-codes'), [RecoveryCodeController::class, 'store'])
            ->middleware($twoFactorMiddleware);
    }

    Route::middleware('auth:sanctum')->group(function () {
        // Fetch job opportunities
        Route::get('/job-opportunities', [JobInboxController::class, 'getJobOpportunities']);

        // Fetch job applications
        Route::get('/job-applications', [JobInboxController::class, 'getJobApplications']);

        // Fetch notifications
        Route::get('/notifications', [JobInboxController::class, 'getNotifications']);

        // Apply for a job
        Route::post('/apply-for-job', [JobInboxController::class, 'applyForJob']);

        // Archive a job opportunity
        Route::post('/archive-job-opportunity', [JobInboxController::class, 'archiveJobOpportunity']);

        // Mark notification as read
        Route::post('/mark-notification-as-read', [JobInboxController::class, 'markNotificationAsRead']);
    });
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/job-search', [JobSearchController::class, 'index'])->name('job-search.index');
    Route::post('/job-search/results', [JobSearchController::class, 'search'])->name('jobs.search.results');

    // Portfolio Routes
    Route::get('/portfolio', [ProfileController::class, 'showPortfolio'])->name('portfolio');
    Route::get('/portfolio/{id}', [PortfolioController::class, 'show']);
    Route::put('/portfolio/{id}', [PortfolioController::class, 'update']);

    // JobInbox Routes - Updated to use JobInboxController
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/job-inbox', [JobInboxController::class, 'index'])
            ->name('job.inbox')
            ->middleware(['auth', 'verified']);
        Route::get('/job-opportunities', [JobInboxController::class, 'getJobOpportunities'])->name('job-opportunities');
        Route::get('/job-applications', [JobInboxController::class, 'getJobApplications'])->name('job-applications');
        Route::get('/notifications', [JobInboxController::class, 'getNotifications'])->name('notifications');
        Route::post('/apply-for-job', [JobInboxController::class, 'applyForJob'])->name('apply-for-job');
        Route::post('/archive-job-opportunity', [JobInboxController::class, 'archiveJobOpportunity'])->name('archive-job-opportunity');
        Route::post('/mark-notification-as-read', [JobInboxController::class, 'markNotificationAsRead'])->name('mark-notification-as-read');
    });
});

// Profile Settings Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::put('/profile/education', [ProfileController::class, 'updateEducation'])->name('profile.education');
    Route::put('/profile/skills', [ProfileController::class, 'updateSkills'])->name('profile.skills');
    Route::put('/profile/projects', [ProfileController::class, 'updateProjects'])->name('profile.projects');
    Route::put('/profile/certifications', [ProfileController::class, 'updateCertifications'])->name('profile.certifications');
    Route::put('/profile/achievements', [ProfileController::class, 'updateAchievements'])->name('profile.achievements');
    Route::put('/profile/testimonials', [ProfileController::class, 'updateTestimonials'])->name('profile.testimonials');
    Route::put('/profile/career-goals', [ProfileController::class, 'updateCareerGoals'])->name('profile.career-goals');
    Route::put('/profile/resume', [ProfileController::class, 'updateResume'])->name('profile.resume');
});

// Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.updateProfile');

    // Education Routes
    Route::post('/profile/education', [ProfileController::class, 'addEducation'])->name('education.add');
    Route::put('/profile/education/{id}', [ProfileController::class, 'updateEducation'])->name('education.update');
    Route::delete('/profile/education/{id}', [ProfileController::class, 'removeEducation'])->name('education.remove');

    // Experience Routes
    Route::post('/profile/experiences', [ProfileController::class, 'addExperience'])->name('experience.addExperience');
    Route::put('/profile/experiences/{id}', [ProfileController::class, 'updateExperience'])->name('experience.updateExperience');
    Route::delete('/profile/experiences/{id}', [ProfileController::class, 'removeExperience'])->name('experience.remove c');

    // Project Routes
    Route::prefix('profile/projects')->group(function () {
        // Add these project routes
        Route::post('/projects/add', [ProfileController::class, 'addProject'])->name('projects.add');
        Route::put('/projects/{id}', [ProfileController::class, 'updateProject'])->name('projects.update');
        Route::delete('/projects/{id}', [ProfileController::class, 'removeProject'])->name('projects.remove');
    });

    // Skill Routes
    Route::post('/profile/skills', [ProfileController::class, 'addSkill'])->name('skills.add');
    Route::put('/profile/skills/{id}', [ProfileController::class, 'updateSkill'])->name('skills.update');
    Route::delete('/profile/skills/{id}', [ProfileController::class, 'removeSkill'])->name('skills.remove');

    // Certification Routes
    Route::post('/profile/certifications', [ProfileController::class, 'addCertification'])->name('certifications.add');
    Route::put('/profile/certifications/{id}', [ProfileController::class, 'updateCertification'])->name('certifications.update');
    Route::delete('/profile/certifications/{id}', [ProfileController::class, 'removeCertification'])->name('certifications.remove');

    // Achievement Routes
    Route::post('/profile/achievements', [ProfileController::class, 'addAchievement'])->name('achievements.add');
    Route::put('/profile/achievements/{id}', [ProfileController::class, 'updateAchievement'])->name('achievements.update');
    Route::delete('/profile/achievements/{id}', [ProfileController::class, 'deleteAchievement'])->name('achievements.delete');

    // Testimonial Routes
    Route::post('/profile/testimonials', [ProfileController::class, 'addTestimonial'])->name('testimonials.add');
    Route::put('/profile/testimonials/{id}', [ProfileController::class, 'updateTestimonial'])->name('testimonials.update');
    Route::delete('/profile/testimonials/{id}', [ProfileController::class, 'removeTestimonial'])->name('testimonials.remove');

    // Employment Preferences Routes
    Route::post('/profile/employment-preferences', [ProfileController::class, 'updateEmploymentPreferences'])->name('employment.preferences.updateEmploymentPreferences');
    Route::post('/employment-preferences/save', [ProfileController::class, 'saveEmploymentPreferences'])->name('employment.preferences.save');
    Route::post('/employment-references/save', [ProfileController::class, 'saveEmploymentReference'])->name('employment.references.save');
    Route::get('/employment-references', [ProfileController::class, 'getEmploymentReference'])->name('employment.references.get');

    // Career Goals Routes
    Route::post('/profile/career-goals', [ProfileController::class, 'saveCareerGoals'])->name('career.goals.save');
    Route::post('/career-goals/add-industry', [ProfileController::class, 'addIndustry'])->name('career.goals.add.industry');
    Route::post('/career-goals/save', [ProfileController::class, 'saveCareerGoals'])->name('career.goals.save');
    Route::get('/career-goals', [ProfileController::class, 'getCareerGoals'])->name('career.goals.get');

    // Resume Routes
    Route::post('/resume/upload', [ProfileController::class, 'uploadResume'])->name('resume.upload');
    Route::delete('/resume/delete', [ProfileController::class, 'deleteResume'])->name('resume.delete');
    Route::post('/upload', [ProfileController::class, 'uploadFile']);
    Route::get('/file/{filename}', [ProfileController::class, 'getFile']);
    Route::delete('/file/{filename}', [ProfileController::class, 'deleteFile']);
});