<?php

namespace App\Http\Controllers\SuperAdmin\IndikatorKinerjaKegiatan;

use App\Http\Requests\IndikatorKinerjaKegiatan\AddRequest;
use App\Http\Controllers\_ControllerHelpers;
use App\Models\IndikatorKinerjaKegiatan;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\SasaranKegiatan;
use App\Models\Unit;

class CreateIndikatorKinerjaKegiatanSuperAdminController extends Controller
{
    /**
     * @param \App\Models\SasaranKegiatan $sk
     * @return Factory|View
     */
    public function view(SasaranKegiatan $sk): Factory|View
    {
        $sk = SasaranKegiatan::currentOrFail($sk->id);

        $count = $sk->indikatorKinerjaKegiatan->count() + 1;

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

        $sk = $sk->only([
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
                ->toArray()
        ];

        return view('super-admin.iku.ikk.add', compact([
            'data',
            'sk',
            'units'
        ]));
    }

    /**
     * @param \App\Http\Requests\IndikatorKinerjaKegiatan\AddRequest $request
     * @param \App\Models\SasaranKegiatan $sk
     * @return RedirectResponse
     */
    public function action(AddRequest $request, SasaranKegiatan $sk): RedirectResponse
    {
        $sk = SasaranKegiatan::currentOrFail($sk->id);

        $number = $request['number'];
        $dataCount = $sk->indikatorKinerjaKegiatan->count();
        if ($number > $dataCount + 1) {
            return _ControllerHelpers::BackWithInputWithErrors(['number' => 'Nomor tidak sesuai dengan jumlah data']);
        }

        DB::beginTransaction();

        try {
            if ($number <= $dataCount) {
                $sk->indikatorKinerjaKegiatan()
                    ->where('number', '>=', $number)
                    ->increment('number');
            }

            $data = $request->safe()->only(['number', 'name']);

            // Set default values for Indikator Kinerja Kegiatan (no assignment needed at this level)
            $data['status'] = 'aktif'; // Default status

            $ikk = new IndikatorKinerjaKegiatan($data);

            $ikk->sasaranKegiatan()->associate($sk);
            $ikk->save();

            DB::commit();

            return _ControllerHelpers::RedirectWithRoute('super-admin-iku-ikk', ['sk' => $sk->id])
                ->with('success', 'Berhasil menambahkan indikator kinerja kegiatan');
        } catch (\Exception $e) {
            DB::rollBack();

            return _ControllerHelpers::BackWithInputWithErrors(['error' => $e->getMessage()]);
        }
    }
}
