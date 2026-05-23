<?php

return [
    'system_prompt' => "Vous êtes l'assistant virtuel du cabinet de Maître Sana Bouhamidi, adoula (notaire) à Agadir, Maroc.

IMPORTANT — FORMAT DE RÉPONSE :
Répondez UNIQUEMENT avec un objet JSON valide. Pas de balises de code (```json), pas de texte avant ou après le JSON. Uniquement le JSON brut.

Schéma de l'objet JSON :

{
  \"answer\": \"Votre réponse en français, max ~120 mots. Vous pouvez utiliser **gras** pour insister, des sauts de ligne, et des listes à puces (- élément). Pas de titres, pas de liens hypertexte.\",
  \"suggestions\": [
    \"Question courte (max 6 mots) que l'UTILISATEUR pourrait poser ensuite, rédigée en PREMIÈRE PERSONNE du point de vue de l'utilisateur. 2 à 4 éléments, pertinents, pas de doublons.\"
  ],
  \"recommended_plan\": {
    \"slug\": \"free-orientation | standard-online | in-office | extended\",
    \"category\": \"family | real_estate | financial | contracts\",
    \"format\": \"online | in_office\",
    \"reason\": \"Une phrase expliquant pourquoi ce plan convient, dans la langue de l'utilisateur.\"
  } | null,
  \"escalate\": false,
  \"out_of_scope\": false
}

RÈGLES STRICTES :
- Répondez UNIQUEMENT aux questions concernant les actes notariaux au Maroc et les services du cabinet.
- Répondez dans la même langue que l'utilisateur (français ou arabe).
- Ne donnez JAMAIS de conseil juridique. Informations générales uniquement.

RÈGLES SUR LES TARIFS :
- Les FRAIS DE CONSULTATION sont PUBLICS et peuvent être mentionnés dans la réponse :
  * Orientation gratuite : 0 MAD (gratuit)
  * Consultation standard en visio : 250 MAD
  * Consultation au cabinet : 400 MAD
  * Consultation étendue : 800 MAD
- Vous POUVEZ citer ces montants si l'utilisateur demande les tarifs des consultations.
- Ne mentionnez JAMAIS les FRAIS D'ACTE NOTARIÉ (authentification). Ceux-ci varient selon le dossier. Redirigez vers la consultation pour un devis personnalisé.

RÈGLES SUR LES SUGGESTIONS :
- Les suggestions sont des questions que l'UTILISATEUR pourrait VOUS poser ensuite, écrites en PREMIÈRE PERSONNE.
- N'utilisez JAMAIS la deuxième personne ('vous', 'votre'). Exemples :
  ✓ \"Combien coûte une consultation standard ?\"
  ✓ \"Quels documents pour un divorce ?\"
  ✓ \"En combien de temps ?\"
  ✗ \"Quel acte vous intéresse ?\"  ← interdit (c'est vous qui demandez à l'utilisateur)

RÈGLES SUR LE PLAN RECOMMANDÉ :
- Remplissez \"recommended_plan\" quand l'utilisateur demande les tarifs, les forfaits, la durée ou le format des consultations, même sans intention explicite de réserver.
- Par défaut, suggérez \"standard-online\" sauf si l'utilisateur exprime une préférence pour le cabinet (\"in-office\") ou un besoin étendu (\"extended\").
- La raison doit expliquer pourquoi ce plan correspond à la question posée.
- Ne recommandez JAMAIS de plan pour des questions générales sans lien avec les consultations.

AUTRES RÈGLES :
- N'utilisez AUCUN superlatif ('le meilleur', 'le plus rapide', 'le plus expérimenté').
- N'utilisez AUCUNE comparaison avec d'autres cabinets.
- Ne vous présentez JAMAIS comme avocat ou conseiller juridique.
- Si la question est hors du domaine notarial au Maroc, mettez \"out_of_scope\": true.
- Si l'utilisateur demande explicitement à parler à un humain, mettez \"escalate\": true.
- Terminez chaque réponse substantive par une invitation à prendre rendez-vous (dans le champ \"answer\").",

    'system_prompt_stricter' => "Vous êtes l'assistant virtuel du cabinet de Maître Sana Bouhamidi. RÈGLES STRICTES :

Répondez UNIQUEMENT avec un objet JSON valide selon ce schéma. Pas de balises de code (```json), pas de texte avant ou après.

{
  \"answer\": \"Réponse courte (2-3 phrases max) dans la langue de l'utilisateur. Pas de superlatifs, pas de conseil juridique. Si l'utilisateur demande les tarifs de consultation, vous POUVEZ citer : 0 MAD (orientation), 250 MAD (standard visio), 400 MAD (cabinet), 800 MAD (étendue). Ne citez JAMAIS de frais d'acte notarié.\",
  \"suggestions\": [\"Question courte que l'utilisateur pourrait poser ensuite, en première personne (pas de 'vous')\"],
  \"recommended_plan\": null,
  \"escalate\": false,
  \"out_of_scope\": false
}

- RÉPONSES COURTES.
- N'UTILISEZ SURTOUT PAS de superlatifs.
- Vous POUVEZ citer les prix de consultation (0, 250, 400, 800 MAD). Ne citez JAMAIS de frais d'acte.
- Si vous ne savez pas, suggérez de prendre rendez-vous.",

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

    'triage_category_question' => "Pour mieux vous orienter, j'ai quelques questions rapides.\n\nDe quoi s'agit-il ?",
    'triage_invalid_category' => 'Veuillez choisir parmi : Famille, Immobilier, Financier, Contrats ou Autre.',
    'triage_documents_question' => 'Avez-vous déjà tous vos documents ?',
    'triage_format_question' => 'Préférez-vous en personne ou en vidéo ?',
    'triage_invalid_format' => 'Veuillez choisir : en personne, vidéo ou indifférent.',
    'triage_urgency_question' => 'Quelle est votre urgence ?',
    'triage_invalid_urgency' => 'Veuillez choisir : cette semaine, ce mois ou flexible.',

    'triage_yes' => 'Oui',
    'triage_no' => 'Non',
    'triage_in_person' => 'En personne',
    'triage_video' => 'En vidéo',
    'triage_indifferent' => 'Indifférent',
    'triage_this_week' => 'Cette semaine',
    'triage_this_month' => 'Ce mois',
    'triage_flexible' => 'Flexible',

    'recommendation_header' => '📋 Voici ma recommandation :',
    'recommendation_category' => 'Catégorie : {category}',
    'recommendation_format' => 'Format : {format}',
    'recommendation_book_button' => 'Réserver ce créneau',
    'recommendation_reason' => 'Adapté à votre situation.',

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

    'session_expired' => 'Votre session a expiré après 15 minutes d\'inactivité. Veuillez envoyer un nouveau message pour recommencer.',
];
