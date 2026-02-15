<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'client_name' => ['uz' => 'Nodira Xasanova', 'ru' => 'Нодира Хасанова', 'en' => 'Nodira Khasanova'],
                'client_role' => ['uz' => 'Doimiy mijoz', 'ru' => 'Постоянный клиент', 'en' => 'Regular client'],
                'comment' => [
                    'uz' => 'Ajoyib xizmat! Dilnoza juda professional va g\'amxo\'r. Har safar massajdan keyin o\'zimni yangiday his qilaman.',
                    'ru' => 'Отличный сервис! Дильноза очень профессиональна и заботлива. После каждого массажа чувствую себя обновлённой.',
                    'en' => 'Amazing service! Dilnoza is very professional and caring. I feel renewed after every massage session.',
                ],
                'rating' => 5,
                'sort_order' => 1,
                'status' => true,
            ],
            [
                'client_name' => ['uz' => 'Sherzod Mirzayev', 'ru' => 'Шерзод Мирзаев', 'en' => 'Sherzod Mirzayev'],
                'client_role' => ['uz' => 'IT mutaxassis', 'ru' => 'IT специалист', 'en' => 'IT specialist'],
                'comment' => [
                    'uz' => 'Sport massaji orqali bel og\'rig\'imdan xalos bo\'ldim. Sardor usta juda zo\'r ishlaydi. Tavsiya qilaman!',
                    'ru' => 'Спортивный массаж помог избавиться от боли в спине. Сардор работает превосходно. Рекомендую!',
                    'en' => 'Sports massage helped me get rid of back pain. Sardor does an excellent job. Highly recommend!',
                ],
                'rating' => 5,
                'sort_order' => 2,
                'status' => true,
            ],
            [
                'client_name' => ['uz' => 'Kamola Tursunova', 'ru' => 'Камола Турсунова', 'en' => 'Kamola Tursunova'],
                'client_role' => ['uz' => 'Biznes egalik qiluvchi', 'ru' => 'Владелец бизнеса', 'en' => 'Business owner'],
                'comment' => [
                    'uz' => 'Uyga kelib xizmat ko\'rsatishlari juda qulay. Issiq tosh massaji ajoyib edi. Albatta yana bron qilaman.',
                    'ru' => 'Очень удобно, что приходят на дом. Массаж горячими камнями был восхитительным. Обязательно запишусь снова.',
                    'en' => 'So convenient that they come to your home. Hot stone massage was amazing. Will definitely book again.',
                ],
                'rating' => 5,
                'sort_order' => 3,
                'status' => true,
            ],
            [
                'client_name' => ['uz' => 'Bobur Abdullayev', 'ru' => 'Бобур Абдуллаев', 'en' => 'Bobur Abdullayev'],
                'client_role' => ['uz' => 'Sportchi', 'ru' => 'Спортсмен', 'en' => 'Athlete'],
                'comment' => [
                    'uz' => 'Har haftada sport massajiga yozilaman. Mashg\'ulotlardan keyin mushaklar tez tiklanadi. Narxi ham mos.',
                    'ru' => 'Записываюсь на спортивный массаж каждую неделю. Мышцы быстро восстанавливаются после тренировок. Цены адекватные.',
                    'en' => 'I book sports massage every week. Muscles recover quickly after workouts. Prices are reasonable too.',
                ],
                'rating' => 4,
                'sort_order' => 4,
                'status' => true,
            ],
            [
                'client_name' => ['uz' => 'Madina Rahimova', 'ru' => 'Мадина Рахимова', 'en' => 'Madina Rahimova'],
                'client_role' => ['uz' => 'Shifokor', 'ru' => 'Врач', 'en' => 'Doctor'],
                'comment' => [
                    'uz' => 'Professional yondashuv va sifatli xizmat. Aziza relaks massajni zo\'r qiladi. Stressdan charchaganlar uchun ideal.',
                    'ru' => 'Профессиональный подход и качественный сервис. Азиза отлично делает расслабляющий массаж. Идеально от стресса.',
                    'en' => 'Professional approach and quality service. Aziza does an excellent relaxing massage. Ideal for stress relief.',
                ],
                'rating' => 5,
                'sort_order' => 5,
                'status' => true,
            ],
            [
                'client_name' => ['uz' => 'Rustam Karimov', 'ru' => 'Рустам Каримов', 'en' => 'Rustam Karimov'],
                'client_role' => ['uz' => 'Haydovchi', 'ru' => 'Водитель', 'en' => 'Driver'],
                'comment' => [
                    'uz' => 'Bo\'yin va bel massaji menga juda yordam berdi. Uzoq vaqt haydashdan keyin bel og\'rig\'i yo\'qoldi.',
                    'ru' => 'Массаж шеи и спины очень помог. После долгого вождения боль в спине исчезла.',
                    'en' => 'Neck and back massage helped me a lot. Back pain disappeared after long hours of driving.',
                ],
                'rating' => 4,
                'sort_order' => 6,
                'status' => true,
            ],
        ];

        foreach ($testimonials as $data) {
            Testimonial::create($data);
        }
    }
}
