<?php

namespace App\Services;

use App\Models\Master;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        Log::info('MasterService: Creating new master', [
            'email' => $data['email'],
            'name' => $data['first_name'] . ' ' . $data['last_name'],
        ]);

        return DB::transaction(function () use ($data, $request) {
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->imageService->upload($request->file('photo'), 'masters');
                Log::info('MasterService: Photo uploaded', ['photo' => $data['photo']]);
            }

            // Create user account
            $user = User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
            $user->assignRole('master');
            Log::info('MasterService: User account created', ['user_id' => $user->id]);

            // Create master
            $data['user_id'] = $user->id;
            $master = Master::create($data);
            Log::info('MasterService: Master record created', ['master_id' => $master->id]);

            // Attach relationships
            if ($request->has('service_types')) {
                $master->serviceTypes()->attach($request->service_types);
                Log::info('MasterService: Service types attached', ['count' => count($request->service_types)]);
            }

            if ($request->has('oils')) {
                $master->oils()->attach($request->oils);
                Log::info('MasterService: Oils attached', ['count' => count($request->oils)]);
            }

            if ($request->has('pressure_levels')) {
                $master->pressureLevels()->attach($request->pressure_levels);
                Log::info('MasterService: Pressure levels attached', ['count' => count($request->pressure_levels)]);
            }

            Log::info('MasterService: Master created successfully', ['master_id' => $master->id]);
            return $master;
        });
    }

    /**
     * Update an existing master
     */
    public function update(Master $master, array $data, Request $request): Master
    {
        Log::info('MasterService: Updating master', ['master_id' => $master->id]);

        return DB::transaction(function () use ($master, $data, $request) {
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->imageService->replace(
                    $master->photo,
                    $request->file('photo'),
                    'masters'
                );
                Log::info('MasterService: Photo updated', ['master_id' => $master->id]);
            }

            // Update user account
            if ($master->user) {
                $userData = [
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                ];

                if ($request->filled('password')) {
                    $userData['password'] = $request->password;
                    Log::info('MasterService: Password updated', ['master_id' => $master->id]);
                }

                $master->user->update($userData);
                Log::info('MasterService: User account updated', ['user_id' => $master->user->id]);
            }

            // Update master
            $master->update($data);

            // Sync relationships
            $master->serviceTypes()->sync($request->service_types ?? []);
            $master->oils()->sync($request->oils ?? []);
            $master->pressureLevels()->sync($request->pressure_levels ?? []);
            Log::info('MasterService: Relationships synced', ['master_id' => $master->id]);

            Log::info('MasterService: Master updated successfully', ['master_id' => $master->id]);
            return $master;
        });
    }

    /**
     * Delete a master and their user account
     */
    public function delete(Master $master): void
    {
        Log::info('MasterService: Deleting master', ['master_id' => $master->id]);

        DB::transaction(function () use ($master) {
            // Delete photo
            $this->imageService->delete($master->photo);

            // Delete user account (will cascade delete master)
            if ($master->user) {
                $master->user->delete();
                Log::info('MasterService: User account deleted', ['master_id' => $master->id]);
            } else {
                $master->delete();
            }
        });

        Log::info('MasterService: Master deleted successfully', ['master_id' => $master->id]);
    }

    /**
     * Get master data formatted for edit form
     */
    public function getEditData(Master $master): array
    {
        $master->load('user', 'serviceTypes', 'oils', 'pressureLevels');

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
            'pressure_levels' => $master->pressureLevels->pluck('id')->toArray(),
            'uz' => ['bio' => $master->getTranslation('bio', 'uz')],
            'ru' => ['bio' => $master->getTranslation('bio', 'ru')],
            'en' => ['bio' => $master->getTranslation('bio', 'en')],
        ];
    }
}
