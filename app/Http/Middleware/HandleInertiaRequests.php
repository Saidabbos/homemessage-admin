<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Get the authenticated user based on route context
     */
    protected function getAuthUser(Request $request)
    {
        // For admin routes, use admin guard
        if ($request->is('admin/*') || $request->is('admin')) {
            return Auth::guard('admin')->user();
        }
        
        // For other routes, use default web guard
        return Auth::guard('web')->user();
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $parentShare = parent::share($request);
        $user = $this->getAuthUser($request);

        return array_merge($parentShare, [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => substr($user->name ?? $user->phone ?? '', 0, 1),
                    'role' => $user->getRoleNames()->first() ?? 'User',
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'ziggy' => fn () => array_merge((new \Tighten\Ziggy\Ziggy)->toArray(), [
                'location' => $request->url(),
            ]),
        ]);
    }
}
