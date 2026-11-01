<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('privacy policy page shows the October 2026 version', function () {
    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee('Politique de confidentialité.')
        ->assertSee('26 octobre 2026')
        ->assertSee('loi marocaine n° 09-08', false)
        ->assertSee('Continuer avec Google')
        ->assertSee('contact@usedis.com')
        // Drafting notes addressed to the company must not be published.
        ->assertDontSee('avant publication')
        ->assertDontSee('doivent être définies et documentées');
});

test('privacy policy is reachable at its public url', function () {
    expect(route('privacy', absolute: false))->toBe('/politique-de-confidentialite');
});

test('cgu page shows the new usedis version', function () {
    $this->get(route('cgu'))
        ->assertOk()
        ->assertSee('Article 4 / Garantie « Usedis Protection »', false)
        ->assertSee('3.8 Usedis Luxury')
        ->assertSee('www.usedis.com')
        ->assertSee(route('privacy'))
        ->assertDontSee('used.ma');
});

test('faq page shows the new usedis version', function () {
    $this->get(route('faq'))
        ->assertOk()
        ->assertSee("1. Qu'est-ce que Usedis ?", false)
        ->assertSee('20. Comment recevoir mon argent ?')
        ->assertSee('contact@usedis.com')
        ->assertDontSee('used.ma');
});

test('footer links to the privacy policy', function () {
    $this->get(route('faq'))->assertSee(route('privacy'));
});

test('signup popup links to the cgu and privacy policy', function () {
    \Livewire\Livewire::test(\App\Livewire\LoginPopup::class)
        ->call('openPopup')
        ->call('setView', 'register')
        ->assertSeeHtml('href="'.route('cgu').'"')
        ->assertSeeHtml('href="'.route('privacy').'"');
});
