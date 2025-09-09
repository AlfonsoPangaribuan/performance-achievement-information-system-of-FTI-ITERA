<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class KKRSTarget extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /*
    | -----------------------------------------------------------------
    | VARIABLES
    | -----------------------------------------------------------------
    */

    protected $table = 'kk_rs_targets';

    protected $fillable = [
        'target',
        'indikator_kinerja_id',
        'kk_id',
        'year_id',
    ];


    /*
    | -----------------------------------------------------------------
    | RELATION - BELONGSTO
    | -----------------------------------------------------------------
    */

    public function indikatorKinerja(): BelongsTo
    {
        return $this->belongsTo(IndikatorKinerja::class);
    }

    public function kk(): BelongsTo
    {
        return $this->belongsTo(KK::class);
    }

    public function year(): BelongsTo
    {
        return $this->belongsTo(RSYear::class);
    }
}