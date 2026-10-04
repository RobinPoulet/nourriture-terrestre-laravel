<?php

namespace App\Models;

use App\Enums\SettingKey;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $setting_key
 * @property string $setting_value
 */
#[Table('settings')]
#[Fillable(['setting_key', 'setting_value'])]
class Setting extends Model
{
    /**
     * Lire une valeur de configuration.
     */
    public static function getValue(SettingKey $key): string
    {
        $value = static::query()->where('setting_key', $key->value)->value('setting_value');

        return $value !== null ? (string) $value : $key->default();
    }

    /**
     * Le paramètre (case à cocher) est-il activé ?
     */
    public static function isEnabled(SettingKey $key): bool
    {
        return static::getValue($key) === '1';
    }

    /**
     * Écrire / mettre à jour une valeur de configuration.
     */
    public static function setValue(SettingKey $key, string $value): void
    {
        static::query()->updateOrCreate(['setting_key' => $key->value], ['setting_value' => $value]);
    }
}
