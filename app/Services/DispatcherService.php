<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class DispatcherService
{
    /**
     * Create a new dispatcher
     */
    public function create(array $data, Request $request): User
    {
        $data['status'] = $request->boolean('status', true);

        $user = User::create($data);
        $user->assignRole('dispatcher');

        return $user;
    }

    /**
     * Update an existing dispatcher
     */
    public function update(User $dispatcher, array $data, Request $request): User
    {
        $data['status'] = $request->boolean('status', true);

        // Remove password if not provided
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $dispatcher->update($data);

        return $dispatcher;
    }

    /**
     * Delete a dispatcher
     */
    public function delete(User $dispatcher): void
    {
        $dispatcher->delete();
    }
}
