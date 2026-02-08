<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerAuthService
{
    public function __construct(protected UserRepository $userRepository) {}

    /**
     * Login or register a customer by phone number
     * Returns the User instance (either existing or newly created)
     */
    public function loginOrRegister(string $phone, string $locale = 'uz'): User
    {
        return DB::transaction(function () use ($phone, $locale) {
            $user = $this->userRepository->findByPhone($phone);

            // Create new customer if not exists
            if (!$user) {
                $user = User::create([
                    'name' => $phone, // Default name is phone number
                    'phone' => $phone,
                    'email' => null, // Email is optional for customers
                    'password' => Hash::make(Str::random(32)), // Random password (OTP-based, not used)
                    'status' => true,
                    'locale' => $locale,
                ]);

                // Assign customer role
                $user->assignRole('customer');
            } else {
                // Update locale preference
                $user->update(['locale' => $locale]);

                // Ensure customer has correct role
                if (!$user->hasRole('customer')) {
                    $user->assignRole('customer');
                }

                // Activate account if inactive
                if (!$user->status) {
                    $user->update(['status' => true]);
                }
            }

            return $user;
        });
    }
}
