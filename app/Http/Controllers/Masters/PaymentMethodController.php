<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;

class PaymentMethodController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private MasterDataService $masterDataService) {}

    public function index()
    {
        $paymentMethods = PaymentMethod::where('company_id', current_company()?->id)->orderBy('name')->get();

        return view('masters.payment-methods.index', ['paymentMethods' => $paymentMethods]);
    }

    public function create()
    {
        return view('masters.payment-methods.create');
    }

    public function store(PaymentMethodRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();

        $this->masterDataService->create(PaymentMethod::class, [
            ...$request->validated(),
            'company_id' => $company->id,
        ], 'Payment Method');

        return redirect()->route('payment-methods.index')->with('status', 'Payment method created.');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        $this->ensureBelongsToCurrentCompany($paymentMethod);

        return view('masters.payment-methods.edit', ['paymentMethod' => $paymentMethod]);
    }

    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($paymentMethod);

        $this->masterDataService->update($paymentMethod, $request->validated(), 'Payment Method');

        return redirect()->route('payment-methods.index')->with('status', 'Payment method updated.');
    }
}
