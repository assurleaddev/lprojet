<?php

namespace Modules\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = ['wallet_id', 'amount', 'status', 'bank_details', 'admin_note'];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
