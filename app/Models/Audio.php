<?php

namespace App\Models;

use Database\Factories\AudioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'path', 'mode_id'])]
class Audio extends Model
{
    /** @use HasFactory<AudioFactory> */
    use HasFactory;

    protected $table = 'audios';

    /**
     * @return BelongsTo<Mode, $this>
     */
    public function mode(): BelongsTo
    {
        return $this->belongsTo(Mode::class);
    }
}
