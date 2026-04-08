<?php

namespace Modules\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Chat\Services\ChatService;

class ChatController extends Controller
{
    /**
     * Get or create a conversation with a user (e.g. from a liked-product notification)
     * and redirect to the chat dashboard with that conversation selected.
     */
    public function startWithUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $otherUser = User::findOrFail($request->user_id);
        $product = $request->product_id ? Product::find($request->product_id) : null;

        $chatService = app(ChatService::class);
        $conversation = $chatService->getOrCreateConversation(Auth::user(), $otherUser, $product);

        return redirect()->route('chat.dashboard', ['id' => $conversation->id]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('chat::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('chat::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('chat::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('chat::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
    }
}
