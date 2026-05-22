<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        $value = $setting->value;

        if (json_validate($value)) {
            return json_decode($value, true);
        }

        return $value;
    }

    public static function set(string $key, mixed $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value],
        );
    }

    public static function practiceInfo(): array
    {
        return static::get('practice_info', [
            'ice' => '',
            'if' => '',
            'rc' => '',
            'patente' => '',
            'phone' => '',
            'mobile' => '',
            'whatsapp' => '',
            'email' => '',
            'address' => '',
            'hours_fr' => 'Lun–Ven 09h–17h',
            'hours_ar' => 'الإثنين–الجمعة 09–17',
        ]);
    }
}
