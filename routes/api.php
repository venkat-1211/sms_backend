<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\StudentController;
use App\Http\Controllers\API\V1\CourseController;
use App\Http\Controllers\API\V1\AdmissionController;
use App\Http\Controllers\API\V1\DashboardController;

Route::prefix('v1')->group(function () {
    // Public Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('google', [AuthController::class, 'googleLogin']);
        Route::get('google-url', [AuthController::class, 'getGoogleAuthUrl']);
        Route::get('google-callback', [AuthController::class, 'googleCallback']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->name('verification.verify');
    });

    // Protected Routes
    Route::middleware('auth:api')->group(function () {
        // Authentication Routes
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refreshToken']);
            Route::get('profile', [AuthController::class, 'profile']);
            Route::put('profile', [AuthController::class, 'updateProfile']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
            Route::post('email/resend', [AuthController::class, 'resendVerificationEmail']);
            Route::post('revoke-all-tokens', [AuthController::class, 'revokeAllTokens']);
            Route::get('check', [AuthController::class, 'checkAuth']);
        });

        // Dashboard Routes
        Route::prefix('dashboard')->group(function () {
            Route::get('/', [DashboardController::class, 'index']);
            Route::get('stats', [DashboardController::class, 'stats']);
            Route::post('clear-cache', [DashboardController::class, 'clearCache']);
        });

        // Student Routes
        Route::prefix('students')->group(function () {
            Route::get('/', [StudentController::class, 'index'])->name('students.index');
            Route::post('/', [StudentController::class, 'store'])->name('students.store');
            Route::get('/search', [StudentController::class, 'search'])->name('students.search');
            Route::get('/stats', [StudentController::class, 'stats'])->name('students.stats');
            Route::get('/recent', [StudentController::class, 'recent'])->name('students.recent');
            Route::get('/gender-distribution', [StudentController::class, 'genderDistribution'])->name('students.gender');
            Route::post('/bulk-delete', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
            Route::get('/export', [StudentController::class, 'export'])->name('students.export');
            Route::get('/by-course/{courseId}', [StudentController::class, 'byCourse'])->name('students.by-course');
            Route::get('/{id}', [StudentController::class, 'show'])->name('students.show');
            Route::put('/{id}', [StudentController::class, 'update'])->name('students.update');
            Route::delete('/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
            Route::post('/{id}/toggle-status', [StudentController::class, 'toggleStatus'])->name('students.toggle-status');
        });

        // Course Routes
        Route::prefix('courses')->group(function () {
            Route::get('/', [CourseController::class, 'index'])->name('courses.index');
            Route::post('/', [CourseController::class, 'store'])->name('courses.store');
            Route::get('/search', [CourseController::class, 'search'])->name('courses.search');
            Route::get('/stats', [CourseController::class, 'stats'])->name('courses.stats');
            Route::get('/popular', [CourseController::class, 'popular'])->name('courses.popular');
            Route::get('/active', [CourseController::class, 'active'])->name('courses.active');
            Route::get('/{id}', [CourseController::class, 'show'])->name('courses.show');
            Route::put('/{id}', [CourseController::class, 'update'])->name('courses.update');
            Route::delete('/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');
            Route::post('/{id}/toggle-status', [CourseController::class, 'toggleStatus'])->name('courses.toggle-status');
        });

        // Admission Routes
        Route::prefix('admissions')->group(function () {
            Route::get('/', [AdmissionController::class, 'index'])->name('admissions.index');
            Route::post('/', [AdmissionController::class, 'store'])->name('admissions.store');
            Route::get('/search', [AdmissionController::class, 'search'])->name('admissions.search');
            Route::get('/stats', [AdmissionController::class, 'stats'])->name('admissions.stats');
            Route::get('/revenue', [AdmissionController::class, 'revenue'])->name('admissions.revenue');
            Route::get('/recent', [AdmissionController::class, 'recent'])->name('admissions.recent');
            Route::get('/payment-distribution', [AdmissionController::class, 'paymentDistribution'])->name('admissions.payment-distribution');
            Route::get('/monthly-revenue', [AdmissionController::class, 'monthlyRevenue'])->name('admissions.monthly-revenue');
            Route::get('/export', [AdmissionController::class, 'export'])->name('admissions.export');
            Route::get('/by-student/{studentId}', [AdmissionController::class, 'byStudent'])->name('admissions.by-student');
            Route::get('/by-course/{courseId}', [AdmissionController::class, 'byCourse'])->name('admissions.by-course');
            Route::get('/{id}', [AdmissionController::class, 'show'])->name('admissions.show');
            Route::put('/{id}', [AdmissionController::class, 'update'])->name('admissions.update');
            Route::delete('/{id}', [AdmissionController::class, 'destroy'])->name('admissions.destroy');
            Route::post('/{id}/pay', [AdmissionController::class, 'payFee'])->name('admissions.pay');
            Route::post('/{id}/toggle-status', [AdmissionController::class, 'toggleStatus'])->name('admissions.toggle-status');
        });
    });
});