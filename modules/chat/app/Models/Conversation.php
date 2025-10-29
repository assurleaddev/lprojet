<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User; 
use App\Models\Product;
// use Modules\Chat\Database\Factories\ConversationFactory;

class Conversation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'chat_conversations';
    protected $fillable = ['product_id', 'user_one_id', 'user_two_id', 'last_message_at'];

    // Relationships
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }
    
    // Helper to get the other user in the conversation
    public function getOtherUser(User $currentUser): User
    {
        return $this->user_one_id === $currentUser->id ? $this->userTwo : $this->userOne;
    }
}
