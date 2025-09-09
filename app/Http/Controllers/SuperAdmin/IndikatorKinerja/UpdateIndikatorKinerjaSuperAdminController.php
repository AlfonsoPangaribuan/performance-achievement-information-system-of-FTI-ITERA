<?php

namespace App\Http\Controllers\SuperAdmin\IndikatorKinerja;

use App\Http\Requests\IndikatorKinerja\EditRequest;
use App\Http\Controllers\_ControllerHelpers;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\IndikatorKinerja;
use App\Models\SasaranStrategis;
use Illuminate\Support\Carbon;
use App\Models\Kegiatan;
use App\Models\Unit;

class UpdateIndikatorKinerjaSuperAdminController extends Controller
{
    /**
     * @param \App\Models\SasaranStrategis $ss
     * @param \App\Models\Kegiatan $k
     * @param \App\Models\IndikatorKinerja $ik
     * @return Factory|View
     */
    public function view(SasaranStrategis $ss, Kegiatan $k, IndikatorKinerja $ik): Factory|View
    {
        if ($ss->id !== $k->sasaranStrategis->id || $k->id !== $ik->kegiatan->id) {
            abort(404);
        }

        $count = $k->indikatorKinerja->count();

        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[$i] = [
                "value" => strval($i + 1),
                "text" => strval($i + 1),
            ];
        }
        $data[$ik->number - 1] = [
            ...$data[$ik->number - 1],
            'selected' => true,
        ];

        $previousRoute = route('super-admin-rs-ik', ['ss' => $ss->id, 'k' => $k->id]);
        $current = true;
        if ($ss->time->year !== Carbon::now()->format('Y')) {
            $previousRoute = route('super-admin-achievement-rs', ['year' => $ss->time->year]);
            $current = false;
        }

        $type = [['value' => $ik->type, 'text' => ucfirst($ik->type)]];

        $units = [
            [
                'value' => '',
                'text' => 'Pilih Unit'
            ],
            ...Unit::select([
                'name AS text',
                'id AS value',
            ])
                ->get()
                ->map(function ($unit) use ($ik): array {
                    $data = $unit->toArray();
                    if (is_array($ik->unit_id) && in_array($unit->value, $ik->unit_id)) {
                        $data['selected'] = true;
                    }
                    return $data;
                })
                ->toArray()
        ];

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
        $ik = $ik->only([
            'textSelections',
            'status',
            'name',
            'type',
            'id',
            'assigned_to_type',
            'unit_id',
        ]);

        return view('super-admin.rs.ik.edit', compact([
            'previousRoute',
            'current',
            'data',
            'type',
            'units',
            'ik',
            'ss',
            'k',
        ]));
    }

    /**
     * @param \App\Http\Requests\IndikatorKinerja\EditRequest $request
     * @param \App\Models\SasaranStrategis $ss
     * @param \App\Models\Kegiatan $k
     * @param \App\Models\IndikatorKinerja $ik
     * @return RedirectResponse
     */
    public function action(EditRequest $request, SasaranStrategis $ss, Kegiatan $k, IndikatorKinerja $ik): RedirectResponse
    {
        if ($ss->id !== $k->sasaranStrategis->id || $k->id !== $ik->kegiatan->id) {
            abort(404);
        }

        $number = (int) $request['number'];
        if ($number > $k->indikatorKinerja->count()) {
            return _ControllerHelpers::BackWithInputWithErrors(['number' => 'Nomor tidak sesuai dengan jumlah data']);
        }

        DB::beginTransaction();

        try {
            $currentNumber = $ik->number;
            if ($number !== $currentNumber) {
                $ik->number = $number;

                if ($number < $currentNumber) {
                    $k->indikatorKinerja()
                        ->where('number', '>=', $number)
                        ->where('number', '<', $currentNumber)
                        ->increment('number');
                } else {
                    $k->indikatorKinerja()
                        ->where('number', '<=', $number)
                        ->where('number', '>', $currentNumber)
                        ->decrement('number');
                }
            }

            $ik->name = $request['name'];

            // Handle assignment changes
            $oldAssignedToType = $ik->assigned_to_type;
            $newAssignedToType = $request['assigned_to_type'];

            if ($oldAssignedToType !== $newAssignedToType) {
                // Delete existing targets when changing assignment type
                $ik->target()->forceDelete();

                if ($newAssignedToType === 'admin') {
                    $ik->unit_id = null;
                    // Create targets for all units
                    $allUnits = Unit::pluck('id')->toArray();
                    foreach ($allUnits as $unitId) {
                        $ik->target()->create([
                            'unit_id' => $unitId,
                            'target' => 0,
                        ]);
                    }
                } elseif ($newAssignedToType === 'kk') {
                    $unitIds = $request['unit_id'] ?? [];
                    $ik->unit_id = array_filter($unitIds);
                    // Create targets for selected units
                    foreach ($ik->unit_id as $unitId) {
                        $ik->target()->create([
                            'unit_id' => $unitId,
                            'target' => 0,
                        ]);
                    }
                }
            } else {
                // Same assignment type, check if unit_id changed for KK
                if ($newAssignedToType === 'kk') {
                    $newUnitIds = $request['unit_id'] ?? [];
                    $newUnitIds = array_filter($newUnitIds);
                    $oldUnitIds = $ik->unit_id ?? [];

                    if ($oldUnitIds !== $newUnitIds) {
                        // Delete existing targets
                        $ik->target()->forceDelete();

                        // Update unit_id
                        $ik->unit_id = $newUnitIds;

                        // Create new targets
                        foreach ($newUnitIds as $unitId) {
                            $ik->target()->create([
                                'unit_id' => $unitId,
                                'target' => 0,
                            ]);
                        }
                    }
                }
            }

            $ik->assigned_to_type = $newAssignedToType;
            $ik->save();

            DB::commit();

            if ($ss->time->year === Carbon::now()->format('Y')) {
                return _ControllerHelpers::RedirectWithRoute('super-admin-rs-ik', ['ss' => $ss->id, 'k' => $k->id])->with('success', 'Berhasil memperbaharui indikator kinerja');
            }
            return _ControllerHelpers::RedirectWithRoute('super-admin-achievement-rs', [
                'year' => $ss->time->year
            ])->with('success', 'Berhasil memperbaharui indikator kinerja');
        } catch (\Exception $e) {
            DB::rollBack();

            return _ControllerHelpers::BackWithInputWithErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param \App\Models\SasaranStrategis $ss
     * @param \App\Models\Kegiatan $k
     * @param \App\Models\IndikatorKinerja $ik
     * @return RedirectResponse
     */
    public function statusToggle(SasaranStrategis $ss, Kegiatan $k, IndikatorKinerja $ik): RedirectResponse
    {
        if ($ss->id !== $k->sasaranStrategis->id && $k->id !== $ik->kegiatan->id) {
            abort(404);
        }

        $ss = SasaranStrategis::currentOrFail($k->sasaranStrategis->id);

        $ik->realization()->forceDelete();
        $ik->evaluation()->forceDelete();
        $ik->target()->forceDelete();

        $newStatus = $ik->status === 'aktif' ? 'tidak aktif' : 'aktif';

        $ik->status = $newStatus;
        $ik->save();

        return _ControllerHelpers::Back()->with('success', 'Berhasil memperbaharui status indikator kinerja');
    }
}
