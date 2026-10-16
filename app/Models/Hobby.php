<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name'])]
class Hobby extends Model
{
    protected static function booted(): void
    {
        static::saving(function (Hobby $hobby): void {
            $hobby->name = self::normalizeName($hobby->name);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function normalizeName(string $name): string
    {
        return preg_replace('/\s+/', ' ', trim($name)) ?? '';
    }
}
