<?php

namespace Database\Seeders;

use App\Models\StandardItem;
use Illuminate\Database\Seeder;

class StandardItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'slug' => 'towel',
                'name' => [
                    'uz' => 'Sochiq',
                    'ru' => 'Полотенце',
                    'en' => 'Towel',
                ],
                'description' => [
                    'uz' => 'Yumshoq paxta sochiq',
                    'ru' => 'Мягкое хлопковое полотенце',
                    'en' => 'Soft cotton towel',
                ],
                'icon' => '🧴',
                'status' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'robe',
                'name' => [
                    'uz' => 'Xalat',
                    'ru' => 'Халат',
                    'en' => 'Robe',
                ],
                'description' => [
                    'uz' => 'Bir martalik yoki qayta ishlatiladigan xalat',
                    'ru' => 'Одноразовый или многоразовый халат',
                    'en' => 'Disposable or reusable robe',
                ],
                'icon' => '🥋',
                'status' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'slippers',
                'name' => [
                    'uz' => 'Shippak',
                    'ru' => 'Тапочки',
                    'en' => 'Slippers',
                ],
                'description' => [
                    'uz' => 'Bir martalik shippaklar',
                    'ru' => 'Одноразовые тапочки',
                    'en' => 'Disposable slippers',
                ],
                'icon' => '🩴',
                'status' => true,
                'sort_order' => 3,
            ],
            [
                'slug' => 'massage-table',
                'name' => [
                    'uz' => 'Massage stoli',
                    'ru' => 'Массажный стол',
                    'en' => 'Massage Table',
                ],
                'description' => [
                    'uz' => 'Professional ko\'chma massage stoli',
                    'ru' => 'Профессиональный переносной массажный стол',
                    'en' => 'Professional portable massage table',
                ],
                'icon' => '🛏️',
                'status' => true,
                'sort_order' => 4,
            ],
            [
                'slug' => 'sheets',
                'name' => [
                    'uz' => 'Choyshab',
                    'ru' => 'Простыня',
                    'en' => 'Sheets',
                ],
                'description' => [
                    'uz' => 'Toza bir martalik choyshablar',
                    'ru' => 'Чистые одноразовые простыни',
                    'en' => 'Clean disposable sheets',
                ],
                'icon' => '🧻',
                'status' => true,
                'sort_order' => 5,
            ],
            [
                'slug' => 'pillow',
                'name' => [
                    'uz' => 'Yostiq',
                    'ru' => 'Подушка',
                    'en' => 'Pillow',
                ],
                'description' => [
                    'uz' => 'Bosh va tana uchun maxsus yostiqlar',
                    'ru' => 'Специальные подушки для головы и тела',
                    'en' => 'Special pillows for head and body',
                ],
                'icon' => '🛋️',
                'status' => true,
                'sort_order' => 6,
            ],
            [
                'slug' => 'candles',
                'name' => [
                    'uz' => 'Sham',
                    'ru' => 'Свечи',
                    'en' => 'Candles',
                ],
                'description' => [
                    'uz' => 'Aromaterapiya shamlari',
                    'ru' => 'Ароматерапевтические свечи',
                    'en' => 'Aromatherapy candles',
                ],
                'icon' => '🕯️',
                'status' => true,
                'sort_order' => 7,
            ],
            [
                'slug' => 'music-speaker',
                'name' => [
                    'uz' => 'Musiqa kolonkasi',
                    'ru' => 'Музыкальная колонка',
                    'en' => 'Music Speaker',
                ],
                'description' => [
                    'uz' => 'Tinchlantiruvchi musiqa uchun portativ kolonka',
                    'ru' => 'Портативная колонка для расслабляющей музыки',
                    'en' => 'Portable speaker for relaxing music',
                ],
                'icon' => '🔊',
                'status' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($items as $item) {
            StandardItem::firstOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }
    }
}
