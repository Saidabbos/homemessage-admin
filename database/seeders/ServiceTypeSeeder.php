<?php

namespace Database\Seeders;

use App\Models\ServiceType;
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
                'duration' => 60,
                'price' => 150000,
                'status' => true,
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
                'duration' => 90,
                'price' => 200000,
                'status' => true,
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
                'duration' => 60,
                'price' => 180000,
                'status' => true,
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
                'duration' => 60,
                'price' => 250000,
                'status' => true,
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
                'duration' => 60,
                'price' => 280000,
                'status' => true,
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
                'duration' => 60,
                'price' => 120000,
                'status' => true,
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
                'duration' => 60,
                'price' => 100000,
                'status' => true,
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
                'duration' => 60,
                'price' => 170000,
                'status' => true,
            ],
        ];

        foreach ($serviceTypes as $serviceType) {
            ServiceType::firstOrCreate(
                ['slug' => $serviceType['slug']],
                $serviceType
            );
        }
    }
}
