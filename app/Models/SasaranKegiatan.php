<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SasaranKegiatan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /*
    | -----------------------------------------------------------------
    | VARIABLES
    | -----------------------------------------------------------------
    */

    protected $table = 'sasaran_kegiatan';

    protected $fillable = [
        'number',
        'name',
        'status',

        'time_id',
    ];


    /*
    | -----------------------------------------------------------------
    | RELATION - BELONGSTO
    | -----------------------------------------------------------------
    */

    public function time(): BelongsTo
    {
        return $this->belongsTo(IKUYear::class);
    }


    /*
    | -----------------------------------------------------------------
    | RELATION - HASMANY
    | -----------------------------------------------------------------
    */

    public function indikatorKinerjaKegiatan(): HasMany
    {
        return $this->hasMany(IndikatorKinerjaKegiatan::class);
    }


    /*
    | -----------------------------------------------------------------
    | FUNCTION
    | -----------------------------------------------------------------
    */

    static function currentOrFail($id): SasaranKegiatan
    {
        $ss = SasaranKegiatan::findOrFail($id);
        $year = Carbon::now()->format('Y');

        if ($ss->time->year === $year) {
            return $ss;
        }

        abort(404);
    }

    public function deleteOrTrashed(): void
    {
        foreach ($this->indikatorKinerjaKegiatan as $key => $ikk) {
            $ikk->deleteOrTrashed();
        }

        $this->time->sasaranKegiatan()
            ->where('number', '>', $this->number)
            ->decrement('number');

        $this->forceDelete();
    }
}
