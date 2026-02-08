<?php

namespace Database\Seeders;

use App\Models\Master;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $serviceTypes = ServiceType::pluck('id')->toArray();

        $masters = [
            [
                'first_name' => 'Dilnoza',
                'last_name' => 'Karimova',
                'phone' => '+998901234567',
                'email' => 'dilnoza@homemessage.uz',
                'password' => 'password',
                'gender' => 'female',
                'birth_date' => '1992-05-15',
                'experience_years' => 8,
                'shift_start' => '09:00',
                'shift_end' => '21:00',
                'pressure_levels' => ['light', 'medium', 'heavy'],
                'bio' => [
                    'uz' => 'Professional massajchi. Klassik va relaks massaj bo\'yicha mutaxassis. 8 yillik tajriba.',
                    'ru' => 'Профессиональный массажист. Специалист по классическому и релакс массажу. 8 лет опыта.',
                    'en' => 'Professional masseuse. Specialist in classic and relaxing massage. 8 years of experience.',
                ],
                'services' => [1, 2, 6], // Classic, Relax, Back & Neck
            ],
            [
                'first_name' => 'Aziza',
                'last_name' => 'Rustamova',
                'phone' => '+998901234568',
                'email' => 'aziza@homemessage.uz',
                'password' => 'password',
                'gender' => 'female',
                'birth_date' => '1988-11-22',
                'experience_years' => 12,
                'shift_start' => '08:00',
                'shift_end' => '22:00',
                'pressure_levels' => ['light', 'medium', 'heavy'],
                'bio' => [
                    'uz' => 'Tailand massaji bo\'yicha sertifikatlangan mutaxassis. Sharq texnikalarini mukammal biladi.',
                    'ru' => 'Сертифицированный специалист по тайскому массажу. Отлично владеет восточными техниками.',
                    'en' => 'Certified Thai massage specialist. Expert in oriental techniques.',
                ],
                'services' => [4, 5, 7], // Thai, Hot Stone, Foot
            ],
            [
                'first_name' => 'Jamshid',
                'last_name' => 'Aliyev',
                'phone' => '+998901234569',
                'email' => 'jamshid@homemessage.uz',
                'password' => 'password',
                'gender' => 'male',
                'birth_date' => '1990-03-08',
                'experience_years' => 6,
                'shift_start' => '10:00',
                'shift_end' => '20:00',
                'pressure_levels' => ['medium', 'heavy'],
                'bio' => [
                    'uz' => 'Sport massaji mutaxassisi. Professional sportchilar bilan ishlagan tajribaga ega.',
                    'ru' => 'Специалист по спортивному массажу. Имеет опыт работы с профессиональными спортсменами.',
                    'en' => 'Sports massage specialist. Has experience working with professional athletes.',
                ],
                'services' => [3, 6], // Sport, Back & Neck
            ],
            [
                'first_name' => 'Malika',
                'last_name' => 'Saidova',
                'phone' => '+998901234570',
                'email' => 'malika@homemessage.uz',
                'password' => 'password',
                'gender' => 'female',
                'birth_date' => '1995-07-30',
                'experience_years' => 4,
                'shift_start' => '11:00',
                'shift_end' => '19:00',
                'pressure_levels' => ['light', 'medium'],
                'bio' => [
                    'uz' => 'Anti-sellyulit va ariqlashtiruvchi massaj mutaxassisi. Zamonaviy texnikalarni qo\'llaydi.',
                    'ru' => 'Специалист по антицеллюлитному и моделирующему массажу. Применяет современные техники.',
                    'en' => 'Anti-cellulite and body sculpting massage specialist. Uses modern techniques.',
                ],
                'services' => [8, 2], // Anti-cellulite, Relax
            ],
            [
                'first_name' => 'Sardor',
                'last_name' => 'Mahmudov',
                'phone' => '+998901234571',
                'email' => 'sardor@homemessage.uz',
                'password' => 'password',
                'gender' => 'male',
                'birth_date' => '1985-12-10',
                'experience_years' => 15,
                'shift_start' => '08:00',
                'shift_end' => '22:00',
                'pressure_levels' => ['light', 'medium', 'heavy'],
                'bio' => [
                    'uz' => 'Katta tajribaga ega professional. Barcha turdagi massajlarni bajaradi. VIP mijozlar bilan ishlaydi.',
                    'ru' => 'Профессионал с большим опытом. Выполняет все виды массажа. Работает с VIP клиентами.',
                    'en' => 'Experienced professional. Performs all types of massage. Works with VIP clients.',
                ],
                'services' => [1, 2, 3, 4, 5], // Classic, Relax, Sport, Thai, Hot Stone
            ],
            [
                'first_name' => 'Gulnora',
                'last_name' => 'Tosheva',
                'phone' => '+998901234572',
                'email' => 'gulnora@homemessage.uz',
                'password' => 'password',
                'gender' => 'female',
                'birth_date' => '1993-09-18',
                'experience_years' => 5,
                'shift_start' => '09:30',
                'shift_end' => '20:30',
                'pressure_levels' => ['light', 'medium'],
                'bio' => [
                    'uz' => 'Relaksatsiya va stress bartaraf etish bo\'yicha mutaxassis. Aromaterapiya bilan ishlaydi.',
                    'ru' => 'Специалист по релаксации и снятию стресса. Работает с ароматерапией.',
                    'en' => 'Relaxation and stress relief specialist. Works with aromatherapy.',
                ],
                'services' => [2, 7], // Relax, Foot
            ],
        ];

        foreach ($masters as $masterData) {
            // Check if user already exists
            $user = User::where('email', $masterData['email'])->first();

            if (!$user) {
                // Create user account
                $user = User::create([
                    'name' => $masterData['first_name'] . ' ' . $masterData['last_name'],
                    'email' => $masterData['email'],
                    'password' => $masterData['password'],
                ]);
                $user->assignRole('master');
            }

            // Create master if not exists
            $master = Master::where('user_id', $user->id)->first();

            if (!$master) {
                $master = Master::create([
                    'user_id' => $user->id,
                    'first_name' => $masterData['first_name'],
                    'last_name' => $masterData['last_name'],
                    'phone' => $masterData['phone'],
                    'email' => $masterData['email'],
                    'gender' => $masterData['gender'],
                    'birth_date' => $masterData['birth_date'],
                    'experience_years' => $masterData['experience_years'],
                    'shift_start' => $masterData['shift_start'] ?? '08:00',
                    'shift_end' => $masterData['shift_end'] ?? '22:00',
                    'pressure_levels' => $masterData['pressure_levels'] ?? ['light', 'medium', 'heavy'],
                    'bio' => $masterData['bio'],
                    'status' => true,
                ]);

                // Attach services (filter to only existing IDs)
                $validServices = array_intersect($masterData['services'], $serviceTypes);
                if (!empty($validServices)) {
                    $master->serviceTypes()->attach($validServices);
                }
            }
        }
    }
}
