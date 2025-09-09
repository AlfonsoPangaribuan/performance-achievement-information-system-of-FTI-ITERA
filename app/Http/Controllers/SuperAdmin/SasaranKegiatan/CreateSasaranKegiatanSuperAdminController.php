<?php

namespace App\Http\Controllers\SuperAdmin\SasaranKegiatan;

use App\Http\Requests\SasaranKegiatan\AddRequest;
use App\Http\Controllers\_ControllerHelpers;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\SasaranKegiatan;
use App\Models\IKUYear;
use App\Models\Unit;

class CreateSasaranKegiatanSuperAdminController extends Controller
{
    /**
     * @return Factory|View
     */
    public function view(): Factory|View
    {
        $time = IKUYear::currentTime();

        $count = $time->sasaranKegiatan->count() + 1;

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

        return view('super-admin.iku.sk.add', compact('data', 'units'));
    }

    /**
     * @param \App\Http\Requests\SasaranKegiatan\AddRequest $request
     * @return RedirectResponse
     */
    public function action(AddRequest $request): RedirectResponse
    {
        $time = IKUYear::currentTime();

        $number = (int) $request['number'];
        $dataCount = $time->sasaranKegiatan->count();

        if ($number > $dataCount + 1) {
            return _ControllerHelpers::BackWithInputWithErrors(['number' => 'Nomor tidak sesuai dengan jumlah data']);
        }

        DB::beginTransaction();

        try {
            if ($number <= $dataCount) {
                $time->sasaranKegiatan()
                    ->where('number', '>=', $number)
                    ->increment('number');
            }

            $data = $request->safe()->only(['number', 'name']);

            // Set default values for Sasaran Kegiatan (no assignment needed at this level)
            $data['status'] = 'aktif'; // Default status

            $sk = new SasaranKegiatan($data);

            $sk->time()->associate($time);
            $sk->save();

            DB::commit();

            return _ControllerHelpers::RedirectWithRoute('super-admin-iku-sk')->with('success', 'Berhasil menambahkan sasaran kegiatan');
        } catch (\Exception $e) {
            DB::rollBack();

            return _ControllerHelpers::BackWithInputWithErrors(['error' => $e->getMessage()]);
        }
    }
}
