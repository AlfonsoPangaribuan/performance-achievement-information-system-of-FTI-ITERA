<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class KK extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /*
    | -----------------------------------------------------------------
    | VARIABLES
    | -----------------------------------------------------------------
    */

    protected $table = 'kks';

    protected $fillable = [
        'short_name',
        'name',
        'unit_id',
    ];


    /*
    | -----------------------------------------------------------------
    | RELATION - BELONGSTO
    | -----------------------------------------------------------------
    */

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }


    /*
    | -----------------------------------------------------------------
    | RELATION - HASMANY
    | -----------------------------------------------------------------
    */

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function kkRencanaStrategis(): HasMany
    {
        return $this->hasMany(KKRSAchievement::class);
    }

    public function kkIndikatorKinerjaUtama(): HasMany
    {
        return $this->hasMany(KKIKUAchievement::class);
    }

    public function kkSingleIndikatorKinerjaUtama(): HasMany
    {
        return $this->hasMany(KKIKUSingleAchievement::class);
    }
}