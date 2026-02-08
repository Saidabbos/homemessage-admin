<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return User::class;
    }

    public function getByRolePaginated(string $role, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->query()->role($role);

        $this->applySearch($query, $filters['search'] ?? null, ['name', 'email', 'phone']);
        $this->applyStatusFilter($query, $filters['status'] ?? null);

        return $this->paginate($query->latest(), $perPage);
    }

    public function getDispatchers(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->getByRolePaginated('dispatcher', $filters, $perPage);
    }

    public function getCustomers(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->getByRolePaginated('customer', $filters, $perPage);
    }

    /**
     * Find user by phone number
     */
    public function findByPhone(string $phone): ?User
    {
        return $this->query()->where('phone', $phone)->first();
    }
}
