<?php

namespace Database\Seeders;

use App\Models\Oil;
use Illuminate\Database\Seeder;

class OilSeeder extends Seeder
{
    public function run(): void
    {
        $oils = [
            [
                'slug' => 'lavender-oil',
                'name' => [
                    'uz' => 'Lavanda moyi',
                    'ru' => 'Лавандовое масло',
                    'en' => 'Lavender Oil',
                ],
                'description' => [
                    'uz' => 'Tinchlantiruvchi xususiyatlarga ega. Stressni kamaytiradi va uyquni yaxshilaydi.',
                    'ru' => 'Обладает успокаивающими свойствами. Снижает стресс и улучшает сон.',
                    'en' => 'Has calming properties. Reduces stress and improves sleep.',
                ],
                'price' => 25000,
                'status' => true,
            ],
            [
                'slug' => 'coconut-oil',
                'name' => [
                    'uz' => 'Kokos moyi',
                    'ru' => 'Кокосовое масло',
                    'en' => 'Coconut Oil',
                ],
                'description' => [
                    'uz' => 'Terini namlaydi va oziqlantiradi. Tabiiy antibakterial xususiyatlarga ega.',
                    'ru' => 'Увлажняет и питает кожу. Обладает натуральными антибактериальными свойствами.',
                    'en' => 'Moisturizes and nourishes skin. Has natural antibacterial properties.',
                ],
                'price' => 20000,
                'status' => true,
            ],
            [
                'slug' => 'eucalyptus-oil',
                'name' => [
                    'uz' => 'Evkalipt moyi',
                    'ru' => 'Эвкалиптовое масло',
                    'en' => 'Eucalyptus Oil',
                ],
                'description' => [
                    'uz' => 'Nafas olishni yengillashtiradi. Mushak og\'riqlarini kamaytiradi va tetiklantiradi.',
                    'ru' => 'Облегчает дыхание. Уменьшает мышечные боли и бодрит.',
                    'en' => 'Eases breathing. Reduces muscle pain and invigorates.',
                ],
                'price' => 30000,
                'status' => true,
            ],
            [
                'slug' => 'almond-oil',
                'name' => [
                    'uz' => 'Bodom moyi',
                    'ru' => 'Миндальное масло',
                    'en' => 'Almond Oil',
                ],
                'description' => [
                    'uz' => 'Vitamin E ga boy. Terini yumshatadi va elastikligini oshiradi.',
                    'ru' => 'Богато витамином E. Смягчает кожу и повышает её эластичность.',
                    'en' => 'Rich in Vitamin E. Softens skin and increases its elasticity.',
                ],
                'price' => 22000,
                'status' => true,
            ],
            [
                'slug' => 'peppermint-oil',
                'name' => [
                    'uz' => 'Yalpiz moyi',
                    'ru' => 'Мятное масло',
                    'en' => 'Peppermint Oil',
                ],
                'description' => [
                    'uz' => 'Sovutuvchi effekt beradi. Bosh og\'rig\'ini kamaytiradi va energiya beradi.',
                    'ru' => 'Даёт охлаждающий эффект. Уменьшает головную боль и даёт энергию.',
                    'en' => 'Provides cooling effect. Reduces headaches and gives energy.',
                ],
                'price' => 28000,
                'status' => true,
            ],
            [
                'slug' => 'jojoba-oil',
                'name' => [
                    'uz' => 'Jojoba moyi',
                    'ru' => 'Масло жожоба',
                    'en' => 'Jojoba Oil',
                ],
                'description' => [
                    'uz' => 'Teri uchun eng yaxshi moylardan biri. Tez so\'riladi va yog\'li iz qoldirmaydi.',
                    'ru' => 'Одно из лучших масел для кожи. Быстро впитывается и не оставляет жирных следов.',
                    'en' => 'One of the best oils for skin. Absorbs quickly and leaves no greasy residue.',
                ],
                'price' => 35000,
                'status' => true,
            ],
            [
                'slug' => 'rosemary-oil',
                'name' => [
                    'uz' => 'Rozmarin moyi',
                    'ru' => 'Розмариновое масло',
                    'en' => 'Rosemary Oil',
                ],
                'description' => [
                    'uz' => 'Qon aylanishini yaxshilaydi. Xotirani mustahkamlaydi va diqqatni oshiradi.',
                    'ru' => 'Улучшает кровообращение. Укрепляет память и повышает концентрацию.',
                    'en' => 'Improves blood circulation. Strengthens memory and increases concentration.',
                ],
                'price' => 32000,
                'status' => true,
            ],
            [
                'slug' => 'argan-oil',
                'name' => [
                    'uz' => 'Argan moyi',
                    'ru' => 'Аргановое масло',
                    'en' => 'Argan Oil',
                ],
                'description' => [
                    'uz' => 'Marokashdan keltirilgan premium moy. Terini qayta tiklaydi va yoshartiradi.',
                    'ru' => 'Премиальное масло из Марокко. Восстанавливает и омолаживает кожу.',
                    'en' => 'Premium oil from Morocco. Restores and rejuvenates skin.',
                ],
                'price' => 45000,
                'status' => true,
            ],
        ];

        foreach ($oils as $oil) {
            Oil::firstOrCreate(
                ['slug' => $oil['slug']],
                $oil
            );
        }
    }
}
