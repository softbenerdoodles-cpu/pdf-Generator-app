<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\Templates\TemplateManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Details worth typing once: the business block that heads every document,
 * and the customers it gets addressed to.
 */
class AccountController extends Controller
{
    public function __construct(protected TemplateManager $templates) {}

    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Account', [
            'business' => $user->businessProfile?->only('name', 'email', 'phone', 'address'),
            'customers' => $user->customers()->get(['id', 'name', 'email', 'address']),
            'types' => $this->templates->summaries(),
        ]);
    }

    public function updateBusiness(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->businessProfile()->updateOrCreate([], $validated);

        return back()->with('status', 'Business details saved.');
    }

    public function storeCustomer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->customers()->create($validated);

        return back()->with('status', 'Customer added.');
    }

    public function destroyCustomer(Request $request, Customer $customer): RedirectResponse
    {
        abort_unless($customer->user_id === $request->user()->id, 404);

        $customer->delete();

        return back()->with('status', 'Customer removed.');
    }
}
