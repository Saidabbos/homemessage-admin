<?php

namespace Tests\Feature;

use App\Models\Master;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterSlotsApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test GET /api/masters/{master}/slots requires date parameter
     */
    public function test_slots_endpoint_requires_date_parameter(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'status' => true,
        ]);

        $response = $this->getJson("/api/masters/{$master->id}/slots");

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');
    }

    /**
     * Test GET /api/masters/{master}/slots requires duration parameter
     */
    public function test_slots_endpoint_requires_duration_parameter(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'status' => true,
        ]);

        $date = now()->addDays(3)->toDateString();

        $response = $this->getJson("/api/masters/{$master->id}/slots?date={$date}");

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('duration');
    }

    /**
     * Test GET /api/masters/{master}/slots validates date is future
     */
    public function test_slots_endpoint_validates_date_is_future(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'status' => true,
        ]);

        $pastDate = now()->subDays(1)->toDateString();

        $response = $this->getJson("/api/masters/{$master->id}/slots?date={$pastDate}&duration=60");

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');
    }

    /**
     * Test GET /api/masters/{master}/slots validates duration is valid
     */
    public function test_slots_endpoint_validates_duration_is_valid(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'status' => true,
        ]);

        $date = now()->addDays(3)->toDateString();

        $response = $this->getJson("/api/masters/{$master->id}/slots?date={$date}&duration=45");

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('duration');
    }

    /**
     * Test GET /api/masters/{master}/slots returns 404 for inactive master
     */
    public function test_slots_endpoint_returns_404_for_inactive_master(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'status' => false,
        ]);

        $date = now()->addDays(3)->toDateString();

        $response = $this->getJson("/api/masters/{$master->id}/slots?date={$date}&duration=60");

        $response->assertStatus(404);
    }

    /**
     * Test GET /api/masters/{master}/slots returns successful response
     */
    public function test_slots_endpoint_returns_successful_response(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'shift_start' => '08:00',
            'shift_end' => '22:00',
            'status' => true,
        ]);

        $date = now()->addDays(3)->toDateString();

        $response = $this->getJson("/api/masters/{$master->id}/slots?date={$date}&duration=60&people_count=1");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'date',
                'master' => [
                    'id',
                    'name',
                    'shift_start',
                    'shift_end',
                ],
                'slots',
                'slots_count',
            ],
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals($date, $response->json('data.date'));
        $this->assertEquals($master->id, $response->json('data.master.id'));
    }

    /**
     * Test GET /api/masters/{master}/slots includes master info
     */
    public function test_slots_endpoint_includes_master_info(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'shift_start' => '08:00',
            'shift_end' => '22:00',
            'status' => true,
        ]);

        $date = now()->addDays(3)->toDateString();

        $response = $this->getJson("/api/masters/{$master->id}/slots?date={$date}&duration=60");

        $response->assertJson([
            'success' => true,
            'data' => [
                'master' => [
                    'id' => $master->id,
                    'name' => 'John Doe',
                    'shift_start' => '08:00',
                    'shift_end' => '22:00',
                ],
            ],
        ]);
    }

    /**
     * Test GET /api/masters/{master}/slots accepts people_count parameter
     */
    public function test_slots_endpoint_accepts_people_count_parameter(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'shift_start' => '08:00',
            'shift_end' => '22:00',
            'status' => true,
        ]);

        $date = now()->addDays(3)->toDateString();

        $response = $this->getJson("/api/masters/{$master->id}/slots?date={$date}&duration=60&people_count=2");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * Test GET /api/masters/{master}/slots returns slots array
     */
    public function test_slots_endpoint_returns_slots_array(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'shift_start' => '08:00',
            'shift_end' => '22:00',
            'status' => true,
        ]);

        $date = now()->addDays(3)->toDateString();

        $response = $this->getJson("/api/masters/{$master->id}/slots?date={$date}&duration=60");

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data.slots'));
    }
}
