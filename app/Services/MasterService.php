<?php

namespace App\Services;

use App\Models\Master;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterService
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    /**
     * Create a new master with user account
     */
    public function create(array $data, Request $request): Master
    {
        return DB::transaction(function () use ($data, $request) {
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->imageService->upload($request->file('photo'), 'masters');
            }

            // Create user account
            $user = User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
            $user->assignRole('master');

            // Create master
            $data['user_id'] = $user->id;
            $master = Master::create($data);

            // Attach relationships
            if ($request->has('service_types')) {
                $master->serviceTypes()->attach($request->service_types);
            }

            if ($request->has('oils')) {
                $master->oils()->attach($request->oils);
            }

            return $master;
        });
    }

    /**
     * Update an existing master
     */
    public function update(Master $master, array $data, Request $request): Master
    {
        return DB::transaction(function () use ($master, $data, $request) {
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->imageService->replace(
                    $master->photo,
                    $request->file('photo'),
                    'masters'
                );
            }

            // Update user account
            if ($master->user) {
                $userData = [
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                ];

                if ($request->filled('password')) {
                    $userData['password'] = $request->password;
                }

                $master->user->update($userData);
            }

            // Update master
            $master->update($data);

            // Sync relationships
            $master->serviceTypes()->sync($request->service_types ?? []);
            $master->oils()->sync($request->oils ?? []);

            return $master;
        });
    }

    /**
     * Delete a master and their user account
     */
    public function delete(Master $master): void
    {
        DB::transaction(function () use ($master) {
            // Delete photo
            $this->imageService->delete($master->photo);

            // Delete user account (will cascade delete master)
            if ($master->user) {
                $master->user->delete();
            } else {
                $master->delete();
            }
        });
    }

    /**
     * Get master data formatted for edit form
     */
    public function getEditData(Master $master): array
    {
        $master->load('user', 'serviceTypes', 'oils');

        return [
            'id' => $master->id,
            'user_id' => $master->user_id,
            'first_name' => $master->first_name,
            'last_name' => $master->last_name,
            'phone' => $master->phone,
            'email' => $master->email,
            'photo' => $master->photo,
            'photo_url' => $master->photo_url,
            'birth_date' => $master->birth_date?->format('Y-m-d'),
            'gender' => $master->gender,
            'experience_years' => $master->experience_years,
            'status' => $master->status,
            'service_types' => $master->serviceTypes->pluck('id')->toArray(),
            'oils' => $master->oils->pluck('id')->toArray(),
            'uz' => ['bio' => $master->getTranslation('bio', 'uz')],
            'ru' => ['bio' => $master->getTranslation('bio', 'ru')],
            'en' => ['bio' => $master->getTranslation('bio', 'en')],
        ];
    }
}
