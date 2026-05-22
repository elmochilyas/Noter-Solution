<?php

use App\Models\ConsultationPlan;
use Laravel\Dusk\Browser;

uses(Browser::class)->group('dusk', 'booking');

beforeEach(function () {
    $this->plan = ConsultationPlan::factory()->create(['price_centimes' => 50000]);
});

it('completes a card payment from /book to success page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/fr/book?plan='.$this->plan->id)
            ->waitForText('Confirmer')
            ->press('Confirmer')
            ->waitForText('Vos informations')
            ->type('full_name', 'Ahmed Benani')
            ->type('email', 'ahmed@example.com')
            ->type('phone', '+212612345678')
            ->check('accept_terms')
            ->press('Continuer')
            ->waitForText('Paiement')
            ->type('cardnumber', '4242424242424242')
            ->type('exp-date', '12/30')
            ->type('cvc', '123')
            ->press('Payer')
            ->waitForText('Confirmation')
            ->assertSee('Référence');
    });
});

it('shows error on declined card', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/fr/book?plan='.$this->plan->id)
            ->waitForText('Confirmer')
            ->press('Confirmer')
            ->waitForText('Vos informations')
            ->type('full_name', 'Fatima Alaoui')
            ->type('email', 'fatima@example.com')
            ->type('phone', '+212612345679')
            ->check('accept_terms')
            ->press('Continuer')
            ->waitForText('Paiement')
            ->type('cardnumber', '4000000000000002')
            ->type('exp-date', '12/30')
            ->type('cvc', '123')
            ->press('Payer')
            ->waitForText('échec|déclinée|refusé')
            ->assertSee('échec');
    });
});
