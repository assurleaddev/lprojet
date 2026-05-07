<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrackingService
{
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

    private const INVALID_WORDS = ['introuvable', 'not found', 'aucun', 'invalide', 'invalid', 'erreur', '404'];

    public const CARRIER_LABELS = [
        'tawsil' => 'Tawssil',
        'ozon_express' => 'Ozon Express',
        'sendit' => 'Sendit',
        'ortalog' => 'Ortalog',
        'nearya' => 'Nearya',
        'ameex' => 'Ameex',
    ];

    /**
     * Verify a tracking code is valid by hitting the carrier API.
     */
    public function verify(string $carrier, string $code): bool
    {
        try {
            $result = $this->track($carrier, $code);

            return empty($result['error']) && ! empty($result['events']);
        } catch (\Throwable $e) {
            Log::warning('TrackingService verify failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Fetch full tracking data for a carrier + code.
     * Returns ['events' => [...], 'info' => [...], 'error' => string|null]
     */
    public function track(string $carrier, string $code): array
    {
        return match ($carrier) {
            'tawsil' => $this->trackTawssil($code),
            'ozon_express' => $this->trackOzon($code),
            'sendit' => $this->trackSendit($code),
            'ortalog' => $this->trackOrtalog($code),
            'nearya' => $this->trackNearya($code),
            'ameex' => $this->trackAmeex($code),
            default => ['error' => 'Unsupported carrier.', 'events' => [], 'info' => []],
        };
    }

    // ─── Tawssil ─────────────────────────────────────────────────────────────────

    private function trackTawssil(string $barcode): array
    {
        $proxy = config('services.tawssil_proxy');

        $response = Http::withOptions([
            'proxy' => ['http' => $proxy, 'https' => $proxy],
            'timeout' => 30,
            'verify' => false,
        ])->withHeaders([
            'User-Agent' => self::USER_AGENT,
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Origin' => 'https://tracking.tawssil.ma',
            'Referer' => 'https://tracking.tawssil.ma/',
        ])->asForm()->post('https://tracking.tawssil.ma/parcel/tracking/api/', [
            'barcode' => $barcode,
        ]);

        $html = $response->body();

        if ($this->isInvalidTawssil($html)) {
            return ['error' => 'Numéro de suivi invalide ou introuvable.', 'events' => [], 'info' => []];
        }

        $events = $this->parseTawssil($html);
        $info = [
            'receipt' => $barcode,
            'last_update' => $events[0]['date'] ?? '',
            'current_status' => $events[0]['status'] ?? '',
            'destination' => '',
            'amount' => '',
            'phone' => '',
        ];

        return ['error' => null, 'events' => $events, 'info' => $info];
    }

    private function isInvalidTawssil(string $html): bool
    {
        $doc = $this->parseHtml($html);
        $xpath = new \DOMXPath($doc);

        $hasForm = $xpath->query("//form[@action='/parcel/tracking/api/']")->length > 0;
        $hasEvents = $xpath->query("//div[contains(@class,'rounded-xl')]")->length > 0;

        return $hasForm && ! $hasEvents;
    }

    private function parseTawssil(string $html): array
    {
        $doc = $this->parseHtml($html);
        $xpath = new \DOMXPath($doc);
        $events = [];

        foreach ($xpath->query("//div[contains(@class,'rounded-xl')]") as $card) {
            $h3s = $xpath->query('.//h3', $card);
            $dateTag = $xpath->query('.//span', $card)->item(0);

            $step = $h3s->length > 0 ? trim($h3s->item(0)->textContent) : '';
            $status = $h3s->length > 1 ? trim($h3s->item(1)->textContent) : '';
            $date = $dateTag ? trim($dateTag->textContent) : '';

            if ($step || $status || $date) {
                $events[] = compact('step', 'status', 'date');
            }
        }

        return $events;
    }

    // ─── Ozon Express ────────────────────────────────────────────────────────────

    private function trackOzon(string $barcode): array
    {
        $response = Http::withOptions(['timeout' => 30])
            ->withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept-Language' => 'en-US,en;q=0.9,fr;q=0.8',
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'Origin' => 'https://ozonexpress.ma',
                'Referer' => 'https://ozonexpress.ma/',
                'X-Requested-With' => 'XMLHttpRequest',
            ])->asForm()->post('https://ozonexpress.ma/action?action=tracking-new', [
                'parcel-code' => $barcode,
            ]);

        $html = $response->body();

        if ($this->isInvalidOzon($html)) {
            return ['error' => 'Numéro de suivi invalide ou introuvable.', 'events' => [], 'info' => []];
        }

        [$events, $info] = $this->parseOzon($html);

        if (empty($info['receipt'])) {
            $info['receipt'] = $barcode;
        }

        return ['error' => null, 'events' => $events, 'info' => $info];
    }

    private function isInvalidOzon(string $html): bool
    {
        $doc = $this->parseHtml($html);
        $xpath = new \DOMXPath($doc);

        $hasTimeline = $xpath->query("//*[contains(@class,'timeline-step')]")->length > 0;
        $hasInfo = $xpath->query("//*[contains(@class,'ctm-info-item')]")->length > 0;
        $text = strtolower($doc->textContent);

        $hasInvalidWord = (bool) array_filter(self::INVALID_WORDS, fn ($w) => str_contains($text, $w));

        if ($hasInvalidWord && ! $hasTimeline) {
            return true;
        }

        return ! $hasTimeline && ! $hasInfo;
    }

    private function parseOzon(string $html): array
    {
        $doc = $this->parseHtml($html);
        $xpath = new \DOMXPath($doc);

        $info = ['receipt' => '', 'last_update' => '', 'current_status' => '', 'destination' => '', 'amount' => '', 'phone' => ''];

        foreach ($xpath->query("//*[contains(@class,'ctm-info-item')]") as $item) {
            $label = $xpath->query(".//*[contains(@class,'ctm-info-content')]//h3", $item)->item(0);
            $value = $xpath->query(".//*[contains(@class,'ctm-info-content')]//p", $item)->item(0);

            $l = $label ? strtolower(trim($label->textContent)) : '';
            $v = $value ? trim($value->textContent) : '';

            if (str_contains($l, 'récépissé') || str_contains($l, 'recepisse')) {
                $info['receipt'] = $v;
            } elseif (str_contains($l, 'dernière') || str_contains($l, 'mise')) {
                $info['last_update'] = $v;
            } elseif (str_contains($l, 'statut') || str_contains($l, 'status')) {
                $info['current_status'] = $v;
            }
        }

        $events = [];

        foreach ($xpath->query("//*[contains(@class,'timeline-step')]") as $step) {
            $title = $xpath->query(".//*[contains(@class,'timeline-step-title')]", $step)->item(0);
            $date = $xpath->query(".//*[contains(@class,'timeline-step-date')]", $step)->item(0);

            $status = $title ? trim($title->textContent) : '';
            $dateText = $date ? trim($date->textContent) : '';

            if ($status || $dateText) {
                $events[] = ['step' => 'Ozon Express', 'status' => $status, 'date' => $dateText];
            }
        }

        return [$events, $info];
    }

    // ─── Sendit ──────────────────────────────────────────────────────────────────

    private function trackSendit(string $barcode): array
    {
        $response = Http::withOptions(['timeout' => 30])
            ->withHeaders([
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9,fr;q=0.8',
                'User-Agent' => self::USER_AGENT,
            ])->get("https://app.sendit.ma/deliveries/{$barcode}");

        $html = $response->body();

        if ($this->isInvalidSendit($html)) {
            return ['error' => 'Numéro de suivi invalide ou introuvable.', 'events' => [], 'info' => []];
        }

        [$events, $info] = $this->parseSendit($html, $barcode);

        return ['error' => null, 'events' => $events, 'info' => $info];
    }

    private function isInvalidSendit(string $html): bool
    {
        $doc = $this->parseHtml($html);
        $xpath = new \DOMXPath($doc);

        $hasStatus = $xpath->query("//*[contains(@class,'section-status')]//*[contains(@class,'col-status')]//h5")->length > 0;
        $hasCode = $xpath->query("//*[contains(@class,'code-delivery')]")->length > 0;
        $hasCities = $xpath->query("//*[contains(@class,'section-city')]//*[contains(@class,'timeline-title')]")->length > 0;
        $text = strtolower($doc->textContent);

        $hasInvalidWord = (bool) array_filter(self::INVALID_WORDS, fn ($w) => str_contains($text, $w));

        if ($hasInvalidWord && ! $hasStatus) {
            return true;
        }

        return ! $hasStatus && ! $hasCode && ! $hasCities;
    }

    private function parseSendit(string $html, string $barcode): array
    {
        $doc = $this->parseHtml($html);
        $xpath = new \DOMXPath($doc);

        $info = ['receipt' => $barcode, 'last_update' => '', 'current_status' => '', 'destination' => '', 'amount' => '', 'phone' => ''];

        $codeTag = $xpath->query("//*[contains(@class,'code-delivery')]")->item(0);
        if ($codeTag) {
            $info['receipt'] = trim(str_replace('Code :', '', trim($codeTag->textContent)));
        }

        $statusTag = $xpath->query("//*[contains(@class,'section-status')]//*[contains(@class,'col-status')]//h5")->item(0);
        if ($statusTag) {
            $info['current_status'] = trim($statusTag->textContent);
        }

        $cityTitles = [];
        foreach ($xpath->query("//*[contains(@class,'section-city')]//*[contains(@class,'timeline-title')]") as $ct) {
            $cityTitles[] = trim($ct->textContent);
        }
        if ($cityTitles) {
            $info['destination'] = implode(' → ', $cityTitles);
        }

        $customerBox = $xpath->query("//*[contains(@class,'section-infos-customer')]")->item(0);
        if ($customerBox) {
            $text = trim($customerBox->textContent);
            if (str_contains($text, 'Téléphone') || str_contains($text, 'Telephone')) {
                $phonePart = preg_split('/Montant/i', $text)[0] ?? '';
                $info['phone'] = trim(preg_replace('/N°\s*Téléphone\s*:/iu', '', $phonePart));
            }
            if (str_contains($text, 'Montant')) {
                $info['amount'] = trim(explode('Montant :', $text)[1] ?? '');
            }
        }

        $events = [];
        foreach ($xpath->query("//*[contains(@class,'tracking-delivery-details')]//*[contains(@class,'timeline-item')]") as $item) {
            $title = $xpath->query(".//*[contains(@class,'timeline-title')]", $item)->item(0);
            $date = $xpath->query(".//*[contains(@class,'timeline-date') or contains(@class,'timeline-time') or @class='date']", $item)->item(0);

            $status = $title ? trim($title->textContent) : '';
            $dateText = $date ? trim($date->textContent) : '';

            if ($status || $dateText) {
                $events[] = ['step' => 'Sendit', 'status' => $status, 'date' => $dateText];
            }
        }

        if (empty($events) && $info['current_status']) {
            $events[] = ['step' => $info['destination'] ?: 'Sendit', 'status' => $info['current_status'], 'date' => $info['last_update']];
        }

        return [$events, $info];
    }

    // ─── Ortalog ─────────────────────────────────────────────────────────────────

    private function trackOrtalog(string $barcode): array
    {
        $response = Http::withOptions(['timeout' => 30])
            ->withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => '*/*',
                'Accept-Language' => 'en-US,en;q=0.9,fr;q=0.8',
                'Referer' => 'https://www.ortalog.ma/',
            ])->get('https://www.ortalog.ma/action', [
                'action' => 'track',
                'num' => $barcode,
            ]);

        $json = $response->json();

        if (empty($json['success']) || empty($json['SuiviTable'])) {
            return ['error' => 'Numéro de suivi invalide ou introuvable.', 'events' => [], 'info' => []];
        }

        $events = $this->parseOrtalog($json['SuiviTable']);

        if (empty($events)) {
            return ['error' => 'Numéro de suivi invalide ou introuvable.', 'events' => [], 'info' => []];
        }

        $info = [
            'receipt' => $barcode,
            'last_update' => $events[count($events) - 1]['date'] ?? '',
            'current_status' => $events[count($events) - 1]['status'] ?? '',
            'destination' => '',
            'amount' => '',
            'phone' => '',
        ];

        return ['error' => null, 'events' => $events, 'info' => $info];
    }

    private function parseOrtalog(string $html): array
    {
        $doc = $this->parseHtml($html);
        $xpath = new \DOMXPath($doc);
        $events = [];

        foreach ($xpath->query('//tbody/tr') as $row) {
            $cells = $xpath->query('./td', $row);
            if ($cells->length < 3) {
                continue;
            }

            $statusSpan = $xpath->query('.//span', $cells->item(1))->item(0);
            $status = $statusSpan ? trim($statusSpan->textContent) : trim($cells->item(1)->textContent);
            $date = trim($cells->item(2)->textContent);

            if ($status || $date) {
                $events[] = ['step' => 'Ortalog', 'status' => $status, 'date' => $date];
            }
        }

        return $events;
    }

    // ─── Nearya ──────────────────────────────────────────────────────────────────

    private function trackNearya(string $barcode): array
    {
        $response = Http::withOptions(['timeout' => 30])
            ->withHeaders([
                'Accept' => 'application/json, text/plain, */*',
                'User-Agent' => self::USER_AGENT,
                'Origin' => 'https://nearya.express',
                'Referer' => 'https://nearya.express/',
            ])->get('https://nearya.express/api/docParcelStatus/public', [
                'cab' => $barcode,
            ]);

        $json = $response->json();

        if (($json['status'] ?? '') !== 'success' || empty($json['data']['docsParcelStatus'])) {
            return ['error' => 'Numéro de suivi invalide ou introuvable.', 'events' => [], 'info' => []];
        }

        $statuses = $json['data']['docsParcelStatus'];

        // Sort by ordre ascending (API may not always return sorted)
        usort($statuses, fn ($a, $b) => ($a['status']['ordre'] ?? 0) <=> ($b['status']['ordre'] ?? 0));

        $events = array_map(fn ($item) => [
            'step' => 'Nearya',
            'status' => $item['status']['name'] ?? '',
            'date' => isset($item['createdAt'])
                ? date('Y-m-d H:i', strtotime($item['createdAt']))
                : '',
        ], $statuses);

        $parcel = $statuses[0]['parcel'] ?? [];
        $last = end($statuses);

        $info = [
            'receipt' => $parcel['cab'] ?? $barcode,
            'last_update' => isset($last['createdAt']) ? date('Y-m-d H:i', strtotime($last['createdAt'])) : '',
            'current_status' => $last['status']['name'] ?? '',
            'destination' => $parcel['recipientAddress'] ?? '',
            'amount' => isset($parcel['price']) ? $parcel['price'].' MAD' : '',
            'phone' => '',
        ];

        return ['error' => null, 'events' => $events, 'info' => $info];
    }

    // ─── Ameex ───────────────────────────────────────────────────────────────────

    private function trackAmeex(string $barcode): array
    {
        $response = Http::withOptions(['timeout' => 30])
            ->withHeaders([
                'Accept' => '*/*',
                'User-Agent' => self::USER_AGENT,
                'Origin' => 'https://ameex.ma',
                'Referer' => 'https://ameex.ma/',
            ])->get('https://api.ameex.app/web/Tracking', [
                'ParcelCode' => $barcode,
            ]);

        $json = $response->json();

        if (($json['api']['type'] ?? '') !== 'success' || empty($json['api']['tracking'])) {
            return ['error' => 'Numéro de suivi invalide ou introuvable.', 'events' => [], 'info' => []];
        }

        // tracking is a keyed object — sort entries by their numeric key (represents order)
        $tracking = $json['api']['tracking'];
        ksort($tracking, SORT_NUMERIC);

        $events = array_map(fn ($item) => [
            'step' => $item['city_name'] ?? $item['hub_name'] ?? 'Ameex',
            'status' => $item['statut_name'] ?? '',
            'date' => $item['time_str'] ?? '',
        ], $tracking);

        $last = end($tracking);
        $first = reset($tracking);

        $info = [
            'receipt' => $barcode,
            'last_update' => $last['time_str'] ?? '',
            'current_status' => $last['statut_name'] ?? '',
            'destination' => $last['city_name'] ?? $last['hub_name'] ?? '',
            'amount' => '',
            'phone' => '',
        ];

        return ['error' => null, 'events' => array_values($events), 'info' => $info];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    private function parseHtml(string $html): \DOMDocument
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        return $doc;
    }
}
