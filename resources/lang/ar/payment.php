<?php

return [
    'gateway' => [
        'stripe' => 'بطاقة بنكية',
        'cmi' => 'بطاقة بنكية (CMI)',
        'cash' => 'نقداً',
    ],

    'status' => [
        'pending' => 'قيد الانتظار',
        'succeeded' => 'تم الدفع',
        'failed' => 'فشل',
        'refunded' => 'مسترجع',
        'partially_refunded' => 'مسترجع جزئياً',
    ],
];
