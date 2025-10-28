<?php

use Illuminate\Support\Facades\Broadcast;
use Modules\Chat\Models\Conversation;
use Illuminate\Support\Facades\Log;
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversations.{conversationId}', function ($user, $conversationId) {
    // 1. Fetch the conversation
    $conversation = Conversation::find($conversationId);

    // 2. Check if the conversation exists AND if the authenticated user ($user) is one of the participants
    $isParticipant = $conversation && 
                     ($conversation->user_one_id == $user->id || $conversation->user_two_id == $user->id);
    
    // 3. Log the result for debugging
    Log::debug("Channel Auth Check: User {$user->id} trying Conversation {$conversationId}. Conversation Found: " . ($conversation ? 'Yes' : 'No') . ". Is Participant: " . ($isParticipant ? 'TRUE' : 'FALSE'));

    // 4. Return a clear boolean value
    return (bool) $isParticipant; 
});