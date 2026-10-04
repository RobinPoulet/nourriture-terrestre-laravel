<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $message
 * @property string $destination
 * @property ?int $sms_batch_id
 * @property ?string $status
 * @property ?int $menu_id
 * @property Carbon $created_at
 */
#[Table('sms_responses', timestamps: false)]
#[Fillable(['message', 'destination', 'sms_batch_id', 'status', 'menu_id'])]
class SmsResponse extends Model
{
    public const string STATUS_SUCCESS = 'success';

    public const string STATUS_ERROR = 'error';

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Message affiché sur la page des commandes (null si aucun statut connu)
     *
     * @return ?array{status: string, message: string}
     */
    public function summary(): ?array
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => [
                'status' => self::STATUS_SUCCESS,
                'message' => 'Envoyé avec succès à '.$this->created_at?->format('H:i'),
            ],
            self::STATUS_ERROR => ['status' => self::STATUS_ERROR, 'message' => "Echec de l'envoi"],
            default => null,
        };
    }
}
