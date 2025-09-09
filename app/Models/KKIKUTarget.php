<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class KKIKUTarget extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /*
    | -----------------------------------------------------------------
    | VARIABLES
    | -----------------------------------------------------------------
    */

    protected $table = 'kk_iku_targets';

    protected $fillable = [
        'target',
        'indikator_kinerja_program_id',
        'kk_id',
        'year_id',
    ];


    /*
    | -----------------------------------------------------------------
    | RELATION - BELONGSTO
    | -----------------------------------------------------------------
    */

    public function indikatorKinerjaProgram(): BelongsTo
    {
        return $this->belongsTo(IndikatorKinerjaProgram::class);
    }

    public function kk(): BelongsTo
    {
        return $this->belongsTo(KK::class);
    }

    public function year(): BelongsTo
    {
        return $this->belongsTo(IKUYear::class);
    }
}