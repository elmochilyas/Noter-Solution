<?php

return [
    'hello' => 'Bonjour',
    'regards' => 'Cordialement',
    'signature' => 'Cabinet de Maître Sana Bouhamidi',
    'booking_confirmed' => [
        'subject' => 'Confirmation de votre rendez-vous',
        'line1' => 'Votre rendez-vous a été confirmé. Voici un récapitulatif :',
        'reference' => 'Référence',
        'date' => 'Date et heure',
        'format' => 'Format',
        'next_steps' => 'Que faire ensuite ?',
        'video_note' => 'Le lien de visioconférence vous sera envoyé 1 heure avant le rendez-vous.',
        'office_address' => 'Adresse du cabinet : [Adresse à compléter]',
        'portal_cta' => 'Accéder à mon espace client',
        'sms' => 'Confirmation rdv n°:reference le :date - Cabinet Sana Bouhamidi :url',
        'whatsapp' => 'Votre rendez-vous n°:reference est confirmé pour le :date. Suivi : :url - Cabinet Sana Bouhamidi',
    ],
    'booking_reminder_24h' => [
        'subject' => 'Rappel : votre rendez-vous de demain',
        'lines' => [
            'Ceci est un rappel amical pour votre rendez-vous de demain.',
            'Nous vous attendons au cabinet à l\'heure convenue.',
        ],
        'sms' => 'Rappel: rdv n°:reference demain :date - Cabinet Sana Bouhamidi :url',
        'whatsapp' => 'Rappel: votre rendez-vous n°:reference est prévu demain :date. Détails : :url',
    ],
    'booking_reminder_1h' => [
        'subject' => 'Rappel : votre rendez-vous dans 1 heure',
        'lines' => [
            'Votre rendez-vous commence dans une heure.',
            'Merci de confirmer votre présence.',
        ],
        'sms' => 'Rappel: rdv n°:reference dans 1h - Cabinet Sana Bouhamidi :url',
        'whatsapp' => 'Rappel: votre rendez-vous n°:reference commence dans 1 heure. Rejoindre : :url',
    ],
    'booking_cancelled' => [
        'subject' => 'Annulation de votre rendez-vous',
        'line1' => 'Votre rendez-vous a été annulé.',
        'rescheduled_note' => 'Un nouveau rendez-vous a été programmé. Vous recevrez une confirmation séparée.',
        'sms' => 'Annulation rdv n°:reference - Cabinet Sana Bouhamidi :url',
        'whatsapp' => 'Votre rendez-vous n°:reference a été annulé. Détails : :url',
    ],
    'booking_rescheduled' => [
        'subject' => 'Votre rendez-vous a été reprogrammé',
        'line1' => 'Votre rendez-vous a été reprogrammé. Voici les nouveaux détails :',
        'sms' => 'Report: n°:new_ref le :date - Cabinet Sana Bouhamidi :url',
        'whatsapp' => 'Votre rendez-vous a été reprogrammé. Nouveau n°:new_ref le :date. Suivi : :url',
    ],
    'payment_receipt' => [
        'subject' => 'Votre reçu de paiement',
        'line1' => 'Votre paiement a été confirmé. Vous trouverez ci-dessous les détails de votre reçu.',
        'number' => 'Reçu n°',
        'amount' => 'Montant',
        'issued_at' => 'Date d\'émission :',
        'description' => 'Description',
        'qty' => 'Qté',
        'vat_exempt_note' => 'Opération exonérée de TVA selon l\'article 92-I-6° du CGI.',
        'payment_method' => 'Mode de paiement',
        'payment_date' => 'Date de paiement :',
        'attachment_note' => 'Le reçu PDF est disponible en pièce jointe.',
        'sms' => 'Reçu n°:number de :amount MAD disponible :url - Cabinet Sana Bouhamidi',
        'whatsapp' => 'Votre reçu n°:number d\'un montant de :amount MAD est disponible :url',
    ],
    'payment_failed' => [
        'subject' => 'Échec du paiement',
        'line1' => 'Le paiement de votre rendez-vous a échoué.',
        'line2' => 'Veuillez réessayer avec une autre carte ou nous contacter pour d\'autres options de paiement.',
        'sms' => 'Paiement échoué pour rdv n°:reference - Cabinet Sana Bouhamidi :url',
        'whatsapp' => 'Le paiement de votre rendez-vous n°:reference a échoué. Détails : :url',
    ],
    'refund_issued' => [
        'subject' => 'Remboursement effectué',
        'line1' => 'Votre remboursement a été traité.',
        'amount' => 'Montant remboursé',
        'method' => 'Mode de remboursement',
        'card' => 'Carte bancaire',
        'cash' => 'Espèces',
        'reason' => 'Motif du remboursement',
        'delay_note' => 'Le délai de réception dépend de votre banque (généralement 3 à 10 jours ouvrés).',
        'sms' => 'Remboursement de :amount MAD effectué :url - Cabinet Sana Bouhamidi',
        'whatsapp' => 'Votre remboursement de :amount MAD a été traité :url',
    ],

    'magic_link' => [
        'subject' => 'Connexion à votre espace client',
        'line1' => 'Cliquez sur le bouton ci-dessous pour vous connecter à votre espace client.',
        'button' => 'Se connecter',
        'expiry' => 'Ce lien expire dans 15 minutes. Si vous n\'avez pas demandé cette connexion, ignorez cet e-mail.',
    ],

    'magic_link_flash_in_app' => [
        'line1' => 'Cliquez ici pour vous connecter instantanément à votre espace client.',
    ],

    'new_appointment_link' => [
        'subject' => 'Un nouveau rendez-vous a été créé pour vous',
        'line1' => 'Un rendez-vous a été créé pour vous par notre cabinet. Vous trouverez ci-dessous les détails.',
        'button' => 'Accéder à mon espace client',
    ],
];
