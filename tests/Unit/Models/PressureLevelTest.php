<?php

namespace Tests\Unit\Models;

use App\Models\Master;
use App\Models\PressureLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PressureLevelTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test that PressureLevel can be created with translatable attributes
     */
    public function test_pressure_level_can_be_created_with_translations(): void
    {
        $pressureLevel = PressureLevel::create([
            'slug' => 'light',
            'name' => [
                'uz' => 'Yumshoq',
                'ru' => 'Мягкое',
                'en' => 'Light',
            ],
            'description' => [
                'uz' => 'Yumshoq bosim',
                'ru' => 'Мягкое давление',
                'en' => 'Light pressure',
            ],
            'sort_order' => 1,
            'status' => true,
        ]);

        $this->assertNotNull($pressureLevel->id);
        $this->assertEquals('light', $pressureLevel->slug);
        $this->assertEquals('Yumshoq', $pressureLevel->getTranslation('name', 'uz'));
        $this->assertEquals('Мягкое', $pressureLevel->getTranslation('name', 'ru'));
        $this->assertEquals('Light', $pressureLevel->getTranslation('name', 'en'));
    }

    /**
     * Test that PressureLevel can retrieve translations
     */
    public function test_pressure_level_can_get_translation(): void
    {
        $pressureLevel = PressureLevel::create([
            'slug' => 'medium',
            'name' => [
                'uz' => 'O\'rtacha',
                'ru' => 'Среднее',
                'en' => 'Medium',
            ],
            'status' => true,
        ]);

        $this->assertEquals('O\'rtacha', $pressureLevel->getTranslation('name', 'uz'));
    }

    /**
     * Test active scope filters only active pressure levels
     */
    public function test_active_scope_filters_inactive_pressure_levels(): void
    {
        PressureLevel::create([
            'slug' => 'active',
            'name' => ['uz' => 'Faol'],
            'status' => true,
        ]);

        PressureLevel::create([
            'slug' => 'inactive',
            'name' => ['uz' => 'Nofaol'],
            'status' => false,
        ]);

        $active = PressureLevel::active()->get();

        $this->assertEquals(1, $active->count());
        $this->assertEquals('active', $active->first()->slug);
    }

    /**
     * Test ordered scope orders by sort_order then id
     */
    public function test_ordered_scope_orders_by_sort_order(): void
    {
        PressureLevel::create([
            'slug' => 'third',
            'name' => ['uz' => 'Uchinchi'],
            'sort_order' => 3,
            'status' => true,
        ]);

        PressureLevel::create([
            'slug' => 'first',
            'name' => ['uz' => 'Birinchi'],
            'sort_order' => 1,
            'status' => true,
        ]);

        PressureLevel::create([
            'slug' => 'second',
            'name' => ['uz' => 'Ikkinchi'],
            'sort_order' => 2,
            'status' => true,
        ]);

        $ordered = PressureLevel::ordered()->get();

        $this->assertEquals('first', $ordered[0]->slug);
        $this->assertEquals('second', $ordered[1]->slug);
        $this->assertEquals('third', $ordered[2]->slug);
    }

    /**
     * Test PressureLevel has many-to-many relationship with Masters
     */
    public function test_pressure_level_has_many_masters(): void
    {
        $pressureLevel = PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'status' => true,
        ]);

        $master1 = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'status' => true,
        ]);

        $master2 = Master::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '+998901234568',
            'gender' => 'female',
            'status' => true,
        ]);

        $pressureLevel->masters()->attach([$master1->id, $master2->id]);

        $this->assertEquals(2, $pressureLevel->masters()->count());
        $this->assertTrue($pressureLevel->masters()->where('masters.id', $master1->id)->exists());
        $this->assertTrue($pressureLevel->masters()->where('masters.id', $master2->id)->exists());
    }

    /**
     * Test PressureLevel can be fillable
     */
    public function test_pressure_level_fillable_attributes(): void
    {
        $attributes = [
            'slug' => 'heavy',
            'name' => ['uz' => 'Kuchli'],
            'description' => ['uz' => 'Kuchli bosim'],
            'sort_order' => 3,
            'status' => true,
        ];

        $pressureLevel = PressureLevel::create($attributes);

        $this->assertEquals('heavy', $pressureLevel->slug);
        $this->assertEquals(3, $pressureLevel->sort_order);
        $this->assertTrue($pressureLevel->status);
    }
}
