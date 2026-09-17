<?php

use App\Http\Controllers\Accounting\BalanceSheetController;
use App\Http\Controllers\Accounting\ChartOfAccountsController;
use App\Http\Controllers\Accounting\JournalController;
use App\Http\Controllers\Accounting\LedgerController;
use App\Http\Controllers\Accounting\ProfitLossController;
use App\Http\Controllers\Accounting\TrialBalanceController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\FinancialYearController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Expense\ExpenseCategoryController;
use App\Http\Controllers\Expense\ExpenseController;
use App\Http\Controllers\Expense\ExpenseSubCategoryController;
use App\Http\Controllers\Inventory\GoodsReceiptController;
use App\Http\Controllers\Inventory\InventoryReportController;
use App\Http\Controllers\Inventory\ProductCategoryController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\StockAdjustmentController;
use App\Http\Controllers\Masters\BankAccountController;
use App\Http\Controllers\Masters\GstRateController;
use App\Http\Controllers\Masters\PaymentMethodController;
use App\Http\Controllers\Masters\TdsSectionController;
use App\Http\Controllers\Masters\UnitController;
use App\Http\Controllers\Party\PartyController;
use App\Http\Controllers\Party\PartyLedgerController;
use App\Http\Controllers\Procurement\PurchaseBillController;
use App\Http\Controllers\Procurement\PurchaseBillPaymentController;
use App\Http\Controllers\Procurement\PurchaseOrderController;
use App\Http\Controllers\Procurement\QuotationApprovalController;
use App\Http\Controllers\Procurement\VendorQuotationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Sales\DeliveryChallanController;
use App\Http\Controllers\Sales\SaleInvoiceController;
use App\Http\Controllers\Sales\SaleInvoicePaymentController;
use App\Http\Controllers\Sales\SaleOrderController;
use App\Http\Controllers\Settings\AuditLogController;
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\Vendor\VendorProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/companies/{company}/switch', [CompanyController::class, 'switch'])->name('companies.switch');

    Route::middleware('can:companies.manage')->group(function () {
        Route::resource('companies', CompanyController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:financial-years.manage')->group(function () {
        Route::get('/settings/financial-years', [FinancialYearController::class, 'index'])->name('financial-years.index');
        Route::get('/settings/financial-years/create', [FinancialYearController::class, 'create'])->name('financial-years.create');
        Route::post('/settings/financial-years', [FinancialYearController::class, 'store'])->name('financial-years.store');
        Route::post('/settings/financial-years/{financialYear}/activate', [FinancialYearController::class, 'activate'])->name('financial-years.activate');
        Route::post('/settings/financial-years/{financialYear}/lock', [FinancialYearController::class, 'lock'])->name('financial-years.lock');
        Route::post('/settings/financial-years/{financialYear}/unlock', [FinancialYearController::class, 'unlock'])->name('financial-years.unlock');
        Route::post('/settings/financial-years/{financialYear}/close', [FinancialYearController::class, 'close'])->name('financial-years.close');
    });

    Route::middleware('can:users.manage')->group(function () {
        Route::resource('/settings/users', UserController::class)
            ->parameters(['settings/users' => 'user'])
            ->except(['show', 'destroy'])
            ->names('settings.users');
    });

    Route::middleware('can:audit-logs.view')->group(function () {
        Route::get('/settings/audit-logs', [AuditLogController::class, 'index'])->name('settings.audit-logs.index');
    });

    Route::middleware('can:expense-categories.manage')->group(function () {
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show', 'destroy']);
        Route::resource('expense-sub-categories', ExpenseSubCategoryController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:parties.manage')->group(function () {
        Route::get('/party-ledger', [PartyLedgerController::class, 'index'])->name('party-ledger.index');
        Route::resource('parties', PartyController::class)->except(['destroy']);
        Route::post('/parties/{party}/products', [VendorProductController::class, 'store'])->name('parties.products.store');
        Route::put('/parties/{party}/products/{vendorProduct}', [VendorProductController::class, 'update'])->name('parties.products.update');
        Route::delete('/parties/{party}/products/{vendorProduct}', [VendorProductController::class, 'destroy'])->name('parties.products.destroy');
        Route::get('/parties/{party}/ledger', [PartyLedgerController::class, 'show'])->name('parties.ledger');
    });

    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/product-categories', [ProductCategoryController::class, 'index'])->name('product-categories.index');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    });

    Route::middleware('can:product-categories.manage')->group(function () {
        Route::resource('product-categories', ProductCategoryController::class)->except(['show', 'destroy', 'index']);
    });

    Route::middleware('can:products.manage')->group(function () {
        Route::resource('products', ProductController::class)->except(['show', 'destroy', 'index']);
    });

    // Route order is deliberate: literal segments (create, compare) must be
    // registered before the {quotation} wildcard, or Laravel's route matcher
    // (same-method, first-match) swallows them as an id.
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/vendor-quotations', [VendorQuotationController::class, 'index'])->name('vendor-quotations.index');
    });
    Route::middleware('can:vendor-quotations.manage')->group(function () {
        Route::get('/vendor-quotations/create', [VendorQuotationController::class, 'create'])->name('vendor-quotations.create');
    });
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/vendor-quotations/compare', [VendorQuotationController::class, 'compare'])->name('vendor-quotations.compare');
    });
    Route::middleware('can:vendor-quotations.manage')->group(function () {
        Route::post('/vendor-quotations', [VendorQuotationController::class, 'store'])->name('vendor-quotations.store');
    });
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/vendor-quotations/{quotation}', [VendorQuotationController::class, 'show'])->name('vendor-quotations.show');
    });
    Route::middleware('can:vendor-quotations.manage')->group(function () {
        Route::get('/vendor-quotations/{quotation}/edit', [VendorQuotationController::class, 'edit'])->name('vendor-quotations.edit');
        Route::put('/vendor-quotations/{quotation}', [VendorQuotationController::class, 'update'])->name('vendor-quotations.update');
        Route::post('/vendor-quotations/{quotation}/submit', [VendorQuotationController::class, 'submit'])->name('vendor-quotations.submit');
    });
    Route::middleware('can:vendor-quotations.approve')->group(function () {
        Route::post('/vendor-quotations/{quotation}/approval', [QuotationApprovalController::class, 'store'])->name('vendor-quotations.approval.store');
    });

    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    });
    Route::middleware('can:purchase-orders.manage')->group(function () {
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    });
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    });
    Route::middleware('can:purchase-orders.manage')->group(function () {
        Route::get('/purchase-orders/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
        Route::put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
        Route::post('/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])->name('purchase-orders.send');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    });

    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/goods-receipts', [GoodsReceiptController::class, 'index'])->name('goods-receipts.index');
    });
    Route::middleware('can:goods-receipts.manage')->group(function () {
        Route::get('/goods-receipts/create', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create');
        Route::post('/goods-receipts', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
    });
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->name('goods-receipts.show');
    });
    Route::middleware('can:goods-receipts.manage')->group(function () {
        Route::get('/goods-receipts/{goodsReceipt}/edit', [GoodsReceiptController::class, 'edit'])->name('goods-receipts.edit');
        Route::put('/goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'update'])->name('goods-receipts.update');
        Route::post('/goods-receipts/{goodsReceipt}/complete', [GoodsReceiptController::class, 'complete'])->name('goods-receipts.complete');
    });

    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/purchase-bills', [PurchaseBillController::class, 'index'])->name('purchase-bills.index');
    });
    Route::middleware('can:purchase-bills.manage')->group(function () {
        Route::get('/purchase-bills/create', [PurchaseBillController::class, 'create'])->name('purchase-bills.create');
        Route::post('/purchase-bills', [PurchaseBillController::class, 'store'])->name('purchase-bills.store');
    });
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/purchase-bills/{purchaseBill}', [PurchaseBillController::class, 'show'])->name('purchase-bills.show');
        Route::get('/purchase-bills/{purchaseBill}/print', [PurchaseBillController::class, 'print'])->name('purchase-bills.print');
    });
    Route::middleware('can:purchase-bills.manage')->group(function () {
        Route::get('/purchase-bills/{purchaseBill}/edit', [PurchaseBillController::class, 'edit'])->name('purchase-bills.edit');
        Route::put('/purchase-bills/{purchaseBill}', [PurchaseBillController::class, 'update'])->name('purchase-bills.update');
        Route::post('/purchase-bills/{purchaseBill}/post', [PurchaseBillController::class, 'post'])->name('purchase-bills.post');
        Route::post('/purchase-bills/{purchaseBill}/cancel', [PurchaseBillController::class, 'cancel'])->name('purchase-bills.cancel');
        Route::post('/purchase-bills/{purchaseBill}/payments', [PurchaseBillPaymentController::class, 'store'])->name('purchase-bills.payments.store');
        Route::post('/purchase-bills/{purchaseBill}/payments/{payment}/cancel', [PurchaseBillPaymentController::class, 'cancel'])->name('purchase-bills.payments.cancel');
    });

    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/sale-orders', [SaleOrderController::class, 'index'])->name('sale-orders.index');
    });
    Route::middleware('can:sale-orders.manage')->group(function () {
        Route::get('/sale-orders/create', [SaleOrderController::class, 'create'])->name('sale-orders.create');
        Route::post('/sale-orders', [SaleOrderController::class, 'store'])->name('sale-orders.store');
    });
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/sale-orders/{saleOrder}', [SaleOrderController::class, 'show'])->name('sale-orders.show');
    });
    Route::middleware('can:sale-orders.manage')->group(function () {
        Route::get('/sale-orders/{saleOrder}/edit', [SaleOrderController::class, 'edit'])->name('sale-orders.edit');
        Route::put('/sale-orders/{saleOrder}', [SaleOrderController::class, 'update'])->name('sale-orders.update');
        Route::post('/sale-orders/{saleOrder}/confirm', [SaleOrderController::class, 'confirm'])->name('sale-orders.confirm');
        Route::post('/sale-orders/{saleOrder}/cancel', [SaleOrderController::class, 'cancel'])->name('sale-orders.cancel');
    });

    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/delivery-challans', [DeliveryChallanController::class, 'index'])->name('delivery-challans.index');
    });
    Route::middleware('can:delivery-challans.manage')->group(function () {
        Route::get('/delivery-challans/create', [DeliveryChallanController::class, 'create'])->name('delivery-challans.create');
        Route::post('/delivery-challans', [DeliveryChallanController::class, 'store'])->name('delivery-challans.store');
    });
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/delivery-challans/{deliveryChallan}', [DeliveryChallanController::class, 'show'])->name('delivery-challans.show');
        Route::get('/delivery-challans/{deliveryChallan}/print', [DeliveryChallanController::class, 'print'])->name('delivery-challans.print');
    });
    Route::middleware('can:delivery-challans.manage')->group(function () {
        Route::get('/delivery-challans/{deliveryChallan}/edit', [DeliveryChallanController::class, 'edit'])->name('delivery-challans.edit');
        Route::put('/delivery-challans/{deliveryChallan}', [DeliveryChallanController::class, 'update'])->name('delivery-challans.update');
        Route::post('/delivery-challans/{deliveryChallan}/complete', [DeliveryChallanController::class, 'complete'])->name('delivery-challans.complete');
        Route::post('/delivery-challans/{deliveryChallan}/cancel', [DeliveryChallanController::class, 'cancel'])->name('delivery-challans.cancel');
    });

    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/sale-invoices', [SaleInvoiceController::class, 'index'])->name('sale-invoices.index');
    });
    Route::middleware('can:sale-invoices.manage')->group(function () {
        Route::get('/sale-invoices/create', [SaleInvoiceController::class, 'create'])->name('sale-invoices.create');
        Route::post('/sale-invoices', [SaleInvoiceController::class, 'store'])->name('sale-invoices.store');
    });
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/sale-invoices/{saleInvoice}', [SaleInvoiceController::class, 'show'])->name('sale-invoices.show');
        Route::get('/sale-invoices/{saleInvoice}/print', [SaleInvoiceController::class, 'print'])->name('sale-invoices.print');
    });
    Route::middleware('can:sale-invoices.manage')->group(function () {
        Route::get('/sale-invoices/{saleInvoice}/edit', [SaleInvoiceController::class, 'edit'])->name('sale-invoices.edit');
        Route::put('/sale-invoices/{saleInvoice}', [SaleInvoiceController::class, 'update'])->name('sale-invoices.update');
        Route::post('/sale-invoices/{saleInvoice}/post', [SaleInvoiceController::class, 'post'])->name('sale-invoices.post');
        Route::post('/sale-invoices/{saleInvoice}/cancel', [SaleInvoiceController::class, 'cancel'])->name('sale-invoices.cancel');
        Route::post('/sale-invoices/{saleInvoice}/payments', [SaleInvoicePaymentController::class, 'store'])->name('sale-invoices.payments.store');
        Route::post('/sale-invoices/{saleInvoice}/payments/{payment}/cancel', [SaleInvoicePaymentController::class, 'cancel'])->name('sale-invoices.payments.cancel');
    });

    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/stock-adjustments', [StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
    });
    Route::middleware('can:stock-adjustments.manage')->group(function () {
        Route::get('/stock-adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
        Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
    });
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/stock-adjustments/{stockAdjustment}', [StockAdjustmentController::class, 'show'])->name('stock-adjustments.show');
    });
    Route::middleware('can:stock-adjustments.approve')->group(function () {
        Route::post('/stock-adjustments/{stockAdjustment}/decision', [StockAdjustmentController::class, 'decide'])->name('stock-adjustments.decide');
    });

    Route::middleware('can:inventory.view')->prefix('inventory-reports')->name('inventory-reports.')->group(function () {
        Route::get('/low-stock', [InventoryReportController::class, 'lowStock'])->name('low-stock');
        Route::get('/movements', [InventoryReportController::class, 'movements'])->name('movements');
    });

    Route::middleware('can:masters.manage')->group(function () {
        Route::resource('units', UnitController::class)->except(['show', 'destroy']);
        Route::resource('payment-methods', PaymentMethodController::class)->except(['show', 'destroy']);
        Route::resource('gst-rates', GstRateController::class)->except(['show', 'destroy']);
        Route::resource('tds-sections', TdsSectionController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:bank-accounts.manage')->group(function () {
        Route::resource('bank-accounts', BankAccountController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:expenses.view')->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    });

    Route::middleware('can:expenses.manage')->group(function () {
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::post('/expenses/{expense}/cancel', [ExpenseController::class, 'cancel'])->name('expenses.cancel');
    });

    Route::middleware('can:accounting.view')->prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/chart-of-accounts', [ChartOfAccountsController::class, 'index'])->name('chart-of-accounts');
        Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
        Route::get('/journal/{journalEntry}', [JournalController::class, 'show'])->name('journal.show');
        Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger');
        Route::get('/trial-balance', [TrialBalanceController::class, 'index'])->name('trial-balance');
        Route::get('/profit-loss', [ProfitLossController::class, 'index'])->name('profit-loss');
        Route::get('/balance-sheet', [BalanceSheetController::class, 'index'])->name('balance-sheet');
    });

    Route::middleware('can:daily-notes.view')->prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->name('index');
        Route::get('/month', [CalendarController::class, 'month'])->name('month');
        Route::get('/notes', [CalendarController::class, 'notesForDate'])->name('notes');

        Route::middleware('can:daily-notes.manage')->group(function () {
            Route::post('/notes', [CalendarController::class, 'store'])->name('notes.store');
            Route::put('/notes/{dailyNote}', [CalendarController::class, 'update'])->name('notes.update');
            Route::delete('/notes/{dailyNote}', [CalendarController::class, 'destroy'])->name('notes.destroy');
        });
    });

    Route::middleware('can:reports.view')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/category-wise', [ReportController::class, 'categoryWise'])->name('category-wise');
        Route::get('/vendor-wise', [ReportController::class, 'vendorWise'])->name('vendor-wise');
        Route::get('/payment-wise', [ReportController::class, 'paymentWise'])->name('payment-wise');
        Route::get('/monthly-comparison', [ReportController::class, 'monthlyComparison'])->name('monthly-comparison');
        Route::get('/financial-year-summary', [ReportController::class, 'financialYearSummary'])->name('financial-year-summary');
        Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
        Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
        Route::get('/bank-cash-book', [ReportController::class, 'bankCashBook'])->name('bank-cash-book');
        Route::get('/{report}/export/{format}', [ReportController::class, 'export'])->name('export');
    });
});

require __DIR__.'/auth.php';
