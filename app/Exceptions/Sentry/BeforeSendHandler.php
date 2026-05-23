<?php

namespace App\Exceptions\Sentry;

use Sentry\Event;
use Sentry\EventHint;

class BeforeSendHandler
{
    public function __invoke(Event $event, ?EventHint $hint): ?Event
    {
        $tags = $event->getTags();

        if (isset($tags['route']) && str_starts_with((string) $tags['route'], 'filament.')) {
            return null;
        }

        $request = $event->getRequest();
        if ($request !== null) {
            $data = $request->getData() ?? [];

            $sensitiveKeys = [
                'password', 'password_confirmation', 'current_password',
                'card_number', 'cvc', 'cvv', 'expiry', 'cc_number',
                'national_id', 'cin', 'cnie',
                'token', 'api_token', 'access_token', 'secret',
                'phone', 'mobile', 'telephone',
                'magic_link_token', 'recovery_code',
                'two_factor_code', '2fa_code',
            ];

            foreach ($data as $key => $value) {
                if (in_array(strtolower((string) $key), $sensitiveKeys, true)) {
                    $data[$key] = '[REDACTED]';
                }
            }

            $event->setRequest($request->setData($data));
        }

        $event->setTags(array_merge(
            $event->getTags() ?? [],
            ['pii_scrubbed' => 'true']
        ));

        return $event;
    }
}
