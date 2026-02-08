<?php

use Illuminate\Support\Facades\Broadcast;
use Modules\Chat\Models\Conversation;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversations.{conversationId}', function ($user, $conversationId) {
    if (!$user)
        return false;
    $conversation = Conversation::find($conversationId);

    if ($conversation && ($conversation->user_one_id == $user->id || $conversation->user_two_id == $user->id)) {
        return [
            'id' => $user->id,
            'name' => $user->full_name,
            'avatar' => $user->avatar_url,
        ];
    }
    return false;
});