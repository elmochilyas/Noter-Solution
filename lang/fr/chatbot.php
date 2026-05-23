<?php

return [
    'system_prompt' => "Vous êtes l'assistant virtuel du cabinet de Maître Sana Bouhamidi, adoula (notaire) à Agadir, Maroc.

Règles :
- Répondez UNIQUEMENT aux questions concernant les actes notariaux au Maroc et les services du cabinet.
- Répondez dans la même langue que l'utilisateur (français ou arabe).
- Réponses courtes (2-4 phrases). Pas de markdown.
- Ne donnez JAMAIS de conseil juridique. Informations générales uniquement.
- Ne citez PAS de frais qui ne sont pas dans le contexte fourni.
- Pas de superlatifs ('le meilleur', 'le plus rapide').
- Si vous ne savez pas, proposez de prendre rendez-vous.
- Terminez chaque réponse substantielle par : 'Pour plus d'informations, n'hésitez pas à prendre rendez-vous.'",

    'system_prompt_stricter' => "Vous êtes l'assistant virtuel du cabinet de Maître Sana Bouhamidi, adoula (notaire) à Agadir, Maroc. RÈGLES STRICTES :

- RÉPONSES TRÈS COURTES (1-2 phrases maximum).
- N'UTILISEZ SURTOUT PAS de superlatifs ('le meilleur', 'le plus rapide', etc.).
- N'UTILISEZ SURTOUT PAS de termes comme 'conseil juridique' ou 'je suis avocat'.
- NE CITEZ AUCUN FRAIS ni montant en MAD/DH.
- Répondez dans la même langue que l'utilisateur.
- Si vous ne savez pas, dites simplement : 'Je vous invite à prendre rendez-vous au 05 28 38 07 19.'",

    'toggle_button' => 'Ouvrir le chat',
    'dialog_label' => 'Assistant virtuel',
    'close_button' => 'Fermer',
    'input_label' => 'Votre message',
    'input_placeholder' => 'Tapez votre question...',
    'send_button' => 'Envoyer',
    'typing_indicator' => 'Réflexion en cours...',

    'header_title' => 'Maître Bouhamidi',
    'header_subtitle' => 'Assistant virtuel',

    'disclaimer_title' => 'Bienvenue 👋',
    'disclaimer_text' => 'Je peux vous aider à comprendre les démarches notariales ou à choisir le bon rendez-vous.',
    'disclaimer_privacy' => 'Les conversations sont traitées via Cerebras (États-Unis) et conservées pour l\'amélioration du service. Pas de conseil juridique.',
    'disclaimer_accept' => 'Démarrer la conversation',

    'initial_message' => 'Bonjour ! Comment puis-je vous aider ?',
    'greeting_response' => 'Bonjour ! Je suis l\'assistant virtuel de Maître Bouhamidi. Comment puis-je vous aider avec vos démarches notariales ?',

    'empty_message' => 'Veuillez écrire un message.',
    'error_fallback' => 'Désolé, le service est momentanément indisponible.',
    'error_fallback_contact' => 'Désolé, le service est lent en ce moment. Veuillez réessayer ou nous contacter au 05 28 38 07 19.',
    'service_unavailable' => 'Le service est temporairement indisponible. Veuillez nous contacter au 05 28 38 07 19.',
    'footer_note' => 'Réponses générées par IA. Vérifiez auprès du cabinet.',

    'suggestion_documents' => 'Quels documents pour un mariage ?',
    'suggestion_booking' => 'Prendre un rendez-vous',
    'suggestion_pricing' => 'Combien ça coûte ?',
    'suggestion_more_details' => 'Plus de détails',
    'suggestion_speak_to_human' => 'Parler à quelqu\'un',

    'escalation_message' => "Je vous mets en relation avec Maître Bouhamidi.\n\n📞 {phone}\n💬 WhatsApp : {whatsapp_link}\n📅 {booking_link}",

    'triage_category_question' => "Pour mieux vous orienter, j'ai quelques questions rapides.\n\nDe quoi s'agit-il ?\n• Famille\n• Immobilier\n• Financier\n• Contrats\n• Autre",
    'triage_invalid_category' => 'Veuillez choisir parmi : Famille, Immobilier, Financier, Contrats ou Autre.',
    'triage_documents_question' => 'Avez-vous déjà tous vos documents ? (oui/non)',
    'triage_format_question' => 'Préférez-vous en personne ou en vidéo ? (en personne / vidéo / indifférent)',
    'triage_invalid_format' => 'Veuillez choisir : en personne, vidéo ou indifférent.',
    'triage_urgency_question' => 'Quelle est votre urgence ? (cette semaine / ce mois / flexible)',
    'triage_invalid_urgency' => 'Veuillez choisir : cette semaine, ce mois ou flexible.',

    'recommendation_header' => '📋 Voici ma recommandation :',
    'recommendation_category' => 'Catégorie : {category}',
    'recommendation_format' => 'Format : {format}',
    'recommendation_book_button' => 'Réserver ce créneau',

    'category_family' => 'Famille',
    'category_real_estate' => 'Immobilier',
    'category_financial' => 'Financier',
    'category_contracts' => 'Contrats',
    'category_other' => 'Autre',

    'format_video' => 'En visio',
    'format_in_person' => 'Au cabinet',

    'escalation_suggestion' => 'Souhaitez-vous que je vous mette en relation avec le cabinet ? Contactez-nous au 05 28 38 07 19.',

    'out_of_scope' => 'Je ne peux répondre qu\'aux questions concernant le cabinet de Maître Sana Bouhamidi et les actes notariaux. Pour une consultance juridique générale, veuillez contacter le barreau d\'Agadir.',

    'language_switch_fr' => 'Je remarque que vous écrivez en français. Je vous répondrai en français.',
    'language_switch_ar' => 'ألاحظ أنك تكتب بالعربية. سأجيبك بالعربية.',

    'rate_limit_exceeded' => 'Vous avez atteint la limite de messages. Veuillez réessayer dans une heure ou nous contacter au 05 28 38 07 19.',
];
