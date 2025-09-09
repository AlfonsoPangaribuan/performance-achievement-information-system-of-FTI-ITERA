<?php

namespace App\Http\Controllers\SuperAdmin\IndikatorKinerja;

use App\Http\Requests\IndikatorKinerja\AddRequest;
use App\Http\Controllers\_ControllerHelpers;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\IndikatorKinerja;
use App\Models\SasaranStrategis;
use App\Models\Kegiatan;
use App\Models\Unit;

class CreateIndikatorKinerjaSuperAdminController extends Controller
{
    /**
     * @param \App\Models\SasaranStrategis $ss
     * @param \App\Models\Kegiatan $k
     * @return Factory|View
     */
    public function view(SasaranStrategis $ss, Kegiatan $k): Factory|View
    {
        if ($ss->id !== $k->sasaranStrategis->id) {
            abort(404);
        }

        $ss = SasaranStrategis::currentOrFail($ss->id);

        $count = $k->indikatorKinerja->count() + 1;

        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[$i] = [
                "value" => strval($i + 1),
                "text" => strval($i + 1),
            ];
        }
        $data[$count - 1] = [
            ...$data[$count - 1],
            'selected' => true,
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

        $type = [
            [
                'value' => IndikatorKinerja::TYPE_TEXT,
                'text' => ucfirst(IndikatorKinerja::TYPE_TEXT),
            ],
            [
                'value' => IndikatorKinerja::TYPE_NUMBER,
                'text' => ucfirst(IndikatorKinerja::TYPE_NUMBER),
            ],
            [
                'value' => IndikatorKinerja::TYPE_PERCENT,
                'text' => ucfirst(IndikatorKinerja::TYPE_PERCENT),
            ],
        ];

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
                ->toArray()
        ];

        return view('super-admin.rs.ik.add', compact([
            'data',
            'type',
            'ss',
            'k',
            'units'
        ]));
    }

    /**
     * @param \App\Http\Requests\IndikatorKinerja\AddRequest $request
     * @param \App\Models\SasaranStrategis $ss
     * @param \App\Models\Kegiatan $k
     * @return RedirectResponse
     */
    public function action(AddRequest $request, SasaranStrategis $ss, Kegiatan $k): RedirectResponse
    {
        if ($ss->id !== $k->sasaranStrategis->id) {
            abort(404);
        }

        $ss = SasaranStrategis::currentOrFail($ss->id);

        $number = $request['number'];
        $dataCount = $k->indikatorKinerja->count();
        if ($number > $dataCount + 1) {
            return _ControllerHelpers::BackWithInputWithErrors(['number' => 'Nomor tidak sesuai dengan jumlah data']);
        }

        DB::beginTransaction();

        try {
            if ($number <= $dataCount) {
                $k->indikatorKinerja()
                    ->where('number', '>=', $number)
                    ->increment('number');
            }

            $data = $request->safe()->all();

            if ($data['assigned_to_type'] === 'admin') {
                $data['unit_id'] = null;
            } elseif ($data['assigned_to_type'] === 'kk') {
                // Handle multiple units - if KK is selected, ensure all units are assigned
                if (isset($data['unit_id']) && is_array($data['unit_id'])) {
                    $data['unit_id'] = array_filter($data['unit_id']); // Remove empty values
                } else {
                    // If no units selected but KK is chosen, assign all units
                    $allUnits = Unit::pluck('id')->toArray();
                    $data['unit_id'] = $allUnits;
                }
            }

            $ik = new IndikatorKinerja($data);

            $ik->kegiatan()->associate($k);
            $ik->status = 'aktif';

            $ik->save();

            // Create target records for assigned units
            if ($data['assigned_to_type'] === 'admin') {
                // For admin, create targets for all units
                $allUnits = Unit::pluck('id')->toArray();
                foreach ($allUnits as $unitId) {
                    $ik->target()->create([
                        'unit_id' => $unitId,
                        'target' => 0, // Default target value
                    ]);
                }
            } elseif ($data['assigned_to_type'] === 'kk' && isset($data['unit_id']) && is_array($data['unit_id'])) {
                foreach ($data['unit_id'] as $unitId) {
                    $ik->target()->create([
                        'unit_id' => $unitId,
                        'target' => 0, // Default target value
                    ]);
                }
            }

            if ($ik->type === 'teks') {
                $options = ['A', 'TA'];
                if (is_array($request['selection'])) {
                    $options = $request['selection'];
                }

                foreach ($options as $item) {
                    $ik->textSelections()->create([
                        'value' => $item,
                    ]);
                }
            }

            DB::commit();

            return _ControllerHelpers::RedirectWithRoute('super-admin-rs-ik', ['ss' => $ss->id, 'k' => $k->id])->with('success', 'Berhasil menambahkan indikator kinerja');
        } catch (\Exception $e) {
            DB::rollBack();

            return _ControllerHelpers::BackWithInputWithErrors(['error' => $e->getMessage()]);
        }
    }
}
