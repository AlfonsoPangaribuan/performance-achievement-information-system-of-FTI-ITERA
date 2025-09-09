<?php

namespace App\Http\Controllers\SuperAdmin\IndikatorKinerja;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\Factory;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use App\Models\SasaranStrategis;
use Illuminate\Http\Request;
use App\Models\Kegiatan;
use App\Models\Unit;

class HomeIndikatorKinerjaSuperAdminController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\SasaranStrategis $ss
     * @param \App\Models\Kegiatan $k
     * @return Factory|View
     */
    public function view(Request $request, SasaranStrategis $ss, Kegiatan $k): Factory|View
    {
        $searchQuery = $request->query('search');

        if ($ss->id !== $k->sasaranStrategis->id) {
            abort(404);
        }

        $user = auth()->user();

        $ss = SasaranStrategis::currentOrFail($ss->id);

        $data = $k->indikatorKinerja()
            ->select([
                'number',
                'status',
                'name',
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
                        'status',
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

        $ss = $ss->only([
            'number',
            'name',
            'id',
        ]);
        $k = $k->only([
            'number',
            'name',
            'id',
        ]);

        return view('super-admin.rs.ik.home', compact([
            'searchQuery',
            'data',
            'user',
            'ss',
            'k',
        ]));
    }
}
