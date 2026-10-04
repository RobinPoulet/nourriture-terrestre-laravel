<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $message
 * @property bool $is_visible
 */
#[Table('announcements', timestamps: false)]
#[Fillable(['message', 'is_visible'])]
class Announcement extends Model
{
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    /**
     * Uniquement les annonces visibles
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_visible', 1);
    }
}
