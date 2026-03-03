<?php

use App\Http\Controllers\Auth\SendMagicLoginLinkController;
use App\Http\Controllers\MagicLoginController;
use App\Livewire\Auth\MagicLoginRequest;
use App\Livewire\Categories\Form as CategoryForm;
use App\Livewire\Categories\Index as CategoryIndex;
use App\Livewire\ClientProjectRates\Form as ClientProjectRateForm;
use App\Livewire\ClientProjectRates\Index as ClientProjectRateIndex;
use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Clients\Index as ClientIndex;
use App\Livewire\Clients\Show as ClientShow;
use App\Livewire\Dashboard\DashboardPage;
use App\Livewire\Invoices\Form as InvoiceForm;
use App\Livewire\Invoices\Index as InvoiceIndex;
use App\Livewire\Issues\Form as IssueForm;
use App\Livewire\Issues\Index as IssueIndex;
use App\Livewire\Issues\Show as IssueShow;
use App\Livewire\KpoReports\Index as KpoReportIndex;
use App\Livewire\MonthlyExpenses\Index as MonthlyExpenseIndex;
use App\Livewire\PaidExpenses\Index as PaidExpenseIndex;
use App\Livewire\Projects\Form as ProjectForm;
use App\Livewire\Projects\Index as ProjectIndex;
use App\Livewire\Projects\Show as ProjectShow;
use App\Livewire\Settings\IssueCategories\Form as IssueCategoryForm;
use App\Livewire\Settings\IssueCategories\Index as IssueCategoryIndex;
use App\Livewire\Settings\IssuePriorities\Form as IssuePriorityForm;
use App\Livewire\Settings\IssuePriorities\Index as IssuePriorityIndex;
use App\Livewire\Settings\IssueStatuses\Form as IssueStatusForm;
use App\Livewire\Settings\IssueStatuses\Index as IssueStatusIndex;
use App\Livewire\TaxYears\Form as TaxYearForm;
use App\Livewire\TaxYears\Index as TaxYearIndex;
use App\Livewire\Transactions\Index as TransactionIndex;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/magic-login')->name('home');
Route::redirect('/login', '/magic-login')->name('login');

Route::middleware('guest')->group(function () {
    Route::post('/magic-login', SendMagicLoginLinkController::class)
        ->middleware('throttle:6,1')
        ->name('magic-login.send');
    Route::get('/magic-login', MagicLoginRequest::class)->name('magic-login.request');
    Route::get('/magic-login/{token}', MagicLoginController::class)
        ->middleware('signed')
        ->name('magic-login.consume');
});

Route::livewire('dashboard', DashboardPage::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::livewire('clients', ClientIndex::class)->name('clients.index');
    Route::livewire('clients/create', ClientForm::class)->name('clients.create');
    Route::livewire('clients/{client}/edit', ClientForm::class)->name('clients.edit');
    Route::livewire('clients/{client}', ClientShow::class)->name('clients.show');
    Route::livewire('projects', ProjectIndex::class)->name('projects.index');
    Route::livewire('projects/create', ProjectForm::class)->name('projects.create');
    Route::livewire('projects/{project}/edit', ProjectForm::class)->name('projects.edit');
    Route::livewire('projects/{project}', ProjectShow::class)->name('projects.show');
    Route::livewire('client-project-rates', ClientProjectRateIndex::class)->name('client-project-rates.index');
    Route::livewire('client-project-rates/create', ClientProjectRateForm::class)->name('client-project-rates.create');
    Route::livewire('client-project-rates/{clientProjectRate}/edit', ClientProjectRateForm::class)->name('client-project-rates.edit');
    Route::livewire('invoices', InvoiceIndex::class)->name('invoices.index');
    Route::livewire('invoices/create', InvoiceForm::class)->name('invoices.create');
    Route::livewire('invoices/{invoice}/edit', InvoiceForm::class)->name('invoices.edit');
    Route::livewire('categories', CategoryIndex::class)->name('categories.index');
    Route::livewire('categories/create', CategoryForm::class)->name('categories.create');
    Route::livewire('categories/{category}/edit', CategoryForm::class)->name('categories.edit');
    Route::livewire('transactions', TransactionIndex::class)->name('transactions.index');
    Route::livewire('monthly-expenses', MonthlyExpenseIndex::class)->name('monthly-expenses.index');
    Route::livewire('paid-expenses', PaidExpenseIndex::class)->name('paid-expenses.index');
    Route::livewire('tax-years', TaxYearIndex::class)->name('tax-years.index');
    Route::livewire('tax-years/create', TaxYearForm::class)->name('tax-years.create');
    Route::livewire('tax-years/{taxYear}/edit', TaxYearForm::class)->name('tax-years.edit');
    Route::livewire('kpo-reports', KpoReportIndex::class)->name('kpo-reports.index');
    Route::redirect('issue-board', 'issues')->name('issues.board');
    Route::livewire('issues', IssueIndex::class)->name('issues.index');
    Route::livewire('issues/create', IssueForm::class)->name('issues.create');
    Route::livewire('issues/{issue}', IssueShow::class)->name('issues.show');
    Route::livewire('issues/{issue}/edit', IssueForm::class)->name('issues.edit');
    Route::livewire('settings/issue-statuses', IssueStatusIndex::class)->name('settings.issue-statuses.index');
    Route::livewire('settings/issue-statuses/create', IssueStatusForm::class)->name('settings.issue-statuses.create');
    Route::livewire('settings/issue-statuses/{issueStatus}/edit', IssueStatusForm::class)->name('settings.issue-statuses.edit');
    Route::livewire('settings/issue-priorities', IssuePriorityIndex::class)->name('settings.issue-priorities.index');
    Route::livewire('settings/issue-priorities/create', IssuePriorityForm::class)->name('settings.issue-priorities.create');
    Route::livewire('settings/issue-priorities/{issuePriority}/edit', IssuePriorityForm::class)->name('settings.issue-priorities.edit');
    Route::livewire('settings/issue-categories', IssueCategoryIndex::class)->name('settings.issue-categories.index');
    Route::livewire('settings/issue-categories/create', IssueCategoryForm::class)->name('settings.issue-categories.create');
    Route::livewire('settings/issue-categories/{issueCategory}/edit', IssueCategoryForm::class)->name('settings.issue-categories.edit');
});

require __DIR__.'/settings.php';
