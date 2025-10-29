<?php

use Illuminate\Support\Facades\Route;
use Modules\Chat\Http\Controllers\ChatController;
use Modules\Chat\Livewire\ChatDashboard; // You would create a parent component called ChatDashboard
use Modules\Chat\Livewire\ChatWindow;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('chats', ChatController::class)->names('chat');
});

Route::middleware(['web', 'auth'])->prefix('chat')->group(function () {
    // Use the component directly as an invokable action for full-page Livewire components
    // We explicitly reference the full class.
    
    // Main chat dashboard
    Route::get('/', ChatDashboard::class)
        ->name('chat.dashboard');
    
    // Route to open a specific conversation, used for deep-linking
    // We pass the conversation ID as a parameter which the ChatDashboard component will handle
    Route::get('/{queryConversationId}', ChatDashboard::class)
        ->name('chat.show')
        ->where('queryConversationId', '[0-9]+'); // Ensures the parameter is a number
});