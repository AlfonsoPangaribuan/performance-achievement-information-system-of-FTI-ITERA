<?php

namespace App\Http\Controllers\SuperAdmin\KK;

use App\Http\Controllers\Controller;
use App\Models\KK;
use Illuminate\Http\RedirectResponse;

class DeleteKKSuperAdminController extends Controller
{
    /**
     * @param \App\Models\KK $kk
     * @return RedirectResponse
     */
    public function action(KK $kk): RedirectResponse
    {
        $kk->delete();

        return redirect()->route('super-admin-kk')
            ->with('success', 'Kelompok Keahlian berhasil dihapus');
    }
}