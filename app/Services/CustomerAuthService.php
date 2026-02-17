<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomerAuthService
{
    public function __construct(protected UserRepository $userRepository) {}

    /**
     * Login or register a customer by phone number
     * Returns array with 'user' and 'is_new' flag
     * 
     * @param string $phone Phone number
     * @param string $locale User locale preference
     * @param array|null $telegramData Optional Telegram data ['id', 'username', 'first_name', 'photo_url']
     * @return array{user: User, is_new: bool}
     */
    public function loginOrRegister(string $phone, string $locale = 'uz', ?array $telegramData = null): array
    {
        Log::info('CustomerAuthService: Login/register attempt', [
            'phone' => $phone, 
            'locale' => $locale,
            'has_telegram' => !empty($telegramData),
        ]);

        return DB::transaction(function () use ($phone, $locale, $telegramData) {
            $user = $this->userRepository->findByPhone($phone);
            $isNew = false;

            // Create new customer if not exists
            if (!$user) {
                $isNew = true;
                
                // Use Telegram first_name as name, or phone as fallback
                $name = $telegramData['first_name'] ?? $phone;

                $user = User::create([
                    'name' => $name,
                    'phone' => $phone,
                    'email' => null, // Email is optional for customers
                    'password' => Hash::make(Str::random(32)), // Random password (OTP-based, not used)
                    'status' => true,
                    'locale' => $locale,
                    'telegram_id' => $telegramData['id'] ?? null,
                    'telegram_username' => $telegramData['username'] ?? null,
                    'telegram_first_name' => $telegramData['first_name'] ?? null,
                    'telegram_photo_url' => $telegramData['photo_url'] ?? null,
                ]);

                // Assign customer role
                $user->assignRole('customer');
                Log::info('CustomerAuthService: New customer registered', [
                    'user_id' => $user->id, 
                    'phone' => $phone,
                    'name' => $name,
                    'telegram_id' => $telegramData['id'] ?? null,
                ]);
            } else {
                // Update locale preference
                $user->update(['locale' => $locale]);

                // Link Telegram if provided and not already linked
                if ($telegramData && !$user->telegram_id && !empty($telegramData['id'])) {
                    $user->update([
                        'telegram_id' => $telegramData['id'],
                        'telegram_username' => $telegramData['username'] ?? null,
                        'telegram_first_name' => $telegramData['first_name'] ?? null,
                        'telegram_photo_url' => $telegramData['photo_url'] ?? null,
                    ]);
                    Log::info('CustomerAuthService: Telegram linked to existing user', [
                        'user_id' => $user->id,
                        'telegram_id' => $telegramData['id'],
                    ]);
                }

                // Ensure customer has correct role
                if (!$user->hasRole('customer')) {
                    $user->assignRole('customer');
                    Log::info('CustomerAuthService: Customer role assigned', ['user_id' => $user->id]);
                }

                // Activate account if inactive
                if (!$user->status) {
                    $user->update(['status' => true]);
                    Log::info('CustomerAuthService: Account activated', ['user_id' => $user->id]);
                }

                Log::info('CustomerAuthService: Existing customer logged in', ['user_id' => $user->id, 'phone' => $phone]);
            }

            return ['user' => $user, 'is_new' => $isNew];
        });
    }
}
