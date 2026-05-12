<?php

namespace App\Models;

use Database\Factories\ModeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'color'])]
class Mode extends Model
{
    /** @use HasFactory<ModeFactory> */
    use HasFactory;
}
