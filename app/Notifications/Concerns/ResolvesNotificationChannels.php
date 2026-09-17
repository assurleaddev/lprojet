<?php

declare(strict_types=1);

namespace App\Notifications\Concerns;

trait ResolvesNotificationChannels
{
    /**
     * Resolve the delivery channels for a notification based on the notifiable's
     * stored preferences.
     *
     * When a $preferenceKey is provided and the notifiable has opted out of it,
     * no channels are returned. The mail channel is appended only when the
     * notifiable has global email notifications enabled.
     *
     * @param  array<int, string>  $baseChannels
     * @return array<int, string>
     */
    protected function resolveChannels(object $notifiable, array $baseChannels = ['database', 'broadcast'], ?string $preferenceKey = null): array
    {
        if ($preferenceKey !== null && $notifiable->getMeta($preferenceKey, '1') !== '1') {
            return [];
        }

        $channels = $baseChannels;

        if ($notifiable->getMeta('enable_email_notifications', '1') === '1') {
            $channels[] = 'mail';
        }

        return $channels;
    }
}
