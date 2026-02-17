<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AddressController extends Controller
{
    /**
     * Display list of addresses
     */
    public function index()
    {
        $addresses = Auth::user()->addresses()->orderByDesc('is_default')->orderBy('name')->get();

        return Inertia::render('Customer/Addresses/Index', [
            'addresses' => $addresses,
        ]);
    }

    /**
     * Store a new address
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('customer_addresses')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
            'address' => 'required|string|max:500',
            'entrance' => 'nullable|string|max:20',
            'floor' => 'nullable|string|max:20',
            'apartment' => 'nullable|string|max:20',
            'landmark' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        $address = CustomerAddress::create($validated);

        // If this is the first address or marked as default, set as default
        if ($validated['is_default'] ?? false || Auth::user()->addresses()->count() === 1) {
            $address->setAsDefault();
        }

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Manzil muvaffaqiyatli qo\'shildi');
    }

    /**
     * Update an address
     */
    public function update(Request $request, CustomerAddress $address)
    {
        // Ensure user owns this address
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('customer_addresses')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })->ignore($address->id),
            ],
            'address' => 'required|string|max:500',
            'entrance' => 'nullable|string|max:20',
            'floor' => 'nullable|string|max:20',
            'apartment' => 'nullable|string|max:20',
            'landmark' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ]);

        $address->update($validated);

        if ($validated['is_default'] ?? false) {
            $address->setAsDefault();
        }

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Manzil muvaffaqiyatli yangilandi');
    }

    /**
     * Delete an address
     */
    public function destroy(CustomerAddress $address)
    {
        // Ensure user owns this address
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // If deleted address was default, set another one as default
        if ($wasDefault) {
            $newDefault = Auth::user()->addresses()->first();
            if ($newDefault) {
                $newDefault->setAsDefault();
            }
        }

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Manzil o\'chirildi');
    }

    /**
     * Set an address as default
     */
    public function setDefault(CustomerAddress $address)
    {
        // Ensure user owns this address
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $address->setAsDefault();

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Asosiy manzil o\'zgartirildi');
    }
}
