<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class KKIKUAchievementData extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /*
    | -----------------------------------------------------------------
    | VARIABLES
    | -----------------------------------------------------------------
    */

    protected $table = 'kk_iku_achievement_data';

    protected $fillable = [
        'value',
        'column_index',
        'kk_iku_achievement_id',
        'column_id',
    ];


    /*
    | -----------------------------------------------------------------
    | RELATION - BELONGSTO
    | -----------------------------------------------------------------
    */

    public function kkIKUAchievement(): BelongsTo
    {
        return $this->belongsTo(KKIKUAchievement::class);
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(IKPColumn::class);
    }
}