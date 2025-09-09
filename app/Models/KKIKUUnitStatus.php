<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class KKIKUUnitStatus extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /*
    | -----------------------------------------------------------------
    | VARIABLES
    | -----------------------------------------------------------------
    */

    protected $table = 'kk_iku_unit_statuses';

    protected $fillable = [
        'is_active',
        'kk_id',
        'period_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    /*
    | -----------------------------------------------------------------
    | RELATION - BELONGSTO
    | -----------------------------------------------------------------
    */

    public function kk(): BelongsTo
    {
        return $this->belongsTo(KK::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(IKUPeriod::class);
    }
}