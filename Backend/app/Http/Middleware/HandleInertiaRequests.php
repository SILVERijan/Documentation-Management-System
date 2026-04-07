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
                return $request->user()?->isAdmin()
                    ? Application::select('id', 'name', 'slug', 'color', 'status')
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('name', 'asc')
                        ->get()
                    : Application::where('status', 'active')
                        ->select('id', 'name', 'slug', 'color')
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('name', 'asc')
                        ->get();
            },
            'flash' => function (Request $request) {
                return $request->session()->get('flash_message');
            },
        ];
    }
}
