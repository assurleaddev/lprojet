<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MediaLibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Mobile profile & settings — mirrors the web SettingsController for the
 * self-contained parts: profile fields (+ user_meta), avatar, password and
 * notification preferences. Verification-gated flows (email change, 2FA,
 * sessions) and separate CRUD (addresses, payout accounts) are not exposed here.
 */
class ProfileController extends Controller
{
    /** Notification preference meta keys (same as the web notifications page). */
    private const NOTIFICATION_KEYS = [
        'enable_email_notifications',
        'notify_vinted_updates',
        'notify_marketing',
        'notify_high_priority_messages',
        'notify_high_priority_feedback',
        'notify_high_priority_reduced_items',
        'notify_favourited_items',
        'notify_new_items',
    ];

    public function __construct(private readonly MediaLibraryService $media)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json($this->payload($request->user()->load('userMeta')));
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'about' => ['nullable', 'string', 'max:1000'],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'show_city' => ['nullable', 'boolean'],
            'language' => ['nullable', 'string', 'max:10'],
            'gender' => ['nullable', 'string', 'in:Male,Female,Other'],
            'birthday' => ['nullable', 'date'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $userFields = collect($validated)
            ->only(['first_name', 'last_name', 'username'])
            ->filter(fn ($v) => $v !== null)
            ->toArray();
        if (! empty($userFields)) {
            $user->update($userFields);
        }

        foreach (['about', 'country', 'city', 'language', 'gender', 'birthday'] as $key) {
            if ($request->filled($key)) {
                $this->setMeta($user, $key, $validated[$key]);
            }
        }
        if ($request->has('show_city')) {
            $this->setMeta($user, 'show_city', $request->boolean('show_city') ? '1' : '0');
        }

        if ($request->hasFile('avatar')) {
            $uploaded = $this->media->uploadMedia([$request->file('avatar')]);
            if (! empty($uploaded)) {
                $user->update(['avatar_id' => $uploaded[0]->id]);
            }
        }

        return response()->json($this->payload($user->refresh()->load('userMeta')));
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
                'errors' => ['current_password' => ['Le mot de passe actuel est incorrect.']],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }

    public function updateNotifications(Request $request): JsonResponse
    {
        $user = $request->user();

        foreach (self::NOTIFICATION_KEYS as $key) {
            if ($request->has($key)) {
                $this->setMeta($user, $key, $request->boolean($key) ? '1' : '0');
            }
        }
        if ($request->filled('notification_limit')) {
            $this->setMeta($user, 'notification_limit', (string) $request->notification_limit);
        }

        return response()->json($this->notifications($user->refresh()->load('userMeta')));
    }

    private function setMeta(User $user, string $key, string $value): void
    {
        $user->userMeta()->updateOrCreate(['meta_key' => $key], ['meta_value' => $value]);
    }

    private function payload(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'username' => $user->username,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'phone_number' => $user->phone_number,
            'member_since' => $user->created_at?->year,
            'about' => $user->getMeta('about'),
            'country' => $user->getMeta('country'),
            'city' => $user->getMeta('city'),
            'show_city' => $user->getMeta('show_city', '0') === '1',
            'language' => $user->getMeta('language'),
            'gender' => $user->getMeta('gender'),
            'birthday' => $user->getMeta('birthday'),
            'notifications' => $this->notifications($user),
            'stats' => [
                'products_count' => $user->products()->count(),
            ],
        ];
    }

    /** Follow / unfollow a user (mirrors the web HomeController::toggleFollow). */
    public function toggleFollow(Request $request, int $id): JsonResponse
    {
        $me = $request->user();
        $user = User::findOrFail($id);

        abort_if($me->is($user), 422, 'You cannot follow yourself.');

        $me->isFollowing($user) ? $me->unfollow($user) : $me->follow($user);
        $isFollowing = $me->isFollowing($user);

        if ($isFollowing) {
            try {
                $user->notify(new \App\Notifications\NewFollowerNotification($me));
            } catch (\Throwable $e) {
                // Notification failure must not break the follow action.
            }
        }

        return response()->json([
            'following' => $isFollowing,
            'followers_count' => $user->followers()->count(),
        ]);
    }

    private function notifications(User $user): array
    {
        // Defaults must MATCH enforcement: the notification classes' via() use
        // getMeta($key, '1') for these, so an untouched account shows them ON.
        // The two marketing opt-ins are unenforced and default OFF.
        $defaults = ['notify_vinted_updates' => '0', 'notify_marketing' => '0'];

        $out = [];
        foreach (self::NOTIFICATION_KEYS as $key) {
            $out[$key] = $user->getMeta($key, $defaults[$key] ?? '1') === '1';
        }
        $out['notification_limit'] = $user->getMeta('notification_limit', 'unlimited');

        return $out;
    }
}
