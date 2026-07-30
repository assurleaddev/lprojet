<?php

declare(strict_types=1);

use App\Services\TrackingService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->service = new TrackingService();
});

test('track returns an error for an unsupported carrier', function () {
    $result = $this->service->track('unknown_carrier', 'ABC123');

    expect($result['error'])->toEqual('Unsupported carrier.')
        ->and($result['events'])->toBeEmpty()
        ->and($result['info'])->toBeEmpty();
});

test('carrier labels are exposed for every carrier handled by track', function () {
    expect(array_keys(TrackingService::CARRIER_LABELS))->toContain(
        'tawsil',
        'ozon_express',
        'sendit',
        'ortalog',
        'nearya',
        'ameex',
        'cathedis',
        'speedaf',
        'amana',
        'olivraison',
        'coliaty',
        'digylog',
    );
});

test('verify returns true when the carrier returns events', function () {
    Http::fake([
        'www.ortalog.ma/*' => Http::response([
            'success' => true,
            'SuiviTable' => '<table><tbody><tr><td>1</td><td><span>Livré</span></td><td>2026-01-02 10:00</td></tr></tbody></table>',
        ]),
    ]);

    expect($this->service->verify('ortalog', 'ORT1'))->toBeTrue();
});

test('verify returns false when the carrier reports an invalid code', function () {
    Http::fake([
        'www.ortalog.ma/*' => Http::response(['success' => false]),
    ]);

    expect($this->service->verify('ortalog', 'ORT1'))->toBeFalse();
});

test('verify returns false when the carrier request throws', function () {
    Http::fake(function () {
        throw new \RuntimeException('connection refused');
    });

    expect($this->service->verify('ortalog', 'ORT1'))->toBeFalse();
});

test('ortalog tracking parses the html table into events', function () {
    Http::fake([
        'www.ortalog.ma/*' => Http::response([
            'success' => true,
            'SuiviTable' => <<<'HTML'
                <table><tbody>
                    <tr><td>1</td><td><span>Colis ramassé</span></td><td>2026-01-01 09:00</td></tr>
                    <tr><td>2</td><td><span>Colis livré</span></td><td>2026-01-02 15:30</td></tr>
                    <tr><td>ignored row</td></tr>
                </tbody></table>
                HTML,
        ]),
    ]);

    $result = $this->service->track('ortalog', 'ORT1');

    expect($result['error'])->toBeNull()
        ->and($result['events'])->toHaveCount(2)
        ->and($result['events'][0])->toEqual(['step' => 'Ortalog', 'status' => 'Colis ramassé', 'date' => '2026-01-01 09:00'])
        ->and($result['info']['receipt'])->toEqual('ORT1')
        ->and($result['info']['current_status'])->toEqual('Colis livré')
        ->and($result['info']['last_update'])->toEqual('2026-01-02 15:30');
});

test('ortalog tracking errors when the table has no usable rows', function () {
    Http::fake([
        'www.ortalog.ma/*' => Http::response([
            'success' => true,
            'SuiviTable' => '<table><tbody><tr><td>only</td><td>two</td></tr></tbody></table>',
        ]),
    ]);

    $result = $this->service->track('ortalog', 'ORT1');

    expect($result['error'])->toEqual('Numéro de suivi invalide ou introuvable.')
        ->and($result['events'])->toBeEmpty();
});

test('nearya tracking sorts statuses by order and builds info from the parcel', function () {
    Http::fake([
        'nearya.express/*' => Http::response([
            'status' => 'success',
            'data' => [
                'docsParcelStatus' => [
                    [
                        'status' => ['name' => 'Livré', 'ordre' => 2],
                        'createdAt' => '2026-01-02T15:30:00Z',
                        'parcel' => ['cab' => 'NEA-1', 'recipientAddress' => 'Casablanca', 'price' => 250],
                    ],
                    [
                        'status' => ['name' => 'Créé', 'ordre' => 1],
                        'createdAt' => '2026-01-01T09:00:00Z',
                        'parcel' => ['cab' => 'NEA-1', 'recipientAddress' => 'Casablanca', 'price' => 250],
                    ],
                ],
            ],
        ]),
    ]);

    $result = $this->service->track('nearya', 'NEA-1');

    expect($result['error'])->toBeNull()
        ->and(array_column($result['events'], 'status'))->toEqual(['Créé', 'Livré'])
        ->and($result['info']['receipt'])->toEqual('NEA-1')
        ->and($result['info']['current_status'])->toEqual('Livré')
        ->and($result['info']['destination'])->toEqual('Casablanca')
        ->and($result['info']['amount'])->toEqual('250 MAD');
});

test('nearya tracking errors when the api reports a failure', function () {
    Http::fake([
        'nearya.express/*' => Http::response(['status' => 'error']),
    ]);

    expect($this->service->track('nearya', 'NEA-1')['error'])
        ->toEqual('Numéro de suivi invalide ou introuvable.');
});

test('ameex tracking orders the keyed tracking object numerically', function () {
    Http::fake([
        'api.ameex.app/*' => Http::response([
            'api' => [
                'type' => 'success',
                'tracking' => [
                    '10' => ['city_name' => 'Rabat', 'statut_name' => 'Livré', 'time_str' => '2026-01-02 15:30'],
                    '2' => ['hub_name' => 'Hub Casa', 'statut_name' => 'Ramassé', 'time_str' => '2026-01-01 09:00'],
                ],
            ],
        ]),
    ]);

    $result = $this->service->track('ameex', 'AME-1');

    expect($result['error'])->toBeNull()
        ->and($result['events'][0])->toEqual(['step' => 'Hub Casa', 'status' => 'Ramassé', 'date' => '2026-01-01 09:00'])
        ->and($result['events'][1]['step'])->toEqual('Rabat')
        ->and($result['info']['current_status'])->toEqual('Livré')
        ->and($result['info']['destination'])->toEqual('Rabat');
});

test('ameex tracking errors when tracking is empty', function () {
    Http::fake([
        'api.ameex.app/*' => Http::response(['api' => ['type' => 'success', 'tracking' => []]]),
    ]);

    expect($this->service->track('ameex', 'AME-1')['error'])
        ->toEqual('Numéro de suivi invalide ou introuvable.');
});

test('cathedis tracking builds a full timeline and keeps only reached steps as events', function () {
    Http::fake([
        'www.cathedis.ma/*' => Http::response([
            'data' => [[
                'values' => [
                    'steps' => [
                        ['description' => 'CREATED', 'createdOn' => '2026-01-01 09:00:00', 'city' => ['fullName' => 'Casablanca']],
                        ['description' => 'COLLECTED', 'actionDate' => '2026-01-01 12:00:00', 'city' => ['name' => 'Casa']],
                    ],
                ],
            ]],
        ]),
    ]);

    $result = $this->service->track('cathedis', 'CAT-1');

    expect($result['error'])->toBeNull()
        ->and($result['timeline'])->toHaveCount(5)
        ->and($result['events'])->toHaveCount(2)
        ->and($result['events'][0]['step'])->toEqual('Casablanca')
        ->and($result['events'][1]['date'])->toEqual('2026-01-01 12:00')
        ->and($result['info']['current_status'])->toEqual('Colis collecté')
        ->and($result['info']['destination'])->toEqual('Casa')
        ->and(array_column($result['timeline'], 'active'))->toEqual([true, true, false, false, false]);
});

test('cathedis tracking errors when no steps are returned', function () {
    Http::fake([
        'www.cathedis.ma/*' => Http::response(['data' => []]),
    ]);

    expect($this->service->track('cathedis', 'CAT-1')['error'])
        ->toEqual('Numéro de suivi invalide ou introuvable.');
});

test('speedaf tracking reverses tracks and extracts the hub location', function () {
    Http::fake([
        'speedaf.com/*' => Http::response([
            'success' => true,
            'data' => [[
                'tracks' => [
                    ['actionName' => 'Delivered', 'time' => '2026-01-02 15:30', 'msgEng' => 'Parcel arrived at 【HUB CASABLANCA】'],
                    ['actionName' => 'Collected', 'time' => '2026-01-01 09:00', 'msgEng' => 'Parcel left 【SOME PLACE】', 'msgFre' => 'Colis 【DC-RABAT】'],
                ],
            ]],
        ]),
    ]);

    $result = $this->service->track('speedaf', 'MA0201');

    expect($result['error'])->toBeNull()
        ->and($result['events'][0])->toEqual(['step' => 'DC-RABAT', 'status' => 'Collected', 'date' => '2026-01-01 09:00'])
        ->and($result['events'][1]['step'])->toEqual('HUB CASABLANCA')
        ->and($result['info']['current_status'])->toEqual('Delivered')
        ->and($result['info']['destination'])->toEqual('HUB CASABLANCA');

    Http::assertSent(fn ($request) => $request->header('countryCode')[0] === 'MA');
});

test('speedaf tracking falls back to the default country code for numeric codes', function () {
    Http::fake([
        'speedaf.com/*' => Http::response(['success' => false]),
    ]);

    expect($this->service->track('speedaf', '12345')['error'])
        ->toEqual('Numéro de suivi invalide ou introuvable.');

    Http::assertSent(fn ($request) => $request->header('countryCode')[0] === 'MA');
});

test('speedaf location falls back to the carrier name when no hub is present', function () {
    Http::fake([
        'speedaf.com/*' => Http::response([
            'success' => true,
            'data' => [['tracks' => [['actionName' => 'Created', 'time' => '2026-01-01 09:00', 'msgEng' => 'no markers here']]]],
        ]),
    ]);

    expect($this->service->track('speedaf', 'MA1')['events'][0]['step'])->toEqual('Speedaf');
});

test('coliaty tracking reverses the timeline and formats the amount', function () {
    Http::fake([
        'cx-endpoint.coliaty.com/*' => Http::response([
            'success' => true,
            'data' => [
                'parcel_city' => 'Marrakech',
                'price' => 199,
                'current_status' => ['label' => 'Livré'],
                'timeline' => [
                    ['status_label' => 'Livré', 'date' => '2026-01-02 15:30'],
                    ['status_code' => 'CREATED', 'date' => '2026-01-01 09:00'],
                ],
            ],
        ]),
    ]);

    $result = $this->service->track('coliaty', 'COL-1');

    expect($result['error'])->toBeNull()
        ->and(array_column($result['events'], 'status'))->toEqual(['CREATED', 'Livré'])
        ->and($result['events'][0]['step'])->toEqual('Marrakech')
        ->and($result['info']['last_update'])->toEqual('2026-01-02 15:30')
        ->and($result['info']['current_status'])->toEqual('Livré')
        ->and($result['info']['amount'])->toEqual('199 MAD');
});

test('coliaty tracking errors when the timeline is missing', function () {
    Http::fake([
        'cx-endpoint.coliaty.com/*' => Http::response(['success' => true, 'data' => []]),
    ]);

    expect($this->service->track('coliaty', 'COL-1')['error'])
        ->toEqual('Numéro de suivi invalide ou introuvable.');
});

test('olivraison tracking skips internal statuses and labels known codes', function () {
    Http::fake([
        'www.olivraison.com/*' => Http::response([
            'data' => ['packageHistory' => ['history' => [
                ['status' => 'CREATED', 'updateAt' => 1767256200000],
                ['status' => 'OUTGOING_CALL', 'updateAt' => 1767256300000],
                ['status' => 'ENROUTE', 'updateAt' => 1767256400000, 'msg' => 'Colis en route vers Hub Temara'],
            ]]],
        ]),
    ]);

    $result = $this->service->track('olivraison', 'OLI-1');

    expect($result['error'])->toBeNull()
        ->and($result['events'])->toHaveCount(2)
        ->and(array_column($result['events'], 'status'))->toEqual(['Colis créé', 'En route'])
        ->and($result['events'][0]['step'])->toEqual('Olivraison')
        ->and($result['events'][1]['step'])->toEqual('Hub Temara')
        ->and($result['info']['destination'])->toEqual('Hub Temara')
        ->and($result['info']['current_status'])->toEqual('En route');
});

test('olivraison tracking errors when only skipped statuses are returned', function () {
    Http::fake([
        'www.olivraison.com/*' => Http::response([
            'data' => ['packageHistory' => ['history' => [['status' => 'whatsapp', 'updateAt' => 1767256200000]]]],
        ]),
    ]);

    expect($this->service->track('olivraison', 'OLI-1')['error'])
        ->toEqual('Numéro de suivi invalide ou introuvable.');
});

test('olivraison tracking errors when the history is empty', function () {
    Http::fake([
        'www.olivraison.com/*' => Http::response(['data' => []]),
    ]);

    expect($this->service->track('olivraison', 'OLI-1')['error'])
        ->toEqual('Numéro de suivi invalide ou introuvable.');
});

test('amana tracking parses the embedded timeline html in chronological order', function () {
    Http::fake([
        'bam-tracking.barid.ma/*' => Http::response([
            'OperationSuccess' => true,
            'Html' => <<<'HTML'
                <div class="lblCurrentPosition">Agence Rabat</div>
                <div class="lblMttCrbt">120 MAD</div>
                <ul class="timeline">
                    <li>
                        <span class="container_date">02/01/2026</span>
                        <span class="container_time">15:30</span>
                        <div class="mt-3 mb-5"><b>Rabat</b> Distribué</div>
                    </li>
                    <li>
                        <span class="container_date">01/01/2026</span>
                        <span class="container_time">09:00</span>
                        <div class="mt-3 mb-5">Prise en charge</div>
                    </li>
                </ul>
                HTML,
        ]),
    ]);

    $result = $this->service->track('amana', 'AMA-1');

    expect($result['error'])->toBeNull()
        ->and($result['events'])->toHaveCount(2)
        ->and($result['events'][0])->toEqual(['step' => 'Amana', 'status' => 'Prise en charge', 'date' => '01/01/2026 09:00'])
        ->and($result['events'][1])->toEqual(['step' => 'Rabat', 'status' => 'Distribué', 'date' => '02/01/2026 15:30'])
        ->and($result['info']['destination'])->toEqual('Agence Rabat')
        ->and($result['info']['amount'])->toEqual('120 MAD')
        ->and($result['info']['current_status'])->toEqual('Distribué');
});

test('amana tracking errors when the operation failed', function () {
    Http::fake([
        'bam-tracking.barid.ma/*' => Http::response(['OperationSuccess' => false]),
    ]);

    expect($this->service->track('amana', 'AMA-1')['error'])
        ->toEqual('Numéro de suivi invalide ou introuvable.');
});

test('tawssil tracking parses event cards', function () {
    Http::fake([
        'tracking.tawssil.ma/*' => Http::response(<<<'HTML'
            <div class="rounded-xl"><h3>Casablanca</h3><h3>Colis livré</h3><span>2026-01-02 15:30</span></div>
            <div class="rounded-xl"><h3>Rabat</h3><h3>Colis ramassé</h3><span>2026-01-01 09:00</span></div>
            HTML),
    ]);

    $result = $this->service->track('tawsil', 'TAW-1');

    expect($result['error'])->toBeNull()
        ->and($result['events'])->toHaveCount(2)
        ->and($result['events'][0])->toEqual(['step' => 'Casablanca', 'status' => 'Colis livré', 'date' => '2026-01-02 15:30'])
        ->and($result['info']['receipt'])->toEqual('TAW-1')
        ->and($result['info']['current_status'])->toEqual('Colis livré');
});

test('tawssil tracking errors when only the search form is returned', function () {
    Http::fake([
        'tracking.tawssil.ma/*' => Http::response('<form action="/parcel/tracking/api/"></form>'),
    ]);

    expect($this->service->track('tawsil', 'TAW-1')['error'])
        ->toEqual('Numéro de suivi invalide ou introuvable.');
});

test('ozon tracking parses info items and timeline steps', function () {
    Http::fake([
        'ozonexpress.ma/*' => Http::response(<<<'HTML'
            <div class="ctm-info-item"><div class="ctm-info-content"><h3>Récépissé</h3><p>OZN-9</p></div></div>
            <div class="ctm-info-item"><div class="ctm-info-content"><h3>Statut</h3><p>Livré</p></div></div>
            <div class="ctm-info-item"><div class="ctm-info-content"><h3>Dernière mise à jour</h3><p>2026-01-02 15:30</p></div></div>
            <div class="timeline-step"><div class="timeline-step-title">Colis livré</div><div class="timeline-step-date">2026-01-02 15:30</div></div>
            HTML),
    ]);

    $result = $this->service->track('ozon_express', 'OZN-1');

    expect($result['error'])->toBeNull()
        ->and($result['events'])->toEqual([['step' => 'Ozon Express', 'status' => 'Colis livré', 'date' => '2026-01-02 15:30']])
        ->and($result['info']['receipt'])->toEqual('OZN-9')
        ->and($result['info']['current_status'])->toEqual('Livré')
        ->and($result['info']['last_update'])->toEqual('2026-01-02 15:30');
});

test('ozon tracking errors when the page reports the parcel is not found', function () {
    Http::fake([
        'ozonexpress.ma/*' => Http::response('<div class="ctm-info-item">Colis introuvable</div>'),
    ]);

    expect($this->service->track('ozon_express', 'OZN-1')['error'])
        ->toEqual('Numéro de suivi invalide ou introuvable.');
});

test('sendit tracking parses status, destination, customer info and events', function () {
    Http::fake([
        'app.sendit.ma/*' => Http::response(<<<'HTML'
            <div class="code-delivery">Code : SND-9</div>
            <div class="section-status"><div class="col-status"><h5>Livré</h5></div></div>
            <div class="section-city"><div class="timeline-title">Casablanca</div><div class="timeline-title">Rabat</div></div>
            <div class="section-infos-customer">N° Téléphone : 0600000000 Montant : 250 MAD</div>
            <div class="tracking-delivery-details">
                <div class="timeline-item"><div class="timeline-title">Colis ramassé</div><div class="timeline-date">2026-01-01 09:00</div></div>
            </div>
            HTML),
    ]);

    $result = $this->service->track('sendit', 'SND-1');

    expect($result['error'])->toBeNull()
        ->and($result['events'])->toEqual([['step' => 'Sendit', 'status' => 'Colis ramassé', 'date' => '2026-01-01 09:00']])
        ->and($result['info']['receipt'])->toEqual('SND-9')
        ->and($result['info']['current_status'])->toEqual('Livré')
        ->and($result['info']['destination'])->toEqual('Casablanca → Rabat')
        ->and($result['info']['phone'])->toEqual('0600000000')
        ->and($result['info']['amount'])->toEqual('250 MAD');
});

test('sendit tracking falls back to the current status when no timeline items exist', function () {
    Http::fake([
        'app.sendit.ma/*' => Http::response(<<<'HTML'
            <div class="section-status"><div class="col-status"><h5>En cours</h5></div></div>
            <div class="section-city"><div class="timeline-title">Fès</div></div>
            HTML),
    ]);

    $result = $this->service->track('sendit', 'SND-1');

    expect($result['events'])->toEqual([['step' => 'Fès', 'status' => 'En cours', 'date' => '']]);
});

test('sendit tracking errors on an empty page', function () {
    Http::fake([
        'app.sendit.ma/*' => Http::response('<html><body></body></html>'),
    ]);

    expect($this->service->track('sendit', 'SND-1')['error'])
        ->toEqual('Numéro de suivi invalide ou introuvable.');
});

test('digylog tracking extracts the form nonce and parses the results table', function () {
    Http::fake([
        'www.digylog.com/*' => Http::sequence()
            ->push('<input name="fusion-form-nonce-42" value="nonce-value" />')
            ->push('<table><tbody><tr><td>02/01/2026</td><td>Colis livré</td><td>Casablanca</td></tr></tbody></table>'),
    ]);

    $result = $this->service->track('digylog', 'DIG-1');

    expect($result['error'])->toBeNull()
        ->and($result['events'])->toEqual([['step' => 'Casablanca', 'status' => 'Colis livré', 'date' => '02/01/2026']])
        ->and($result['info']['receipt'])->toEqual('DIG-1')
        ->and($result['info']['destination'])->toEqual('Casablanca');
});

test('digylog tracking reports the service as unavailable when the nonce is missing', function () {
    Http::fake([
        'www.digylog.com/*' => Http::response('<html><body>no form here</body></html>'),
    ]);

    expect($this->service->track('digylog', 'DIG-1')['error'])
        ->toEqual('Service Digylog indisponible.');
});

test('digylog tracking errors when the results table has no rows', function () {
    Http::fake([
        'www.digylog.com/*' => Http::sequence()
            ->push('<input name="fusion-form-nonce-42" value="nonce-value" />')
            ->push('<table><tbody></tbody></table>'),
    ]);

    expect($this->service->track('digylog', 'DIG-1')['error'])
        ->toEqual('Numéro de suivi invalide ou introuvable.');
});
