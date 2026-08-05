<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CreditCardBillController;
use App\Http\Controllers\CreditCardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\FinancialGoalController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionImportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'audit'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/forecast', [ForecastController::class, 'index'])->name('forecast.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::resource('accounts', AccountController::class);
    Route::resource('categories', CategoryController::class)->except(['create', 'show']);
    Route::get('transactions/export/csv', [TransactionController::class, 'export'])->name('transactions.export');
    Route::get('transactions/export/ofx', [TransactionController::class, 'exportOfx'])->name('transactions.export.ofx');
    Route::get('transactions/import', [TransactionImportController::class, 'create'])->name('transactions.import.create');
    Route::post('transactions/import', [TransactionImportController::class, 'store'])->name('transactions.import.store');
    Route::post('transactions/import/{batch}/preview', [TransactionImportController::class, 'preview'])->name('transactions.import.preview');
    Route::get('transactions/import/{batch}', [TransactionImportController::class, 'show'])->name('transactions.import.show');
    Route::post('transactions/import/{batch}/commit', [TransactionImportController::class, 'commit'])->name('transactions.import.commit');
    Route::post('transactions/import/{batch}/revert', [TransactionImportController::class, 'revert'])->name('transactions.import.revert');
    Route::post('transactions/{transaction}/duplicate', [TransactionController::class, 'duplicate'])->name('transactions.duplicate');
    Route::patch('transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
    Route::resource('transactions', TransactionController::class)->except('show');

    Route::post('credit-cards/{credit_card}/bills', [CreditCardBillController::class, 'store'])->name('credit-cards.bills.store');
    Route::get('credit-card-bills/{bill}/edit', [CreditCardBillController::class, 'edit'])->name('credit-card-bills.edit');
    Route::patch('credit-card-bills/{bill}', [CreditCardBillController::class, 'update'])->name('credit-card-bills.update');
    Route::post('credit-card-bills/{bill}/pay', [CreditCardBillController::class, 'pay'])->name('credit-card-bills.pay');
    Route::delete('credit-card-bills/{bill}', [CreditCardBillController::class, 'destroy'])->name('credit-card-bills.destroy');
    Route::resource('credit-cards', CreditCardController::class);

    Route::post('debt-installments/{installment}/pay', [DebtController::class, 'pay'])->name('debts.installments.pay');
    Route::resource('debts', DebtController::class);

    Route::post('investments/{investment}/operations', [InvestmentController::class, 'operation'])->name('investments.operations.store');
    Route::resource('investments', InvestmentController::class);

    Route::post('budgets/copy-previous', [BudgetController::class, 'copy'])->name('budgets.copy');
    Route::resource('budgets', BudgetController::class)->except(['create', 'show']);
    Route::resource('goals', FinancialGoalController::class)->except('show');

    Route::get('reports/export/csv', [ReportController::class, 'export'])->name('reports.export');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/security-history', [AuditLogController::class, 'index'])->name('profile.audit-log');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::patch('/settings/toggle-values', [SettingsController::class, 'toggleValues'])->name('settings.toggle-values');
    Route::patch('/settings/toggle-theme', [SettingsController::class, 'toggleTheme'])->name('settings.toggle-theme');
});

require __DIR__.'/auth.php';
