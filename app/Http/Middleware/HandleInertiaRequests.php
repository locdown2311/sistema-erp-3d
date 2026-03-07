<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

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
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $userData = null;

        if ($user) {
            $currentPlan = $user->currentPlan();
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'slug' => $user->slug,
                'is_admin' => $user->is_admin,
                'store_name' => $user->store_name,
                'store_logo' => $user->store_logo,
                'store_logo_thumbnail_url' => $user->store_logo_thumbnail_url,
                'can_use_nfe' => $currentPlan ? $currentPlan->can_use_nfe : false,
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $userData,
            ],
            'csrf_token' => csrf_token(),
            'setting' => [
                'company_name' => \App\Models\Setting::get('company_name', 'Central 3D'),
            ]
        ];
    }
}
