<?php

namespace App\Http\Controllers\SuperAdmin\IndikatorKinerjaProgram;

use App\Http\Requests\IndikatorKinerjaProgram\EditRequest;
use App\Http\Controllers\_ControllerHelpers;
use App\Models\IndikatorKinerjaKegiatan;
use App\Models\IndikatorKinerjaProgram;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\ProgramStrategis;
use App\Models\SasaranKegiatan;
use Illuminate\Support\Carbon;
use App\Models\Unit;

class UpdateIndikatorKinerjaProgramSuperAdminController extends Controller
{
    protected $types = HomeIndikatorKinerjaProgramSuperAdminController::TYPES;

    /**
     * @param \App\Models\SasaranKegiatan $sk
     * @param \App\Models\IndikatorKinerjaKegiatan $ikk
     * @param \App\Models\ProgramStrategis $ps
     * @param \App\Models\IndikatorKinerjaProgram $ikp
     * @return Factory|View
     */
    public function view(SasaranKegiatan $sk, IndikatorKinerjaKegiatan $ikk, ProgramStrategis $ps, IndikatorKinerjaProgram $ikp): Factory|View
    {
        if ($sk->id !== $ikk->sasaranKegiatan->id || $ikk->id !== $ps->indikatorKinerjaKegiatan->id || $ps->id !== $ikp->programStrategis->id) {
            abort(404);
        }

        $count = $ps->indikatorKinerjaProgram->count();
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[$i] = [
                "value" => strval($i + 1),
                "text" => strval($i + 1),
            ];
        }
        $data[$ikp->number - 1] = [
            ...$data[$ikp->number - 1],
            'selected' => true,
        ];

        $index = $ikp->type === 'iku' ? 0 : 1;
        $types = $this->types;
        $types[$index] = [
            ...$types[$index],
            'selected' => true
        ];

        $previousRoute = route('super-admin-iku-ikp', ['ikk' => $ikk['id'], 'sk' => $sk['id'], 'ps' => $ps['id']]);
        $current = true;
        if ($sk->time->year !== Carbon::now()->format('Y')) {
            $previousRoute = route('super-admin-achievement-iku', ['year' => $sk->time->year]);
            $current = false;
        }

        $columns = $ikp->columns()
            ->select([
                'file',
                'name',
            ])
            ->orderBy('number')
            ->get()
            ->toArray();

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
                ->map(function ($unit) use ($ikp): array {
                    $data = $unit->toArray();
                    if (is_array($ikp->unit_id) && in_array($unit->value, $ikp->unit_id)) {
                        $data['selected'] = true;
                    }
                    return $data;
                })
                ->toArray()
        ];

        $ikp = $ikp->only([
            'definition',
            'status',
            'mode',
            'name',
            'id',
            'assigned_to_type',
            'unit_id',
        ]);

        return view('super-admin.iku.ikp.edit', compact([
            'previousRoute',
            'columns',
            'current',
            'types',
            'data',
            'units',
            'ikk',
            'ikp',
            'ps',
            'sk',
        ]));
    }

    /**
     * @param \App\Http\Requests\IndikatorKinerjaProgram\EditRequest $request
     * @param \App\Models\SasaranKegiatan $sk
     * @param \App\Models\IndikatorKinerjaKegiatan $ikk
     * @param \App\Models\ProgramStrategis $ps
     * @param \App\Models\IndikatorKinerjaProgram $ikp
     * @return RedirectResponse
     */
    public function action(EditRequest $request, SasaranKegiatan $sk, IndikatorKinerjaKegiatan $ikk, ProgramStrategis $ps, IndikatorKinerjaProgram $ikp): RedirectResponse
    {
        if ($sk->id !== $ikk->sasaranKegiatan->id || $ikk->id !== $ps->indikatorKinerjaKegiatan->id || $ps->id !== $ikp->programStrategis->id) {
            abort(404);
        }

        $number = (int) $request['number'];
        if ($number > $ps->indikatorKinerjaProgram->count()) {
            return _ControllerHelpers::BackWithInputWithErrors(['number' => 'Nomor tidak sesuai dengan jumlah data']);
        }

        DB::beginTransaction();

        try {
            $currentNumber = $ikp->number;
            if ($number !== $currentNumber) {
                $ikp->number = $number;

                if ($number < $currentNumber) {
                    $ps->indikatorKinerjaProgram()
                        ->where('number', '>=', $number)
                        ->where('number', '<', $currentNumber)
                        ->increment('number');
                } else {
                    $ps->indikatorKinerjaProgram()
                        ->where('number', '<=', $number)
                        ->where('number', '>', $currentNumber)
                        ->decrement('number');
                }
            }

            $ikp->definition = $request['definition'];
            $ikp->name = $request['name'];
            $ikp->type = $request['type'];

            // Handle assignment changes
            $oldAssignedToType = $ikp->assigned_to_type;
            $newAssignedToType = $request['assigned_to_type'];

            if ($oldAssignedToType !== $newAssignedToType) {
                // Delete existing targets when changing assignment type
                $ikp->target()->forceDelete();

                if ($newAssignedToType === 'admin') {
                    $ikp->unit_id = null;
                    // Create targets for all units
                    $allUnits = Unit::pluck('id')->toArray();
                    foreach ($allUnits as $unitId) {
                        $ikp->target()->create([
                            'unit_id' => $unitId,
                            'target' => 0,
                        ]);
                    }
                } elseif ($newAssignedToType === 'kk') {
                    $unitIds = $request['unit_id'] ?? [];
                    $ikp->unit_id = array_filter($unitIds);
                    // Create targets for selected units
                    foreach ($ikp->unit_id as $unitId) {
                        $ikp->target()->create([
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
                    $oldUnitIds = $ikp->unit_id ?? [];

                    if ($oldUnitIds !== $newUnitIds) {
                        // Delete existing targets
                        $ikp->target()->forceDelete();

                        // Update unit_id
                        $ikp->unit_id = $newUnitIds;

                        // Create new targets
                        foreach ($newUnitIds as $unitId) {
                            $ikp->target()->create([
                                'unit_id' => $unitId,
                                'target' => 0,
                            ]);
                        }
                    }
                }
            }

            $ikp->assigned_to_type = $newAssignedToType;
            $ikp->save();

            DB::commit();

            if ($sk->time->year === Carbon::now()->format('Y')) {
                return _ControllerHelpers::RedirectWithRoute('super-admin-iku-ikp', ['sk' => $sk->id, 'ikk' => $ikk->id, 'ps' => $ps->id])
                    ->with('success', 'Berhasil memperbaharui indikator kinerja program');
            }
            return _ControllerHelpers::RedirectWithRoute('super-admin-achievement-iku', [
                'year' => $sk->time->year
            ])->with('success', 'Berhasil memperbaharui indikator kinerja program');
        } catch (\Exception $e) {
            DB::rollBack();

            return _ControllerHelpers::BackWithInputWithErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param \App\Models\SasaranKegiatan $sk
     * @param \App\Models\IndikatorKinerjaKegiatan $ikk
     * @param \App\Models\ProgramStrategis $ps
     * @param \App\Models\IndikatorKinerjaProgram $ikp
     * @return RedirectResponse
     */
    public function statusToggle(SasaranKegiatan $sk, IndikatorKinerjaKegiatan $ikk, ProgramStrategis $ps, IndikatorKinerjaProgram $ikp): RedirectResponse
    {
        if ($sk->id !== $ikk->sasaranKegiatan->id || $ikk->id !== $ps->indikatorKinerjaKegiatan->id || $ps->id !== $ikp->programStrategis->id) {
            abort(404);
        }

        $sk = SasaranKegiatan::currentOrFail($sk->id);

        $ikp->singleAchievements()->forceDelete();
        $ikp->evaluation()->forceDelete();
        $ikp->unitStatus()->forceDelete();
        $ikp->target()->forceDelete();

        $ikp->achievements()->each(function ($item): void {
            $item->deleteOrTrashed();
        });

        $newStatus = $ikp->status === 'aktif' ? 'tidak aktif' : 'aktif';
        $ikp->status = $newStatus;

        $ikp->save();

        return _ControllerHelpers::Back()->with('success', 'Berhasil memperbaharui status indikator kinerja program');
    }
}
