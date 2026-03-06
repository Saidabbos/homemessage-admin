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
     * Toggle customer restriction (general block)
     */
    public function toggleRestriction(User $customer, ?string $reason, int $adminId): User
    {
        if ($customer->isRestricted()) {
            $customer->update([
                'restriction_reason' => null,
                'restricted_at' => null,
                'restricted_by' => null,
            ]);
        } else {
            $customer->update([
                'restriction_reason' => $reason,
                'restricted_at' => now(),
                'restricted_by' => $adminId,
            ]);
        }

        return $customer->fresh();
    }

    /**
     * Update booking cutoff hour for a customer
     */
    public function updateCutoffHour(User $customer, ?int $cutoffHour, ?string $reason, int $adminId): User
    {
        $data = ['booking_cutoff_hour' => $cutoffHour];

        if ($cutoffHour !== null && !$customer->isRestricted()) {
            $data['restriction_reason'] = $reason;
            $data['restricted_at'] = now();
            $data['restricted_by'] = $adminId;
        } elseif ($cutoffHour === null) {
            $data['restriction_reason'] = null;
            $data['restricted_at'] = null;
            $data['restricted_by'] = null;
        }

        $customer->update($data);
        return $customer->fresh();
    }

    /**
     * Update admin notes for a customer
     */
    public function updateNotes(User $customer, ?string $notes): User
    {
        $customer->update(['admin_notes' => $notes]);
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
