<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\DynamicPostEditDispatcher;
use App\Http\Controllers\Admin\EditPostController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\PostImageController;
use App\Http\Controllers\Admin\PostFileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TaxonomyController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\Front\MaintenanceController as FrontMaintenanceController;
use App\Http\Controllers\Front\CategoryController as FrontCategoryController;
use App\Http\Controllers\Front\TagController as FrontTagController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\Front\PostController as FrontPostController;
use App\Http\Controllers\TwoFactorAuthController;
use App\Http\Middleware\TwoFactorAuthMiddleware;

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified', '2fa'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/', function () {
            return view('dashboard');
        })->name('dashboard');

        Route::post('/maintenance/enable', [MaintenanceController::class, 'enableMaintenance'])->name('admin.enableMaintenance');
        Route::post('/maintenance/disable', [MaintenanceController::class, 'disableMaintenance'])->name('admin.disableMaintenance');

        // Profil kezelés
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Beállítások
        Route::post('/save-per-page', [PostController::class, 'savePerPageSetting'])->name('admin.savePerPage');
        Route::get('/settings/sidebars', [SettingsController::class, 'sidebars'])->name('admin.settings.sidebars');
        Route::post('/settings/sidebars/update', [SettingsController::class, 'sidebarsUpdate'])->name('admin.settings.sidebars.update');
        Route::get('/settings/website', [SettingsController::class, 'website'])->name('admin.settings.website');
        Route::post('/settings/website', [SettingsController::class, 'websiteSettingsUpdate'])->name('admin.settings.website.update');

        Route::middleware(['validateTaxonomy'])->group(function () {
        // Taxonómiák kezelése
            Route::get('/taxonomies/{taxonomy_name}', [TaxonomyController::class, 'index'])->name('taxonomies.index');
            Route::post('/taxonomies/{taxonomy_name}/new', [TaxonomyController::class, 'createNewTerm'])->name('taxonomy.create');
            Route::get('/taxonomies/{taxonomy_name}/{term}', [TaxonomyController::class, 'show'])->name('taxonomy.edit');
            Route::put('/taxonomies/{taxonomy_name}/{term}/update', [TaxonomyController::class, 'update'])->name('taxonomy.update');
            Route::get('/taxonomies/{taxonomy_name}/{term}/delete', [TaxonomyController::class, 'delete'])->name('taxonomy.delete');
        });

        Route::middleware(['validatePostType'])->group(function () {
            // Bejegyzések és oldalak kezelése
            Route::get('/post-type/{post_type}s', [PostController::class, 'index'])->name('posts.index');
            Route::get('/post-type/{post_type}s/new', [PostController::class, 'createNewPost'])->name('post.create');
            Route::get('/post-type/{post_type}s/preview', [EditPostController::class, 'preview'])->name('post.preview');
            Route::post('/post-type/{post_type}s/group-action', [EditPostController::class, 'groupAction'])->name('post.group-action');
            Route::get('/post-type/{post_type}s/{post}', [EditPostController::class, 'show'])->name('post.edit');
            Route::put('/post-type/{post_type}s/{post}/update', [EditPostController::class, 'update'])->name('post.update');
            Route::get('/post-type/{post_type}s/{post}/delete', [EditPostController::class, 'delete'])->name('post.delete');
            Route::post('/post-type/{post_type}s/preview/save-temp', [EditPostController::class, 'saveTemp'])->name('post.saveTemp');
        });

        // Fájlfeltöltés
        Route::get('/upload-file', [UploadController::class, 'index'])->name('upload.form');
        Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');

        // Képek kezelése: Kép beillesztése tartalomba
        Route::get('/image-picker', [PostImageController::class, 'imagePicker'])->name('image.picker');
        Route::match(['get', 'post'], '/image-picker-delete-images', [PostImageController::class, 'deleteImages'])->name('image-picker-delete-images');
        Route::get('/image-picker/{id}/details', [PostImageController::class, 'getImageDetails']);
        Route::post('/image-picker/{id}/update', [PostImageController::class, 'updateImageDetails']);

        // Képek kezelése: Kép album létrehozása és beillesztése tartalomba
        Route::get('/image-album-picker', [PostImageController::class, 'imageAlbumPicker'])->name('image.album.picker');

        // Fájlok kezelése: Fájl beillesztése tartalomba
        Route::get('/file-picker', [PostFileController::class, 'filePicker'])->name('file.picker');
        Route::match(['get', 'post'], '/file-picker-delete-files', [PostFileController::class, 'deleteFiles'])->name('file-picker-delete-files');
        Route::get('/file-picker/{id}/details', [PostFileController::class, 'getFileDetails']);
        Route::get('/file-picker/{id}/data', [PostFileController::class, 'getFileData']);
        Route::post('/file-picker/{id}/update', [PostFileController::class, 'updateFileDetails']);

        // Fájlok kezelése: Fájl album létrehozása és beillesztése tartalomba
        Route::get('/file-album-picker', [PostFileController::class, 'fileAlbumPicker'])->name('file.album.picker');

        //EMAIL
        Route::get('/send-mail', [\App\Http\Controllers\Admin\SendMailController::class, 'showMailForm'])->name('admin.mail-form');
        Route::post('/send-mail', [\App\Http\Controllers\Admin\SendMailController::class, 'sendMail'])->name('admin.send-mail');
    });

    Route::get('/images', [ImageController::class, 'index']);
});


Route::middleware(['auth'])->group(function () {
    // 2FA setup (beállítás)
    Route::get('/two-factor-auth-setup/{mode}', [TwoFactorAuthController::class, 'auth2FaSetup'])->name('2fa.setup');
    Route::post('/two-factor-auth-setup/{mode}', [TwoFactorAuthController::class, 'auth2FaSetupStore'])->name('2fa.setup.store');

    // 2FA verify (bejelentkezéskor ellenőrzés)
    Route::get('/two-factor-auth-verify-email', [TwoFactorAuthController::class, 'verifyíEmailToken'])->name('2fa.verify.email');
    Route::post('/two-factor-auth-verify/{mode}', [TwoFactorAuthController::class, 'verify'])->name('2fa.verify.store');

    // 2FA kikapcsolás
    Route::post('/two-factor-auth-disable', [TwoFactorAuthController::class, 'disable'])->name('2fa.disable');
});


Route::get('/', [FrontPostController::class, 'home'])->name('front.home');;

Route::get('/maintenance', [FrontMaintenanceController::class, 'index'])->name('maintenance');

Route::get('/categories', [FrontCategoryController::class, 'index'])->name('front.categories');
Route::get('/categories/{category}', [FrontCategoryController::class, 'show'])->name('front.category');

Route::get('/tags', [FrontTagController::class, 'index'])->name('front.tags');
Route::get('/tag/{tag}', [FrontTagController::class, 'show'])->name('front.tag');



foreach (glob(base_path('Modules/*/routes/web.php')) as $routeFile) {
    require $routeFile;
}

foreach (glob(base_path('Themes/*/routes/web.php')) as $routeFile) {
    require $routeFile;
}


Route::get('/{slug}', [FrontPostController::class, 'show'])->name('post.show');
