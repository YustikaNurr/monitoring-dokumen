<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/documents');

// Authentication routes
require __DIR__.'/auth.php';

// Notifications
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}/confirm', [NotificationController::class, 'confirmFromNotification'])->name('notifications.confirm');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Documents
Route::middleware('auth')->group(function () {
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
    Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::post('/documents/{document}/workflows', [DocumentController::class, 'storeWorkflow'])->name('documents.workflows.store');
    Route::post('/documents/{document}/workflows/confirm-receipt', [DocumentController::class, 'confirmReceipt'])->name('documents.workflows.confirm-receipt');
    Route::get('/attachments/{attachment}/download', [DocumentController::class, 'downloadAttachment'])->name('attachments.download');
    Route::get('/attachments/{attachment}/preview/{filename?}', [DocumentController::class, 'previewAttachment'])
        ->where('filename', '.*')
        ->name('attachments.preview');
    Route::post('/documents/{document}/attachments', [DocumentController::class, 'storeAttachments'])->name('attachments.store');
    Route::delete('/attachments/{attachment}', [DocumentController::class, 'deleteAttachment'])->name('attachments.delete');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
});
