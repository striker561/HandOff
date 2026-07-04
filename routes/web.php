<?php

use App\Http\Controllers\Agency\Projects\ProjectHubController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Registration is disabled — redirect to login gracefully
Route::get('/register', function () {
    return redirect()->route('login')->with('info', 'Registration is closed. New accounts are created by invitation only.');
})->name('register');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'ensureAdmin'])
    ->prefix('agency')
    ->name('agency.')
    ->group(function () {
        Route::view('clients', 'pages.agency.clients')->name('clients.index');
        Route::view('projects', 'pages.agency.projects')->name('projects.index');

        Route::prefix('projects/{projectUniqueId}')
            ->whereUuid('projectUniqueId')
            ->middleware('ensureProjectAccess')
            ->controller(ProjectHubController::class)
            ->group(function () {
                Route::get('/', 'overview')->name('projects.show');
                Route::get('/milestones', 'milestones')->name('projects.milestones');
                Route::get('/deliverables', 'deliverables')->name('projects.deliverables');
                Route::get('/credentials', 'credentials')->name('projects.credentials');
                Route::get('/meetings', 'meetings')->name('projects.meetings');
            });
    });

Route::middleware(['auth'])->group(function () {
    Route::redirect('profile', 'settings/profile');
});

require __DIR__ . '/settings.php';
