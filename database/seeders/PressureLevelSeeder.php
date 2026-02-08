<?php

namespace Database\Seeders;

use App\Models\PressureLevel;
use Illuminate\Database\Seeder;

class PressureLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pressureLevels = [
            [
                'slug' => 'light',
                'name' => [
                    'uz' => 'Yumshoq (Hafif)',
                    'ru' => 'Мягкое давление',
                    'en' => 'Light Pressure',
                ],
                'description' => [
                    'uz' => 'Relaks va tinchlik uchun. O\'ziga ishonch yo\'q bilan boshlash uchun ideal.',
                    'ru' => 'Для расслабления и спокойствия. Идеально для начинающих.',
                    'en' => 'For relaxation and calmness. Ideal for beginners.',
                ],
                'sort_order' => 1,
                'status' => true,
            ],
            [
                'slug' => 'medium',
                'name' => [
                    'uz' => 'O\'rtacha (O\'rta)',
                    'ru' => 'Среднее давление',
                    'en' => 'Medium Pressure',
                ],
                'description' => [
                    'uz' => 'Balanslashtirilgan massaj. Ko\'pchilik mijozlar uchun to\'g\'ri.',
                    'ru' => 'Сбалансированный массаж. Подходит большинству клиентов.',
                    'en' => 'Balanced massage. Suitable for most clients.',
                ],
                'sort_order' => 2,
                'status' => true,
            ],
            [
                'slug' => 'heavy',
                'name' => [
                    'uz' => 'Kuchli (Og\'ir)',
                    'ru' => 'Сильное давление',
                    'en' => 'Heavy Pressure',
                ],
                'description' => [
                    'uz' => 'Chuqur massaj. Murakkab mushak masalalarni yechish uchun.',
                    'ru' => 'Глубокий массаж. Для решения серьезных проблем с мышцами.',
                    'en' => 'Deep tissue massage. For serious muscle issues.',
                ],
                'sort_order' => 3,
                'status' => true,
            ],
        ];

        foreach ($pressureLevels as $level) {
            PressureLevel::updateOrCreate(
                ['slug' => $level['slug']],
                $level
            );
        }
    }
}
