<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class CustomerService
{
    /**
     * Update an existing customer
     */
    public function update(User $customer, array $data, Request $request): User
    {
        $data['status'] = $request->boolean('status');

        $customer->update($data);

        return $customer;
    }

    /**
     * Delete a customer
     */
    public function delete(User $customer): void
    {
        $customer->delete();
    }
}
