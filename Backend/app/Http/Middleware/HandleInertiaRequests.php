<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Application;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'applications' => function (Request $request) {
                $user = $request->user();
                $query = Application::query();
                
                // If not admin, only show active applications
                if (!$user || $user->role !== 'admin') {
                    $query->where('status', 'active');
                }
                
                return $query->select('id', 'name', 'slug', 'color', 'status')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();
            },
        ];
    }
}
