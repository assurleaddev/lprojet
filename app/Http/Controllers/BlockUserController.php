<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockUserController extends Controller
{
    public function toggleBlock(User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->id === $user->id) {
            return response()->json(['message' => 'You cannot block yourself.'], 422);
        }

        if ($currentUser->blockedUsers()->where('blocked_user_id', $user->id)->exists()) {
            $currentUser->blockedUsers()->detach($user->id);
            $isBlocked = false;
            $message = 'User unblocked successfully.';
        } else {
            $currentUser->blockedUsers()->attach($user->id);
            $isBlocked = true;
            $message = 'User blocked successfully.';
        }

        return response()->json([
            'blocked' => $isBlocked,
            'message' => $message,
        ]);
    }
}
