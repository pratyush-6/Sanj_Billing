@php
$navGroups = [];

if (auth()->user()->can('expenses.view')) {
    $items = [['route' => 'expenses.index', 'label' => __('nav.all_expenses'), 'pattern' => 'expenses.index']];
    if (auth()->user()->can('expenses.manage')) {
        $items[] = ['route' => 'expenses.create', 'label' => __('nav.add_expense'), 'pattern' => 'expenses.create'];
    }
    if (auth()->user()->can('expense-categories.manage')) {
        $items[] = ['route' => 'expense-categories.index', 'label' => __('nav.categories'), 'pattern' => 'expense-categories.*'];
        $items[] = ['route' => 'expense-sub-categories.index', 'label' => __('nav.sub_categories'), 'pattern' => 'expense-sub-categories.*'];
    }
    $navGroups['Expenses'] = ['icon' => 'banknotes', 'items' => $items];
}

if (auth()->user()->can('parties.manage')) {
    $navGroups['Parties'] = ['icon' => 'users', 'items' => [
        ['route' => 'parties.index', 'label' => __('nav.all_parties'), 'pattern' => 'parties.*'],
        ['route' => 'party-ledger.index', 'label' => __('nav.party_ledger'), 'pattern' => 'party-ledger.*'],
    ]];
}

$inventoryItems = [];
if (auth()->user()->can('inventory.view')) {
    $inventoryItems[] = ['route' => 'products.index', 'label' => __('nav.products'), 'pattern' => 'products.*'];
    $inventoryItems[] = ['route' => 'product-categories.index', 'label' => __('nav.product_categories'), 'pattern' => 'product-categories.*'];
    $inventoryItems[] = ['route' => 'stock-adjustments.index', 'label' => __('nav.stock_adjustments'), 'pattern' => 'stock-adjustments.*'];
    $inventoryItems[] = ['route' => 'stock-transfers.index', 'label' => __('nav.stock_transfers'), 'pattern' => 'stock-transfers.*'];
    $inventoryItems[] = ['route' => 'inventory-reports.low-stock', 'label' => __('nav.low_stock_report'), 'pattern' => 'inventory-reports.low-stock'];
    $inventoryItems[] = ['route' => 'inventory-reports.movements', 'label' => __('nav.stock_movements'), 'pattern' => 'inventory-reports.movements'];
}
if ($inventoryItems) {
    $navGroups['Inventory'] = ['icon' => 'box', 'items' => $inventoryItems];
}

$procurementItems = [];
if (auth()->user()->can('inventory.view')) {
    $procurementItems[] = ['route' => 'purchase-orders.index', 'label' => __('nav.purchase_orders'), 'pattern' => 'purchase-orders.index'];
    $procurementItems[] = ['route' => 'purchase-bills.index', 'label' => __('nav.purchase_bills'), 'pattern' => 'purchase-bills.*'];
}
if ($procurementItems) {
    $navGroups['Procurement'] = ['icon' => 'clipboard', 'items' => $procurementItems];
}

$salesItems = [];
if (auth()->user()->can('inventory.view')) {
    $salesItems[] = ['route' => 'sale-orders.index', 'label' => __('nav.sale_orders'), 'pattern' => 'sale-orders.*'];
    $salesItems[] = ['route' => 'delivery-challans.index', 'label' => __('nav.delivery_challans'), 'pattern' => 'delivery-challans.*'];
    $salesItems[] = ['route' => 'sale-invoices.index', 'label' => __('nav.sale_invoices'), 'pattern' => 'sale-invoices.*'];
}
if ($salesItems) {
    $navGroups['Sales'] = ['icon' => 'cart', 'items' => $salesItems];
}

if (auth()->user()->can('reports.view')) {
    $navGroups['Reports'] = ['icon' => 'chart', 'items' => [
        ['route' => 'reports.index', 'label' => __('nav.all_reports'), 'pattern' => 'reports.index'],
        ['route' => 'reports.sales', 'label' => __('nav.sales_report'), 'pattern' => 'reports.sales'],
        ['route' => 'reports.category-wise', 'label' => __('nav.category_wise'), 'pattern' => 'reports.category-wise'],
        ['route' => 'reports.vendor-wise', 'label' => __('nav.vendor_wise'), 'pattern' => 'reports.vendor-wise'],
        ['route' => 'reports.payment-wise', 'label' => __('nav.payment_wise'), 'pattern' => 'reports.payment-wise'],
        ['route' => 'reports.bank-cash-book', 'label' => __('nav.cash_bank_book'), 'pattern' => 'reports.bank-cash-book'],
        ['route' => 'reports.monthly-comparison', 'label' => __('nav.monthly_comparison'), 'pattern' => 'reports.monthly-comparison'],
        ['route' => 'reports.financial-year-summary', 'label' => __('nav.financial_year_summary'), 'pattern' => 'reports.financial-year-summary'],
    ]];
}

$masterItems = [];
if (auth()->user()->can('masters.manage')) {
    $masterItems[] = ['route' => 'units.index', 'label' => __('nav.units'), 'pattern' => 'units.*'];
    $masterItems[] = ['route' => 'payment-methods.index', 'label' => __('nav.payment_methods'), 'pattern' => 'payment-methods.*'];
    $masterItems[] = ['route' => 'gst-rates.index', 'label' => __('nav.gst_rates'), 'pattern' => 'gst-rates.*'];
    $masterItems[] = ['route' => 'tds-sections.index', 'label' => __('nav.tds_sections'), 'pattern' => 'tds-sections.*'];
}
if (auth()->user()->can('bank-accounts.manage')) {
    $masterItems[] = ['route' => 'bank-accounts.index', 'label' => __('nav.bank_cash_accounts'), 'pattern' => 'bank-accounts.*'];
}
if ($masterItems) {
    $navGroups['Masters'] = ['icon' => 'wrench', 'items' => $masterItems];
}

$companyItems = [];
if (auth()->user()->can('financial-years.manage')) {
    $companyItems[] = ['route' => 'financial-years.index', 'label' => __('nav.financial_years'), 'pattern' => 'financial-years.*'];
}
if (auth()->user()->can('companies.manage')) {
    $companyItems[] = ['route' => 'companies.index', 'label' => __('nav.companies'), 'pattern' => 'companies.*'];
}
if (auth()->user()->can('branches.manage')) {
    $companyItems[] = ['route' => 'branches.index', 'label' => __('nav.branches'), 'pattern' => 'branches.*'];
}
if ($companyItems) {
    $navGroups['Company'] = ['icon' => 'building', 'items' => $companyItems];
}

if (auth()->user()->can('accounting.view')) {
    $navGroups['Accounting'] = ['icon' => 'ledger', 'items' => [
        ['route' => 'accounting.chart-of-accounts', 'label' => __('nav.chart_of_accounts'), 'pattern' => 'accounting.chart-of-accounts'],
        ['route' => 'accounting.journal.index', 'label' => __('nav.journal'), 'pattern' => 'accounting.journal.*'],
        ['route' => 'accounting.ledger', 'label' => __('nav.ledger'), 'pattern' => 'accounting.ledger'],
        ['route' => 'accounting.trial-balance', 'label' => __('nav.trial_balance'), 'pattern' => 'accounting.trial-balance'],
        ['route' => 'accounting.profit-loss', 'label' => __('nav.profit_loss'), 'pattern' => 'accounting.profit-loss'],
        ['route' => 'accounting.balance-sheet', 'label' => __('nav.balance_sheet'), 'pattern' => 'accounting.balance-sheet'],
    ]];
}

$adminItems = [];
if (auth()->user()->can('users.manage')) {
    $adminItems[] = ['route' => 'settings.users.index', 'label' => __('nav.users'), 'pattern' => 'settings.users.*'];
}
if (auth()->user()->can('roles.manage')) {
    $adminItems[] = ['route' => 'settings.roles.index', 'label' => __('nav.roles_permissions'), 'pattern' => 'settings.roles.*'];
}
if (auth()->user()->can('audit-logs.view')) {
    $adminItems[] = ['route' => 'settings.audit-logs.index', 'label' => __('nav.audit_logs'), 'pattern' => 'settings.audit-logs.*'];
}
if ($adminItems) {
    $navGroups['Administration'] = ['icon' => 'shield', 'items' => $adminItems];
}

$accessibleCompanies = app(\App\Services\CompanyContextService::class)->accessibleCompanies();
$currentCompany = current_company();

$icons = [
    'home' => 'M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69zM12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z',
    'banknotes' => 'M2.25 18.75a60.07 60.07 0 0116.5 0M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H2.25m17.25 0h-2.25m-13.5 0a7.5 7.5 0 0115 0M2.25 6.75h19.5M2.25 6.75a1.5 1.5 0 011.5-1.5h16.5a1.5 1.5 0 011.5 1.5M2.25 6.75v11.25A1.5 1.5 0 003.75 19.5h16.5a1.5 1.5 0 001.5-1.5V6.75',
    'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    'wrench' => 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z',
    'chart' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
    'ledger' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
    'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
    'box' => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
    'clipboard' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z',
    'building' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    'shield' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
    'cart' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.997-4.708 2.598-7.201.121-.502-.245-.999-.76-.999H5.25M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z',
];
@endphp

<div class="flex h-full flex-col bg-white dark:bg-ink-900">
    <div class="flex h-16 shrink-0 items-center px-5 border-b border-ink-100 dark:border-ink-800">
        <a href="{{ route('dashboard') }}">
            <x-application-logo />
        </a>
    </div>

    <div class="flex items-start gap-2 px-3 pt-3">
    @if ($currentCompany && $accessibleCompanies->count() > 1)
        <div class="flex-1 min-w-0" x-data="{ open: false }" @click.outside="open = false">
            <div class="relative">
                <button @click="open = ! open" type="button" class="flex w-full items-center justify-between gap-2 rounded-lg border border-ink-200 dark:border-ink-700 px-3 py-2 text-sm hover:bg-ink-50 dark:hover:bg-ink-800">
                    <span class="truncate font-medium text-ink-800 dark:text-ink-100">{{ $currentCompany->name }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-ink-400 dark:text-ink-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                </button>
                <div x-show="open" x-transition style="display: none;" class="absolute z-40 mt-1 w-full rounded-lg border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 shadow-lg py-1">
                    @foreach ($accessibleCompanies as $company)
                        <form method="POST" action="{{ route('companies.switch', $company) }}">
                            @csrf
                            <button type="submit" @class([
                                'flex w-full items-center justify-between px-3 py-2 text-sm text-left hover:bg-ink-50 dark:hover:bg-ink-700',
                                'text-brand-700 dark:text-brand-400 font-medium' => $company->id === $currentCompany->id,
                                'text-ink-700 dark:text-ink-200' => $company->id !== $currentCompany->id,
                            ])>
                                {{ $company->name }}
                                @if ($company->id === $currentCompany->id)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                @endif
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if ($currentCompany)
        @php($branchContext = app(\App\Services\BranchContextService::class))
        @php($accessibleBranches = $branchContext->accessibleBranches())
        @php($currentBranch = current_branch())
        <div class="flex-1 min-w-0" x-data="{ open: false }" @click.outside="open = false">
            <div class="relative">
                <button @click="open = ! open" type="button" class="flex w-full items-center justify-between gap-2 rounded-lg border border-ink-200 dark:border-ink-700 px-3 py-2 text-sm hover:bg-ink-50 dark:hover:bg-ink-800">
                    <span class="truncate font-medium text-ink-800 dark:text-ink-100">
                        {{ $branchContext->isAllBranches() ? 'All branches' : ($currentBranch?->name ?? 'No branch') }}
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-ink-400 dark:text-ink-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                </button>
                <div x-show="open" x-transition style="display: none;" class="absolute z-40 mt-1 w-full rounded-lg border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 shadow-lg py-1">
                    @forelse ($accessibleBranches as $branch)
                        <form method="POST" action="{{ route('branches.switch', $branch) }}">
                            @csrf
                            <button type="submit" @class([
                                'flex w-full items-center justify-between px-3 py-2 text-sm text-left hover:bg-ink-50 dark:hover:bg-ink-700',
                                'text-brand-700 dark:text-brand-400 font-medium' => ! $branchContext->isAllBranches() && $currentBranch?->id === $branch->id,
                                'text-ink-700 dark:text-ink-200' => $branchContext->isAllBranches() || $currentBranch?->id !== $branch->id,
                            ])>
                                {{ $branch->name }}
                                @if ($branch->is_primary)
                                    <span class="text-[10px] font-semibold uppercase tracking-wide text-ink-400 dark:text-ink-500">{{ ui_label('Head') }}</span>
                                @endif
                            </button>
                        </form>
                    @empty
                        <div class="px-3 py-2 text-sm text-ink-400 dark:text-ink-500">No branch assigned.</div>
                    @endforelse
                    @if ($accessibleBranches->count() > 1)
                        <form method="POST" action="{{ route('branches.switch-all') }}" class="border-t border-ink-100 dark:border-ink-700">
                            @csrf
                            <button type="submit" class="flex w-full items-center px-3 py-2 text-sm text-left text-ink-700 dark:text-ink-200 hover:bg-ink-50 dark:hover:bg-ink-700">
                                All branches (reports)
                            </button>
                        </form>
                    @endif
                    @can('branches.manage')
                        <a href="{{ route('branches.index') }}" class="block border-t border-ink-100 dark:border-ink-700 px-3 py-2 text-sm text-brand-600 dark:text-brand-400 hover:bg-ink-50 dark:hover:bg-ink-700">Manage branches</a>
                    @endcan
                </div>
            </div>
        </div>
    @endif

    </div>

    <?php
        $defaultOpenGroup = array_key_first($navGroups);
        foreach ($navGroups as $groupLabel => $group) {
            foreach ($group['items'] as $item) {
                if (request()->routeIs($item['pattern'])) {
                    $defaultOpenGroup = $groupLabel;
                    break 2;
                }
            }
        }
    ?>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1"
         x-data="{
             openGroups: @js([$defaultOpenGroup => true]),
             toggle(label) { this.openGroups[label] = ! this.openGroups[label]; },
         }"
         x-init="$nextTick(() => $el.querySelector('a.bg-brand-50')?.scrollIntoView({ block: 'nearest' }))">
        <a href="{{ route('dashboard') }}"
           @class([
               'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
               'bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-400' => request()->routeIs('dashboard'),
               'text-ink-600 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-ink-800 hover:text-ink-900 dark:hover:text-ink-50' => ! request()->routeIs('dashboard'),
           ])>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['home'] }}" /></svg>
            {{ __('nav.dashboard') }}
        </a>

        @can('daily-notes.view')
            <a href="{{ route('calendar.index') }}"
               @class([
                   'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                   'bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-400' => request()->routeIs('calendar.index'),
                   'text-ink-600 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-ink-800 hover:text-ink-900 dark:hover:text-ink-50' => ! request()->routeIs('calendar.index'),
               ])>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['calendar'] }}" /></svg>
                {{ __('nav.calendar') }}
            </a>
        @endcan

        @foreach ($navGroups as $groupLabel => $group)
            <div class="pt-4">
                <button type="button" x-on:click="toggle('{{ $groupLabel }}')"
                        class="flex w-full items-center gap-1.5 px-3 text-xs font-semibold uppercase tracking-wider text-ink-400 dark:text-ink-500 hover:text-ink-600 dark:hover:text-ink-300 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$group['icon']] }}" /></svg>
                    <span class="flex-1 text-left">{{ __('nav.group.'.\Illuminate\Support\Str::slug($groupLabel)) }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform" :class="openGroups['{{ $groupLabel }}'] ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div x-show="openGroups['{{ $groupLabel }}']" x-transition.opacity>
                @foreach ($group['items'] as $item)
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'flex items-center gap-3 rounded-lg px-3 py-2 ms-1 text-sm font-medium transition border-s-2',
                           'bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-400 border-brand-500' => request()->routeIs($item['pattern']),
                           'text-ink-600 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-ink-800 hover:text-ink-900 dark:hover:text-ink-50 border-transparent' => ! request()->routeIs($item['pattern']),
                       ])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="border-t border-ink-100 dark:border-ink-800 p-3">
        <div class="flex items-center gap-3 rounded-lg px-3 py-2">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-ink-200 dark:bg-ink-700 text-xs font-semibold text-ink-600 dark:text-ink-200">
                {{ collect(explode(' ', Auth::user()->name))->map(fn ($p) => $p[0] ?? '')->join('') }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-ink-800 dark:text-ink-100">{{ Auth::user()->name }}</p>
                <a href="{{ route('profile.edit') }}" class="text-xs text-ink-400 dark:text-ink-500 hover:text-brand-600 dark:hover:text-brand-400">{{ __('nav.profile_settings') }}</a>
            </div>
        </div>
    </div>
</div>
