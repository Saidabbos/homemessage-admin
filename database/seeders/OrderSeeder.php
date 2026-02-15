<?php

namespace Database\Seeders;

use App\Models\Master;
use App\Models\Oil;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\Payment;
use App\Models\QualityControl;
use App\Models\ServiceType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::role('customer')->get();
        $masters = Master::with('serviceTypes.activeDurations')->where('status', true)->get();
        $oils = Oil::where('status', true)->get();
        $dispatcher = User::role('dispatcher')->first();

        if ($customers->isEmpty() || $masters->isEmpty()) {
            $this->command->warn('Customers or Masters not found. Skipping OrderSeeder.');
            return;
        }

        $today = Carbon::today();
        $orderIndex = 1;

        // ── 1. COMPLETED orders (past dates) ──
        for ($i = 0; $i < 8; $i++) {
            $customer = $customers->random();
            $master = $masters->random();
            $serviceType = $master->serviceTypes->random();
            $duration = $serviceType->activeDurations->random();
            $bookingDate = $today->copy()->subDays(rand(3, 30));
            $startHour = rand(9, 17);

            $order = Order::create([
                'order_number' => 'HM-' . $bookingDate->format('Ymd') . '-' . str_pad($orderIndex++, 3, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'master_id' => $master->id,
                'service_type_id' => $serviceType->id,
                'duration_id' => $duration->id,
                'oil_id' => $oils->isNotEmpty() ? $oils->random()->id : null,
                'people_count' => 1,
                'pressure_level' => collect(['soft', 'medium', 'hard'])->random(),
                'booking_date' => $bookingDate,
                'arrival_window_start' => sprintf('%02d:00:00', $startHour),
                'arrival_window_end' => sprintf('%02d:00:00', $startHour + 1),
                'status' => Order::STATUS_COMPLETED,
                'payment_status' => Order::PAY_PAID,
                'total_amount' => $duration->price,
                'paid_at' => $bookingDate->copy()->subDay(),
                'address' => $this->randomAddress(),
                'contact_phone' => $customer->phone,
                'entrance' => (string) rand(1, 4),
                'floor' => (string) rand(1, 16),
                'landmark' => rand(0, 1) ? 'Hovli ichida' : 'Ko\'cha chetida',
                'confirmed_by' => $dispatcher?->id,
                'confirmed_at' => $bookingDate->copy()->subDays(2),
                'ready_sent_at' => $bookingDate->copy()->subDay(),
                'work_order_sent_at' => $bookingDate->copy()->subDay(),
                'qa_completed' => true,
                'qa_overall_rating' => rand(4, 5),
                'qa_punctuality_rating' => rand(3, 5),
                'qa_professionalism_rating' => rand(4, 5),
                'qa_feedback' => $this->randomFeedback(),
                'qa_completed_at' => $bookingDate->copy()->addDay(),
                'qa_completed_by' => $dispatcher?->id,
            ]);

            // Payment record
            Payment::create([
                'order_id' => $order->id,
                'provider' => collect(['payme', 'click'])->random(),
                'transaction_id' => Payment::generateTransactionId(),
                'amount' => $duration->price,
                'currency' => 'UZS',
                'status' => Payment::STATUS_PAID,
                'paid_at' => $bookingDate->copy()->subDay(),
            ]);

            // QA record
            QualityControl::create([
                'order_id' => $order->id,
                'checked_by' => $dispatcher?->id,
                'rating_overall' => rand(4, 5),
                'rating_punctuality' => rand(3, 5),
                'rating_quality' => rand(4, 5),
                'rating_cleanliness' => rand(4, 5),
                'rating_communication' => rand(4, 5),
                'has_all_items' => true,
                'arrived_on_time' => (bool) rand(0, 1),
                'proper_uniform' => true,
                'polite_behavior' => true,
                'customer_feedback' => $this->randomFeedback(),
                'status' => 'completed',
                'completed_at' => $bookingDate->copy()->addDay(),
            ]);

            // Order log
            OrderLog::log($order, OrderLog::ACTION_CREATED, null, Order::STATUS_NEW, 'Buyurtma yaratildi');
            OrderLog::log($order, OrderLog::ACTION_STATUS_CHANGED, Order::STATUS_NEW, Order::STATUS_CONFIRMED, 'Dispatcher tasdiqladi');
            OrderLog::log($order, OrderLog::ACTION_PAYMENT_RECEIVED, null, 'PAID', 'To\'lov qabul qilindi');
            OrderLog::log($order, OrderLog::ACTION_STATUS_CHANGED, Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED, 'Xizmat bajarildi');
        }

        // ── 2. CONFIRMED orders (upcoming) ──
        for ($i = 0; $i < 3; $i++) {
            $customer = $customers->random();
            $master = $masters->random();
            $serviceType = $master->serviceTypes->random();
            $duration = $serviceType->activeDurations->random();
            $bookingDate = $today->copy()->addDays(rand(1, 5));
            $startHour = rand(10, 16);

            $order = Order::create([
                'order_number' => 'HM-' . $bookingDate->format('Ymd') . '-' . str_pad($orderIndex++, 3, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'master_id' => $master->id,
                'service_type_id' => $serviceType->id,
                'duration_id' => $duration->id,
                'oil_id' => $oils->isNotEmpty() ? $oils->random()->id : null,
                'people_count' => 1,
                'pressure_level' => collect(['soft', 'medium'])->random(),
                'booking_date' => $bookingDate,
                'arrival_window_start' => sprintf('%02d:00:00', $startHour),
                'arrival_window_end' => sprintf('%02d:00:00', $startHour + 1),
                'status' => Order::STATUS_CONFIRMED,
                'payment_status' => Order::PAY_PAID,
                'total_amount' => $duration->price,
                'paid_at' => now(),
                'address' => $this->randomAddress(),
                'contact_phone' => $customer->phone,
                'entrance' => (string) rand(1, 4),
                'floor' => (string) rand(1, 16),
                'confirmed_by' => $dispatcher?->id,
                'confirmed_at' => now(),
            ]);

            Payment::create([
                'order_id' => $order->id,
                'provider' => collect(['payme', 'click'])->random(),
                'transaction_id' => Payment::generateTransactionId(),
                'amount' => $duration->price,
                'currency' => 'UZS',
                'status' => Payment::STATUS_PAID,
                'paid_at' => now(),
            ]);

            OrderLog::log($order, OrderLog::ACTION_CREATED, null, Order::STATUS_NEW, 'Buyurtma yaratildi');
            OrderLog::log($order, OrderLog::ACTION_STATUS_CHANGED, Order::STATUS_NEW, Order::STATUS_CONFIRMED, 'Dispatcher tasdiqladi');
            OrderLog::log($order, OrderLog::ACTION_PAYMENT_RECEIVED, null, 'PAID', 'To\'lov qabul qilindi');
        }

        // ── 3. NEW orders (just created, not yet confirmed) ──
        for ($i = 0; $i < 2; $i++) {
            $customer = $customers->random();
            $master = $masters->random();
            $serviceType = $master->serviceTypes->random();
            $duration = $serviceType->activeDurations->random();
            $bookingDate = $today->copy()->addDays(rand(2, 7));
            $startHour = rand(10, 18);

            $order = Order::create([
                'order_number' => 'HM-' . $bookingDate->format('Ymd') . '-' . str_pad($orderIndex++, 3, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'master_id' => $master->id,
                'service_type_id' => $serviceType->id,
                'duration_id' => $duration->id,
                'people_count' => 1,
                'pressure_level' => 'medium',
                'booking_date' => $bookingDate,
                'arrival_window_start' => sprintf('%02d:00:00', $startHour),
                'arrival_window_end' => sprintf('%02d:00:00', $startHour + 1),
                'status' => Order::STATUS_NEW,
                'payment_status' => Order::PAY_NOT_PAID,
                'total_amount' => $duration->price,
                'address' => $this->randomAddress(),
                'contact_phone' => $customer->phone,
            ]);

            OrderLog::log($order, OrderLog::ACTION_CREATED, null, Order::STATUS_NEW, 'Buyurtma yaratildi');
        }

        // ── 4. CANCELLED orders ──
        for ($i = 0; $i < 2; $i++) {
            $customer = $customers->random();
            $master = $masters->random();
            $serviceType = $master->serviceTypes->random();
            $duration = $serviceType->activeDurations->random();
            $bookingDate = $today->copy()->subDays(rand(1, 14));
            $startHour = rand(10, 16);

            $order = Order::create([
                'order_number' => 'HM-' . $bookingDate->format('Ymd') . '-' . str_pad($orderIndex++, 3, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'master_id' => $master->id,
                'service_type_id' => $serviceType->id,
                'duration_id' => $duration->id,
                'people_count' => 1,
                'pressure_level' => 'soft',
                'booking_date' => $bookingDate,
                'arrival_window_start' => sprintf('%02d:00:00', $startHour),
                'arrival_window_end' => sprintf('%02d:00:00', $startHour + 1),
                'status' => Order::STATUS_CANCELLED,
                'payment_status' => Order::PAY_NOT_PAID,
                'total_amount' => $duration->price,
                'address' => $this->randomAddress(),
                'contact_phone' => $customer->phone,
                'cancel_reason' => collect(['Vaqt o\'zgardi', 'Boshqa master tanladim', 'Kerak emas'])->random(),
                'cancelled_by' => $customer->id,
                'cancelled_at' => $bookingDate->copy()->subDay(),
            ]);

            OrderLog::log($order, OrderLog::ACTION_CREATED, null, Order::STATUS_NEW, 'Buyurtma yaratildi');
            OrderLog::log($order, OrderLog::ACTION_CANCELLED, Order::STATUS_NEW, Order::STATUS_CANCELLED, 'Mijoz bekor qildi');
        }

        // ── 5. Group booking (2 masters for same customer) ──
        $customer = $customers->first();
        $groupId = 'GRP-' . now()->format('Ymd') . '-001';
        $bookingDate = $today->copy()->addDays(3);

        foreach ($masters->take(2) as $idx => $master) {
            $serviceType = $master->serviceTypes->first();
            $duration = $serviceType->activeDurations->first();

            $order = Order::create([
                'order_number' => 'HM-' . $bookingDate->format('Ymd') . '-' . str_pad($orderIndex++, 3, '0', STR_PAD_LEFT),
                'booking_group_id' => $groupId,
                'customer_id' => $customer->id,
                'master_id' => $master->id,
                'service_type_id' => $serviceType->id,
                'duration_id' => $duration->id,
                'people_count' => 2,
                'pressure_level' => 'medium',
                'booking_date' => $bookingDate,
                'arrival_window_start' => '14:00:00',
                'arrival_window_end' => '15:00:00',
                'status' => Order::STATUS_CONFIRMED,
                'payment_status' => Order::PAY_PAID,
                'total_amount' => $duration->price,
                'paid_at' => now(),
                'address' => 'Tashkent, Yunusobod tumani, Amir Temur ko\'chasi 15, 3-kvartira',
                'contact_phone' => $customer->phone,
                'confirmed_by' => $dispatcher?->id,
                'confirmed_at' => now(),
            ]);

            Payment::create([
                'order_id' => $order->id,
                'provider' => 'payme',
                'transaction_id' => Payment::generateTransactionId(),
                'amount' => $duration->price,
                'currency' => 'UZS',
                'status' => Payment::STATUS_PAID,
                'paid_at' => now(),
            ]);

            OrderLog::log($order, OrderLog::ACTION_CREATED, null, Order::STATUS_NEW, 'Guruh buyurtma yaratildi');
        }
    }

    private function randomAddress(): string
    {
        $addresses = [
            'Tashkent, Chilonzor tumani, 9-mavze, 12-uy, 45-kvartira',
            'Tashkent, Mirzo Ulug\'bek tumani, Buyuk Ipak Yo\'li 108',
            'Tashkent, Yakkasaroy tumani, Shota Rustaveli 15',
            'Tashkent, Sergeli tumani, S.Mashrab ko\'chasi 22, 8-uy',
            'Tashkent, Olmazor tumani, Beruniy ko\'chasi 77',
            'Tashkent, Mirabad tumani, Afrosiyob ko\'chasi 3, 14-kvartira',
            'Tashkent, Shayhontohur tumani, Navoiy ko\'chasi 30',
            'Tashkent, Uchtepa tumani, Labzak ko\'chasi 5, 2-uy',
        ];

        return collect($addresses)->random();
    }

    private function randomFeedback(): string
    {
        $feedbacks = [
            'Juda yaxshi xizmat, rahmat!',
            'Master professional, tavsiya qilaman',
            'Massaj ajoyib edi, yana kelaman',
            'Vaqtida keldi, sifatli ishladi',
            'Zo\'r tajriba, rahmat katta!',
            'Juda yoqdi, keyingi safar ham shu masterga yozilaman',
            'Yaxshi, lekin biroz kech keldi',
            'A\'lo darajada! 5 ball',
        ];

        return collect($feedbacks)->random();
    }
}
