<?php

use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\Master\CategoryController;
use App\Http\Controllers\Web\PlaceholderPageController;
use App\Http\Controllers\Web\Report\ReportController;
use App\Http\Controllers\Web\Stock\StockCardController;
use App\Http\Controllers\Web\Stock\StockController;
use App\Http\Controllers\Web\Transaction\GoodsIssueController;
use App\Http\Controllers\Web\Transaction\GoodsReceiptController;
use App\Http\Controllers\Web\Transaction\GoodsReturnController;
use App\Http\Controllers\Web\Transaction\StockMutationController;
use App\Http\Controllers\Web\TransactionDocumentController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'active.user'])->group(function (): void {
    Route::get('dashboard', [App\Http\Controllers\Web\Dashboard\DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('items', [App\Http\Controllers\Web\Master\ItemController::class, 'index'])->middleware('permission:items.view')->name('items.index');
    Route::get('items/create', PlaceholderPageController::class)->middleware('permission:items.create')->name('items.create');

    Route::get('categories', [CategoryController::class, 'index'])->middleware('permission:categories.view')->name('categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->middleware('permission:categories.create')->name('categories.create');
    Route::post('categories', [CategoryController::class, 'store'])->middleware('permission:categories.create')->name('categories.store');
    Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->middleware('permission:categories.update')->name('categories.edit');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.update')->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:categories.delete')->name('categories.destroy');

    Route::get('suppliers', [App\Http\Controllers\Web\Master\SupplierController::class, 'index'])->middleware('permission:suppliers.view')->name('suppliers.index');
    Route::get('locations', [App\Http\Controllers\Web\Master\LocationController::class, 'index'])->middleware('permission:locations.view')->name('locations.index');
    Route::get('users', [App\Http\Controllers\Web\Master\UserController::class, 'index'])->middleware('permission:users.view')->name('users.index');

    Route::get('goods-receipts', [GoodsReceiptController::class, 'index'])->middleware('permission:goods-in.view')->name('goods-receipts.index');
    Route::get('goods-receipts/create', [GoodsReceiptController::class, 'create'])->middleware('permission:goods-in.create')->name('goods-receipts.create');
    Route::post('goods-receipts', [GoodsReceiptController::class, 'store'])->middleware('permission:goods-in.create')->name('goods-receipts.store');
    Route::get('goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->middleware('permission:goods-in.view')->name('goods-receipts.show');
    Route::post('goods-receipts/{goodsReceipt}/post', [GoodsReceiptController::class, 'post'])->middleware('permission:goods-in.post')->name('goods-receipts.post');
    Route::post('goods-receipts/{goodsReceipt}/documents', [TransactionDocumentController::class, 'storeGoodsReceipt'])->middleware('permission:documents.upload')->name('goods-receipts.documents.store');

    Route::get('goods-issues', [GoodsIssueController::class, 'index'])->middleware('permission:goods-out.view')->name('goods-issues.index');
    Route::get('goods-issues/create', [GoodsIssueController::class, 'create'])->middleware('permission:goods-out.create')->name('goods-issues.create');
    Route::post('goods-issues', [GoodsIssueController::class, 'store'])->middleware('permission:goods-out.create')->name('goods-issues.store');
    Route::get('goods-issues/{goodsIssue}', [GoodsIssueController::class, 'show'])->middleware('permission:goods-out.view')->name('goods-issues.show');
    Route::post('goods-issues/{goodsIssue}/post', [GoodsIssueController::class, 'post'])->middleware('permission:goods-out.post')->name('goods-issues.post');
    Route::get('goods-issues/{goodsIssue}/handover', [GoodsIssueController::class, 'handover'])->middleware('permission:goods-out.view')->name('goods-issues.handover');
    Route::post('goods-issues/{goodsIssue}/documents', [TransactionDocumentController::class, 'storeGoodsIssue'])->middleware('permission:documents.upload')->name('goods-issues.documents.store');

    Route::get('documents/{document}/download', [TransactionDocumentController::class, 'download'])->middleware('permission:documents.download')->name('documents.download');
    Route::delete('documents/{document}', [TransactionDocumentController::class, 'destroy'])->middleware('permission:documents.delete')->name('documents.destroy');

    Route::get('goods-returns', [GoodsReturnController::class, 'index'])->middleware('permission:returns.view')->name('goods-returns.index');
    Route::get('goods-returns/create', [GoodsReturnController::class, 'create'])->middleware('permission:returns.create')->name('goods-returns.create');
    Route::post('goods-returns', [GoodsReturnController::class, 'store'])->middleware('permission:returns.create')->name('goods-returns.store');
    Route::get('goods-returns/{goodsReturn}', [GoodsReturnController::class, 'show'])->middleware('permission:returns.view')->name('goods-returns.show');
    Route::post('goods-returns/{goodsReturn}/post', [GoodsReturnController::class, 'post'])->middleware('permission:returns.post')->name('goods-returns.post');

    Route::get('stock-mutations', [StockMutationController::class, 'index'])->middleware('permission:mutations.view')->name('stock-mutations.index');
    Route::get('stock-mutations/create', [StockMutationController::class, 'create'])->middleware('permission:mutations.create')->name('stock-mutations.create');
    Route::post('stock-mutations', [StockMutationController::class, 'store'])->middleware('permission:mutations.create')->name('stock-mutations.store');
    Route::get('stock-mutations/{stockMutation}', [StockMutationController::class, 'show'])->middleware('permission:mutations.view')->name('stock-mutations.show');
    Route::post('stock-mutations/{stockMutation}/post', [StockMutationController::class, 'post'])->middleware('permission:mutations.post')->name('stock-mutations.post');

    Route::get('stock', [StockController::class, 'index'])->middleware('permission:stock.view')->name('stock.index');
    Route::get('stock/{stock}/card', [StockCardController::class, 'index'])->middleware('permission:stock.card')->name('stock.card');

    Route::get('reports', [ReportController::class, 'index'])->middleware('permission:reports.view')->name('reports.index');
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function (): void {
        Route::get('stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('goods-in', [ReportController::class, 'goodsIn'])->name('goods-in');
        Route::get('goods-out', [ReportController::class, 'goodsOut'])->name('goods-out');
        Route::get('stock/export', [ReportController::class, 'exportStock'])->middleware('permission:reports.export')->name('stock.export');
        Route::get('goods-in/export', [ReportController::class, 'exportGoodsIn'])->middleware('permission:reports.export')->name('goods-in.export');
        Route::get('goods-out/export', [ReportController::class, 'exportGoodsOut'])->middleware('permission:reports.export')->name('goods-out.export');
    });

    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit-log.view')->name('audit-logs.index');
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->middleware('permission:audit-log.view')->name('audit-logs.show');
});

require __DIR__.'/settings.php';
