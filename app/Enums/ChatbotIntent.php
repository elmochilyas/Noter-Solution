<?php

namespace App\Enums;

enum ChatbotIntent: string
{
    case GREETING = 'greeting';
    case FAQ_QUERY = 'faq_query';
    case BOOKING_INTENT = 'booking_intent';
    case PRICING_QUERY = 'pricing_query';
    case ESCALATION = 'escalation';
    case OUT_OF_SCOPE = 'out_of_scope';
}
