<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Address;
use App\Models\ShippingOption;
use App\Services\MediaLibraryService;

class SettingsController extends Controller
{
    public function __construct(private readonly MediaLibraryService $mediaLibraryService)
    {
    }

    public function profile()
    {
        $user = Auth::user();
        return view('frontend.settings.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'about' => 'nullable|string|max:1000',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:10',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user->update([
            'username' => $request->username,
        ]);

        // Update meta
        $this->updateMeta($user, 'about', $request->about);
        $this->updateMeta($user, 'country', $request->country);
        $this->updateMeta($user, 'city', $request->city);
        $this->updateMeta($user, 'show_city', $request->has('show_city') ? '1' : '0');
        $this->updateMeta($user, 'language', $request->language);

        // Handle Avatar
        if ($request->hasFile('avatar')) {
            $uploadedFiles = $this->mediaLibraryService->uploadMedia([$request->file('avatar')]);
            if (!empty($uploadedFiles)) {
                $media = $uploadedFiles[0];
                $user->update(['avatar_id' => $media->id]);
            }
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function account()
    {
        $user = Auth::user();
        return view('frontend.settings.account', compact('user'));
    }

    public function updateAccount(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'gender' => 'nullable|string|in:Male,Female,Other',
            'birthday' => 'nullable|date',
            'holiday_mode' => 'nullable|boolean',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $this->updateMeta($user, 'gender', $request->gender);
        $this->updateMeta($user, 'birthday', $request->birthday);
        $this->updateMeta($user, 'holiday_mode', $request->has('holiday_mode') ? '1' : '0');
        $this->updateMeta($user, 'phone_number', $request->phone_number);

        return back()->with('success', 'Account settings updated successfully.');
    }

    public function postage()
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->get();
        $shippingOptions = ShippingOption::where('is_active', true)->get();

        return view('frontend.settings.postage', compact('user', 'addresses', 'shippingOptions'));
    }

    public function updatePostage(Request $request)
    {
        $user = Auth::user();
        $shippingOptions = ShippingOption::where('is_active', true)->get();

        foreach ($shippingOptions as $option) {
            $this->updateMeta($user, $option->key, $request->has($option->key) ? '1' : '0');
        }

        return back()->with('success', 'Postage settings updated successfully.');
    }

    public function storeAddress(Request $request)
    {
        $request->validate([
            'country' => 'required|string',
            'full_name' => 'required|string',
            'address_line_1' => 'required|string',
            'address_line_2' => 'nullable|string',
            'city' => 'required|string',
            'postcode' => 'required|string',
        ]);

        Address::create([
            'user_id' => Auth::id(),
            'country' => $request->country,
            'full_name' => $request->full_name,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city' => $request->city,
            'postcode' => $request->postcode,
        ]);

        return back()->with('success', 'Address added successfully.');
    }

    public function notifications()
    {
        $user = auth()->user();
        return view('frontend.settings.notifications', compact('user'));
    }

    public function updateNotifications(Request $request)
    {
        $user = auth()->user();

        // Checkboxes are not sent if unchecked, so we need to handle that.
        // We'll iterate through expected keys or just set what's present?
        // Better to explicitly handle known keys.
        $keys = [
            'enable_email_notifications',
            'notify_vinted_updates',
            'notify_marketing',
            'notify_high_priority_messages',
            'notify_high_priority_feedback',
            'notify_high_priority_reduced_items',
            'notify_favourited_items',
            'notify_new_items',
        ];

        foreach ($keys as $key) {
            $value = $request->has($key) ? '1' : '0';
            $this->updateMeta($user, $key, $value);
        }

        if ($request->has('notification_limit')) {
            $this->updateMeta($user, 'notification_limit', $request->input('notification_limit'));
        }

        return back()->with('success', 'Notification settings updated successfully.');
    }

    public function security()
    {
        $user = auth()->user();
        $sessions = [];

        if (config('session.driver') === 'database') {
            $sessions = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderBy('last_activity', 'desc')
                ->get();
        }

        return view('frontend.settings.security', compact('user', 'sessions'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $user = auth()->user();
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function requestEmailChange(Request $request)
    {
        $request->validate([
            'new_email' => ['required', 'email', 'unique:users,email'],
        ]);

        $user = auth()->user();
        $code = rand(100000, 999999);

        \App\Models\VerificationCode::create([
            'user_id' => $user->id,
            'type' => 'email_change',
            'code' => $code,
            'data' => $request->new_email,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send email
        \Illuminate\Support\Facades\Mail::raw("Your verification code is: $code", function ($message) use ($request) {
            $message->to($request->new_email)
                ->subject('Verify Email Change');
        });

        return back()->with('email_verification_sent', true)->with('success', 'Verification code sent to new email.');
    }

    public function verifyEmailChange(Request $request)
    {
        $request->validate([
            'code' => ['required'],
        ]);

        $user = auth()->user();
        $verification = \App\Models\VerificationCode::where('user_id', $user->id)
            ->where('type', 'email_change')
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return back()->with('email_verification_sent', true)->with('error', 'Invalid or expired code.');
        }

        $user->update(['email' => $verification->data]);
        $verification->delete();

        return back()->with('success', 'Email updated successfully.');
    }

    public function toggleTwoFactor(Request $request)
    {
        $user = auth()->user();

        if ($request->has('enable_2fa')) {
            // Send verification code to confirm enabling
            $code = rand(100000, 999999);

            \App\Models\VerificationCode::create([
                'user_id' => $user->id,
                'type' => '2fa_enable',
                'code' => $code,
                'expires_at' => now()->addMinutes(15),
            ]);

            // Send email
            \Illuminate\Support\Facades\Mail::raw("Your verification code to enable 2FA is: $code", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Verify 2FA Enable');
            });

            return back()->with('2fa_verification_needed', true);
        } else {
            // Disable 2FA
            $this->updateMeta($user, 'enable_2fa', '0');
            return back()->with('success', 'Two-factor authentication disabled.');
        }
    }

    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => ['required'],
        ]);

        $user = auth()->user();
        $verification = \App\Models\VerificationCode::where('user_id', $user->id)
            ->where('type', '2fa_enable')
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return back()->with('2fa_verification_needed', true)->with('error', 'Invalid or expired code.');
        }

        $this->updateMeta($user, 'enable_2fa', '1');
        $verification->delete();

        return back()->with('success', 'Two-factor authentication enabled.');
    }

    public function logoutSession($id)
    {
        \Illuminate\Support\Facades\DB::table('sessions')->where('id', $id)->delete();
        return back()->with('success', 'Session logged out.');
    }

    private function updateMeta($user, $key, $value)
    {
        $user->userMeta()->updateOrCreate(
            ['meta_key' => $key],
            ['meta_value' => $value]
        );
    }
}
