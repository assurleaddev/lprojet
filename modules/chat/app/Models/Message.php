<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
// use Modules\Chat\Database\Factories\MessageFactory;

class Message extends Model
{
    // protected $table = 'chat_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
        'read_at',
        'delivered_at',
        'offer_id',
        'type',
        'attachment_path',
        'attachment_type',
    ];

    /**
     * Get the user who sent the message.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the conversation this message belongs to.
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function offer()
    {
        // Links the 'offer_id' column on this message table 
        // to the 'id' column on the 'chat_offers' table.
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }
}
