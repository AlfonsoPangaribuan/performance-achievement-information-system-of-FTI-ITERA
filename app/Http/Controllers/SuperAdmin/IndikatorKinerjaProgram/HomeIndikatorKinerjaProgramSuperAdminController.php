<?php

namespace App\Http\Controllers\SuperAdmin\IndikatorKinerjaProgram;

use Illuminate\Database\Eloquent\Builder;
use App\Models\IndikatorKinerjaKegiatan;
use Illuminate\Contracts\View\Factory;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use App\Models\ProgramStrategis;
use App\Models\SasaranKegiatan;
use Illuminate\Http\Request;
use App\Models\Unit;

class HomeIndikatorKinerjaProgramSuperAdminController extends Controller
{
    public const TYPES = [
        [
            'value' => 'iku',
            'text' => 'IKU',
        ],
        [
            'value' => 'ikt',
            'text' => 'IKT',
        ],
    ];

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\SasaranKegiatan $sk
     * @param \App\Models\IndikatorKinerjaKegiatan $ikk
     * @param \App\Models\ProgramStrategis $ps
     * @return Factory|View
     */
    public function view(Request $request, SasaranKegiatan $sk, IndikatorKinerjaKegiatan $ikk, ProgramStrategis $ps): Factory|View
    {
        $searchQuery = $request->query('search');

        if ($sk->id !== $ikk->sasaranKegiatan->id || $ikk->id !== $ps->indikatorKinerjaKegiatan->id) {
            abort(404);
        }

        $user = auth()->user();

        $sk = SasaranKegiatan::currentOrFail($sk->id);

        $data = $ps->indikatorKinerjaProgram()
            ->select([
                'definition',
                'number',
                'status',
                'name',
                'mode',
                'type',
                'id',
                'assigned_to_type',
                'unit_id',
            ]);

        // Filter for KK users - only show data assigned to their unit or admin data
        if ($user->role === 'kk') {
            $data->where(function (Builder $query) use ($user) {
                $query->where('assigned_to_type', 'admin')
                      ->orWhere(function (Builder $q) use ($user) {
                          $q->where('assigned_to_type', 'kk')
                            ->whereJsonContains('unit_id', $user->unit_id);
                      });
            });
        }

        $data->when(
            $searchQuery !== null,
            function (Builder $query) use ($searchQuery): Builder {
                return $query->whereAny(
                    [
                        'definition',
                        'status',
                        'mode',
                        'name',
                        'type',
                    ],
                    'LIKE',
                    "%$searchQuery%"
                );
            }
        );

        $data = $data->orderBy('number')->get();

        // Get units data for display
        $units = Unit::pluck('name', 'id')->toArray();

        // Add units data to each item for display
        $data = $data->map(function ($item) use ($units) {
            $itemArray = $item->toArray();
            $itemArray['units'] = $units;
            return $itemArray;
        })->toArray();

        $sk = $sk->only([
            'number',
            'name',
            'id',
        ]);
        $ikk = $ikk->only([
            'number',
            'name',
            'id',
        ]);
        $ps = $ps->only([
            'number',
            'name',
            'id',
        ]);

        return view('super-admin.iku.ikp.home', compact([
            'searchQuery',
            'data',
            'user',
            'ikk',
            'ps',
            'sk',
        ]));
    }
}
