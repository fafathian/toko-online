<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

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
            'cartCount' => $request->user() ? \App\Models\Cart::where('user_id', $request->user()->id)->sum('quantity') : 0,
            'hasStore' => $request->user() ? \App\Models\Store::where('user_id', $request->user()->id)->exists() : false,
            'unreadChatCount' => fn() => $request->user()
                ? \App\Models\ChatMessage::where('user_id', '!=', $request->user()->id)
                ->where('is_read', false)
                ->whereHas('chatRoom', function ($query) use ($request) {
                    // Cek apakah user ini sebagai pembeli ATAU sebagai penjual di room tersebut
                    $query->where('buyer_id', $request->user()->id)
                        ->orWhereHas('store', function ($q) use ($request) {
                            $q->where('user_id', $request->user()->id);
                        });
                })->count()
                : 0,
        ];
    }
}
