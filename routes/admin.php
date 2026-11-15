<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeaderController;
use App\Http\Controllers\Admin\CareerController;
use App\Http\Controllers\Admin\EducationController;
use App\Http\Controllers\Admin\PoliticalNetworkController;
use App\Http\Controllers\Admin\InferredDataController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\ImportHistoryController;
use App\Http\Controllers\Admin\DataQualityController;

Route::prefix('admin')->middleware('web')->name('admin.')->group(function(){
    Route::get('/login',[AuthController::class,'showLogin'])->name('login');
    Route::post('/login',[AuthController::class,'login'])->name('login.submit');
    Route::middleware(['auth','admin.role'])->group(function(){
        Route::post('/logout',[AuthController::class,'logout'])->name('logout');
        Route::get('/profile',[AuthController::class,'profile'])->name('profile');
        Route::post('/profile/password',[AuthController::class,'password'])->name('profile.password');
        Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
        Route::get('/data-quality',[DataQualityController::class,'index'])->name('data-quality');

        Route::get('/leaders',[LeaderController::class,'index'])->name('leaders.index');
        Route::get('/leaders/create',[LeaderController::class,'create'])->middleware('admin.role:super_admin,editor')->name('leaders.create');
        Route::post('/leaders',[LeaderController::class,'store'])->middleware('admin.role:super_admin,editor')->name('leaders.store');
        Route::post('/leaders/bulk-delete',[LeaderController::class,'bulkDestroy'])->middleware('admin.role:super_admin,editor')->name('leaders.bulk-delete');
        Route::get('/leaders/{leaderId}',[LeaderController::class,'show'])->name('leaders.show');
        Route::get('/leaders/{leaderId}/edit',[LeaderController::class,'edit'])->middleware('admin.role:super_admin,editor')->name('leaders.edit');
        Route::put('/leaders/{leaderId}',[LeaderController::class,'update'])->middleware('admin.role:super_admin,editor')->name('leaders.update');
        Route::delete('/leaders/{leaderId}',[LeaderController::class,'destroy'])->middleware('admin.role:super_admin')->name('leaders.destroy');

        Route::get('/leaders/{leaderId}/career/create',[CareerController::class,'create'])->middleware('admin.role:super_admin,editor')->name('career.create');
        Route::post('/leaders/{leaderId}/career',[CareerController::class,'store'])->middleware('admin.role:super_admin,editor')->name('career.store');
        Route::get('/leaders/{leaderId}/career/{seq}/edit',[CareerController::class,'edit'])->middleware('admin.role:super_admin,editor')->name('career.edit');
        Route::put('/leaders/{leaderId}/career/{seq}',[CareerController::class,'update'])->middleware('admin.role:super_admin,editor')->name('career.update');
        Route::delete('/leaders/{leaderId}/career/{seq}',[CareerController::class,'destroy'])->middleware('admin.role:super_admin,editor')->name('career.destroy');
        Route::post('/leaders/{leaderId}/career/bulk-delete',[CareerController::class,'bulkDestroy'])->middleware('admin.role:super_admin,editor')->name('career.bulk-delete');

        Route::get('/leaders/{leaderId}/education/create',[EducationController::class,'create'])->middleware('admin.role:super_admin,editor')->name('education.create');
        Route::post('/leaders/{leaderId}/education',[EducationController::class,'store'])->middleware('admin.role:super_admin,editor')->name('education.store');
        Route::get('/leaders/{leaderId}/education/{educationId}/edit',[EducationController::class,'edit'])->middleware('admin.role:super_admin,editor')->name('education.edit');
        Route::put('/leaders/{leaderId}/education/{educationId}',[EducationController::class,'update'])->middleware('admin.role:super_admin,editor')->name('education.update');
        Route::delete('/leaders/{leaderId}/education/{educationId}',[EducationController::class,'destroy'])->middleware('admin.role:super_admin,editor')->name('education.destroy');
        Route::post('/leaders/{leaderId}/education/bulk-delete',[EducationController::class,'bulkDestroy'])->middleware('admin.role:super_admin,editor')->name('education.bulk-delete');

        Route::get('/leaders/{leaderId}/network/edit',[PoliticalNetworkController::class,'edit'])->middleware('admin.role:super_admin,editor')->name('network.edit');
        Route::put('/leaders/{leaderId}/network',[PoliticalNetworkController::class,'update'])->middleware('admin.role:super_admin,editor')->name('network.update');
        Route::delete('/leaders/{leaderId}/network',[PoliticalNetworkController::class,'destroy'])->middleware('admin.role:super_admin')->name('network.destroy');
        Route::get('/leaders/{leaderId}/inferred/edit',[InferredDataController::class,'edit'])->middleware('admin.role:super_admin,editor')->name('inferred.edit');
        Route::put('/leaders/{leaderId}/inferred',[InferredDataController::class,'update'])->middleware('admin.role:super_admin,editor')->name('inferred.update');
        Route::delete('/leaders/{leaderId}/inferred',[InferredDataController::class,'destroy'])->middleware('admin.role:super_admin')->name('inferred.destroy');

        Route::get('/imports',[ImportController::class,'index'])->name('imports.index');
        Route::get('/imports/new',[ImportController::class,'create'])->middleware('admin.role:super_admin,editor')->name('imports.create');
        Route::post('/imports/upload',[ImportController::class,'upload'])->middleware('admin.role:super_admin,editor')->name('imports.upload');
        Route::get('/imports/map/{token}',[ImportController::class,'map'])->middleware('admin.role:super_admin,editor')->name('imports.map');
        Route::post('/imports/preview/{token}',[ImportController::class,'preview'])->middleware('admin.role:super_admin,editor')->name('imports.preview');
        Route::post('/imports/commit/{token}',[ImportController::class,'commit'])->middleware('admin.role:super_admin,reviewer')->name('imports.commit');
        Route::delete('/imports/cancel/{token}',[ImportController::class,'cancel'])->middleware('admin.role:super_admin,editor')->name('imports.cancel');
        Route::get('/imports/history',[ImportHistoryController::class,'index'])->name('imports.history');
        Route::get('/imports/history/{id}',[ImportHistoryController::class,'show'])->name('imports.history.show');
        Route::get('/imports/{table}',[ImportController::class,'show'])->name('imports.show');
        Route::delete('/imports/{table}',[ImportController::class,'clear'])->middleware('admin.role:super_admin')->name('imports.clear');

        Route::middleware('admin.role:super_admin')->group(function(){
            Route::resource('/users',UserController::class)->except(['show']);
            Route::get('/audit',[AuditController::class,'index'])->name('audit.index');
            Route::get('/audit/{id}',[AuditController::class,'show'])->name('audit.show');
        });
    });
});
