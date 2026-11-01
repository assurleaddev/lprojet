<?php

use App\Notifications\OfferNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Offer notifications used to be stored as "New offer of $144.00 on …".
 * Prices are in MAD, so rewrite the stored text of existing rows to
 * "New offer of 144.00 MAD on …" (web dropdown + mobile list read it as-is).
 */
return new class () extends Migration {
    public function up(): void
    {
        DB::table('notifications')
            ->where('type', OfferNotification::class)
            ->where('data', 'like', '%$%')
            ->orderBy('id')
            ->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $data = json_decode($row->data, true);
                    if (! is_array($data) || ! isset($data['message'])) {
                        continue;
                    }

                    $message = preg_replace('/\$\s?([\d,]+(?:\.\d+)?)/', '$1 MAD', $data['message']);
                    if ($message === $data['message']) {
                        continue;
                    }

                    $data['message'] = $message;
                    DB::table('notifications')->where('id', $row->id)->update(['data' => json_encode($data)]);
                }
            });
    }

    public function down(): void
    {
        // Text-only data fix; nothing to restore.
    }
};
