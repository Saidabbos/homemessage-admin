<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Nodira Xasanova',
                'phone' => '+998901111567',
                'email' => 'nodira@example.com',
                'locale' => 'uz',
            ],
            [
                'name' => 'Sherzod Mirzayev',
                'phone' => '+998937654321',
                'email' => 'sherzod@example.com',
                'locale' => 'ru',
            ],
            [
                'name' => 'Kamola Tursunova',
                'phone' => '+998911112233',
                'email' => 'kamola@example.com',
                'locale' => 'uz',
            ],
            [
                'name' => 'Bobur Abdullayev',
                'phone' => '+998944445566',
                'email' => 'bobur@example.com',
                'locale' => 'uz',
            ],
            [
                'name' => 'Madina Rahimova',
                'phone' => '+998907778899',
                'email' => 'madina@example.com',
                'locale' => 'ru',
            ],
        ];

        foreach ($customers as $data) {
            $user = User::firstOrCreate(
                ['phone' => $data['phone']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt('password'),
                    'locale' => $data['locale'],
                ]
            );
            $user->syncRoles('customer');
        }
    }
}
