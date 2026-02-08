<?php

namespace Tests\Unit\Repositories;

use App\Models\PressureLevel;
use App\Repositories\PressureLevelRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PressureLevelRepositoryTest extends TestCase
{
    use RefreshDatabase;
    private PressureLevelRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PressureLevelRepository();
    }

    /**
     * Test getActive returns only active pressure levels
     */
    public function test_get_active_returns_only_active_pressure_levels(): void
    {
        PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'status' => true,
            'sort_order' => 1,
        ]);

        PressureLevel::create([
            'slug' => 'inactive',
            'name' => ['uz' => 'Nofaol'],
            'status' => false,
            'sort_order' => 2,
        ]);

        $active = $this->repository->getActive();

        $this->assertEquals(1, $active->count());
        $this->assertEquals('light', $active->first()->slug);
    }

    /**
     * Test getActive orders by sort_order
     */
    public function test_get_active_orders_by_sort_order(): void
    {
        PressureLevel::create([
            'slug' => 'heavy',
            'name' => ['uz' => 'Kuchli'],
            'status' => true,
            'sort_order' => 3,
        ]);

        PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'status' => true,
            'sort_order' => 1,
        ]);

        PressureLevel::create([
            'slug' => 'medium',
            'name' => ['uz' => 'O\'rtacha'],
            'status' => true,
            'sort_order' => 2,
        ]);

        $active = $this->repository->getActive();

        $this->assertEquals(3, $active->count());
        $this->assertEquals('light', $active[0]->slug);
        $this->assertEquals('medium', $active[1]->slug);
        $this->assertEquals('heavy', $active[2]->slug);
    }

    /**
     * Test getFilteredPaginated with search filter
     */
    public function test_get_filtered_paginated_with_search(): void
    {
        PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq', 'ru' => 'Мягкое'],
            'status' => true,
            'sort_order' => 1,
        ]);

        PressureLevel::create([
            'slug' => 'heavy',
            'name' => ['uz' => 'Kuchli', 'ru' => 'Сильное'],
            'status' => true,
            'sort_order' => 2,
        ]);

        $result = $this->repository->getFilteredPaginated(['search' => 'light']);

        $this->assertEquals(1, $result->count());
        $this->assertEquals('light', $result->items()[0]->slug);
    }

    /**
     * Test getFilteredPaginated with status filter
     */
    public function test_get_filtered_paginated_with_status_filter(): void
    {
        PressureLevel::create([
            'slug' => 'active1',
            'name' => ['uz' => 'Faol 1'],
            'status' => true,
            'sort_order' => 1,
        ]);

        PressureLevel::create([
            'slug' => 'active2',
            'name' => ['uz' => 'Faol 2'],
            'status' => true,
            'sort_order' => 2,
        ]);

        PressureLevel::create([
            'slug' => 'inactive',
            'name' => ['uz' => 'Nofaol'],
            'status' => false,
            'sort_order' => 3,
        ]);

        $active = $this->repository->getFilteredPaginated(['status' => 'active']);
        $inactive = $this->repository->getFilteredPaginated(['status' => 'inactive']);

        $this->assertEquals(2, $active->total());
        $this->assertEquals(1, $inactive->total());
    }

    /**
     * Test getFilteredPaginated pagination
     */
    public function test_get_filtered_paginated_respects_per_page(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            PressureLevel::create([
                'slug' => "pressure-{$i}",
                'name' => ['uz' => "Bosim {$i}"],
                'status' => true,
                'sort_order' => $i,
            ]);
        }

        $result = $this->repository->getFilteredPaginated([], 10);

        $this->assertEquals(15, $result->total());
        $this->assertEquals(10, $result->count());
        $this->assertTrue($result->hasPages());
    }
}
