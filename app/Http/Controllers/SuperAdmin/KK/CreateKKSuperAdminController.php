<?php

namespace App\Http\Controllers\SuperAdmin\KK;

use App\Http\Controllers\Controller;
use App\Models\KK;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;

class CreateKKSuperAdminController extends Controller
{
    /**
     * @return Factory|View
     */
    public function view(): Factory|View
    {
        $units = Unit::select(['id', 'name'])->orderBy('name')->get();
        
        return view('super-admin.kk.add', compact('units'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function action(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'short_name' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:255'],
            'unit_id' => ['required', 'exists:units,id'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        KK::create([
            'short_name' => $validated['short_name'],
            'name' => $validated['name'],
            'unit_id' => $validated['unit_id'],
        ]);

        return redirect()->route('super-admin-kk')
            ->with('success', 'Kelompok Keahlian berhasil ditambahkan');
    }
}