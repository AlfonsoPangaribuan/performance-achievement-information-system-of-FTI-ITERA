<?php

namespace App\Http\Controllers\SuperAdmin\KK;

use App\Http\Controllers\Controller;
use App\Models\KK;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class HomeKKSuperAdminController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return Factory|View
     */
    public function view(Request $request): Factory|View
    {
        $searchQuery = $request->query('search');

        $user = auth()->user();

        $data = KK::select([
            'short_name',
            'name',
            'id',
        ]);

        $data->when(
            $searchQuery !== null,
            function (Builder $query) use ($searchQuery): Builder {
                return $query->whereAny(
                    [
                        'short_name',
                        'name',
                    ],
                    'LIKE',
                    "%$searchQuery%"
                );
            }
        );

        $data = $data->orderBy('name')->get()->toArray();

        return view('super-admin.kk.home', compact([
            'searchQuery',
            'data',
            'user',
        ]));
    }
}