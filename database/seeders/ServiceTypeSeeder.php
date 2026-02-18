<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use App\Models\ServiceTypeDuration;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $serviceTypes = [
            [
                'slug' => 'classic-massage',
                'name' => [
                    'uz' => 'Klassik massaj',
                    'ru' => 'Классический массаж',
                    'en' => 'Classic Massage',
                ],
                'description' => [
                    'uz' => 'An\'anaviy shved massaji texnikasi. Mushaklar va bo\'g\'imlarni bo\'shashtiradi, qon aylanishini yaxshilaydi.',
                    'ru' => 'Традиционная шведская техника массажа. Расслабляет мышцы и суставы, улучшает кровообращение.',
                    'en' => 'Traditional Swedish massage technique. Relaxes muscles and joints, improves blood circulation.',
                ],
                'status' => true,
                'durations' => [
                    ['duration' => 30, 'price' => 80000, 'is_default' => false],
                    ['duration' => 60, 'price' => 150000, 'is_default' => true],
                    ['duration' => 90, 'price' => 220000, 'is_default' => false],
                    ['duration' => 120, 'price' => 280000, 'is_default' => false],
                ],
            ],
            [
                'slug' => 'relaxing-massage',
                'name' => [
                    'uz' => 'Relaks massaj',
                    'ru' => 'Релакс массаж',
                    'en' => 'Relaxing Massage',
                ],
                'description' => [
                    'uz' => 'Stressni kamaytirish va to\'liq dam olish uchun yumshoq massaj. Aromaterapiya bilan birga.',
                    'ru' => 'Мягкий массаж для снятия стресса и полного расслабления. В сочетании с ароматерапией.',
                    'en' => 'Gentle massage for stress relief and complete relaxation. Combined with aromatherapy.',
                ],
                'status' => true,
                'durations' => [
                    ['duration' => 60, 'price' => 150000, 'is_default' => false],
                    ['duration' => 90, 'price' => 200000, 'is_default' => true],
                    ['duration' => 120, 'price' => 260000, 'is_default' => false],
                ],
            ],
            [
                'slug' => 'sport-massage',
                'name' => [
                    'uz' => 'Sport massaji',
                    'ru' => 'Спортивный массаж',
                    'en' => 'Sports Massage',
                ],
                'description' => [
                    'uz' => 'Sportchilar uchun maxsus massaj. Mushak zo\'riqishini tiklaydi, jarohatlardan tezroq tiklanishga yordam beradi.',
                    'ru' => 'Специальный массаж для спортсменов. Восстанавливает мышечное напряжение, помогает быстрее восстановиться после травм.',
                    'en' => 'Special massage for athletes. Restores muscle tension, helps recover faster from injuries.',
                ],
                'status' => true,
                'durations' => [
                    ['duration' => 45, 'price' => 140000, 'is_default' => false],
                    ['duration' => 60, 'price' => 180000, 'is_default' => true],
                    ['duration' => 90, 'price' => 260000, 'is_default' => false],
                ],
            ],
            [
                'slug' => 'thai-massage',
                'name' => [
                    'uz' => 'Tailand massaji',
                    'ru' => 'Тайский массаж',
                    'en' => 'Thai Massage',
                ],
                'description' => [
                    'uz' => 'Qadimiy sharqona texnika. Cho\'zilish va akupressura kombinatsiyasi. Energiya oqimini tiklaydi.',
                    'ru' => 'Древняя восточная техника. Комбинация растяжки и акупрессуры. Восстанавливает поток энергии.',
                    'en' => 'Ancient oriental technique. Combination of stretching and acupressure. Restores energy flow.',
                ],
                'status' => true,
                'durations' => [
                    ['duration' => 60, 'price' => 250000, 'is_default' => true],
                    ['duration' => 90, 'price' => 350000, 'is_default' => false],
                ],
            ],
            [
                'slug' => 'hot-stone-massage',
                'name' => [
                    'uz' => 'Issiq tosh massaji',
                    'ru' => 'Массаж горячими камнями',
                    'en' => 'Hot Stone Massage',
                ],
                'description' => [
                    'uz' => 'Issiq bazalt toshlari bilan massaj. Chuqur mushak bo\'shashishi va stressni bartaraf etish.',
                    'ru' => 'Массаж горячими базальтовыми камнями. Глубокое расслабление мышц и снятие стресса.',
                    'en' => 'Massage with hot basalt stones. Deep muscle relaxation and stress relief.',
                ],
                'status' => true,
                'durations' => [
                    ['duration' => 60, 'price' => 280000, 'is_default' => true],
                    ['duration' => 90, 'price' => 400000, 'is_default' => false],
                ],
            ],
            [
                'slug' => 'back-neck-massage',
                'name' => [
                    'uz' => 'Orqa va bo\'yin massaji',
                    'ru' => 'Массаж спины и шеи',
                    'en' => 'Back & Neck Massage',
                ],
                'description' => [
                    'uz' => 'Orqa va bo\'yin sohasiga e\'tibor qaratilgan massaj. Ofis xodimlari uchun ideal.',
                    'ru' => 'Массаж с акцентом на область спины и шеи. Идеально для офисных работников.',
                    'en' => 'Massage focused on back and neck area. Ideal for office workers.',
                ],
                'status' => true,
                'durations' => [
                    ['duration' => 30, 'price' => 70000, 'is_default' => false],
                    ['duration' => 45, 'price' => 100000, 'is_default' => true],
                    ['duration' => 60, 'price' => 120000, 'is_default' => false],
                ],
            ],
            [
                'slug' => 'foot-massage',
                'name' => [
                    'uz' => 'Oyoq massaji',
                    'ru' => 'Массаж ног',
                    'en' => 'Foot Massage',
                ],
                'description' => [
                    'uz' => 'Refleksologiya asosida oyoq massaji. Butun tanani tiklaydi va charchoqni yo\'qotadi.',
                    'ru' => 'Массаж ног на основе рефлексологии. Восстанавливает всё тело и снимает усталость.',
                    'en' => 'Reflexology-based foot massage. Restores the whole body and eliminates fatigue.',
                ],
                'status' => true,
                'durations' => [
                    ['duration' => 30, 'price' => 60000, 'is_default' => false],
                    ['duration' => 45, 'price' => 80000, 'is_default' => true],
                    ['duration' => 60, 'price' => 100000, 'is_default' => false],
                ],
            ],
            [
                'slug' => 'anti-cellulite-massage',
                'name' => [
                    'uz' => 'Anti-sellyulit massaj',
                    'ru' => 'Антицеллюлитный массаж',
                    'en' => 'Anti-Cellulite Massage',
                ],
                'description' => [
                    'uz' => 'Sellyulitga qarshi kurashish uchun maxsus texnika. Teri elastikligini oshiradi.',
                    'ru' => 'Специальная техника для борьбы с целлюлитом. Повышает эластичность кожи.',
                    'en' => 'Special technique to fight cellulite. Increases skin elasticity.',
                ],
                'status' => true,
                'durations' => [
                    ['duration' => 45, 'price' => 130000, 'is_default' => false],
                    ['duration' => 60, 'price' => 170000, 'is_default' => true],
                    ['duration' => 90, 'price' => 240000, 'is_default' => false],
                ],
            ],
        ];

        foreach ($serviceTypes as $data) {
            // Extract durations
            $durations = $data['durations'];
            unset($data['durations']);

            // Create or update service type
            $serviceType = ServiceType::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // Sync durations
            foreach ($durations as $index => $duration) {
                ServiceTypeDuration::updateOrCreate(
                    [
                        'service_type_id' => $serviceType->id,
                        'duration' => $duration['duration'],
                    ],
                    [
                        'price' => $duration['price'],
                        'is_default' => $duration['is_default'],
                        'status' => true,
                        'sort_order' => $index,
                    ]
                );
            }
        }
    }
}
