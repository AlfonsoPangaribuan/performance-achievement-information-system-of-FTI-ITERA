<?php

namespace App\Http\Controllers\Admin\IndikatorKinerjaUtama;

use App\Http\Controllers\SuperAdmin\IndikatorKinerjaUtama\HomeIndikatorKinerjaUtamaSuperAdminController;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\Factory;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\IKUPeriod;
use App\Models\IKUYear;

class HomeIndikatorKinerjaUtamaAdminController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return Factory|View
     */
    public function view(Request $request): Factory|View
    {
        HomeIndikatorKinerjaUtamaSuperAdminController::CheckRoutine();

        $periodRequest = $request->query('period');
        $yearQuery = $request->query('year');

        if ($yearQuery !== null && !is_numeric($yearQuery)) {
            abort(404);
        }
        if ($periodRequest !== null && !in_array($periodRequest, ['1', '2', '3', '4'])) {
            abort(404);
        }

        $user = auth()->user();

        $currentDate = Carbon::now();

        $currentMonth = (int) $currentDate->format('m');
        $currentYear = $currentDate->format('Y');
        $currentPeriod = '1';

        foreach ([3, 6, 9, 12] as $key => $value) {
            if ($currentMonth <= $value) {
                $currentPeriod = (string) ($key + 1);
                break;
            }
        }

        $years = IKUYear::whereHas('periods', function (Builder $query) use ($currentDate): void {
                $query->where('status', true);
            })
            ->pluck('year')
            ->toArray();

        $periods = [];
        $badge = [];
        $data = [];

        $period = '';
        $year = '';

        if (count($years)) {
            $year = $yearQuery ?? end($years);
            $yearInstance = IKUYear::where('year', $year)->firstOrFail();

            $periods = $yearInstance->periods()
                ->where('status', true)
                ->orderBy('period')
                ->pluck('period')
                ->map(function ($item): array {
                    $title = 'TW 1 | Jan - Mar';
                    if ($item === '2') {
                        $title = 'TW 2 | Apr - Jun';
                    } else if ($item === '3') {
                        $title = 'TW 3 | Jul - Sep';
                    } else if ($item === '4') {
                        $title = 'TW 4 | Okt - Des';
                    }

                    return [
                        'title' => $title,
                        'value' => $item,
                    ];
                });

            $period = $periodRequest ?? $periods->last()['value'];
            $periodInstance = $yearInstance->periods()
                ->where('period', $period)
                ->where('status', true)
                ->firstOrFail();

            $data = $yearInstance->sasaranKegiatan()
                ->whereHas('indikatorKinerjaKegiatan.programStrategis.indikatorKinerjaProgram', function (Builder $query) use ($user): void {
                    $query->where('status', 'aktif')
                        ->where(function (Builder $query) use ($user): void {
                            if ($user->role === 'admin') {
                                // Admin users see ONLY admin assignments
                                $query->where('assigned_to_type', 'admin');
                            } else {
                                // KK users only see KK assignments for their unit
                                $query->where('assigned_to_type', 'kk')
                                    ->where(function (Builder $kkQuery) use ($user): void {
                                        $kkQuery->whereHas('target', function (Builder $targetQuery) use ($user): void {
                                            $targetQuery->whereBelongsTo($user->unit);
                                        })
                                        ->orWhereJsonContains('unit_id', $user->unit_id);
                                    });
                            }
                        });
                })
                ->with([
                    'indikatorKinerjaKegiatan' => function (HasMany $query) use ($user): void {
                        $query->whereHas('programStrategis.indikatorKinerjaProgram', function (Builder $query) use ($user): void {
                            $query->where('status', 'aktif')
                                ->where(function (Builder $query) use ($user): void {
                                    if ($user->role === 'admin') {
                                        // Admin users see ONLY admin assignments
                                        $query->where('assigned_to_type', 'admin');
                                    } else {
                                        // KK users only see KK assignments for their unit
                                        $query->where('assigned_to_type', 'kk')
                                            ->where(function (Builder $kkQuery) use ($user): void {
                                                $kkQuery->whereHas('target', function (Builder $targetQuery) use ($user): void {
                                                    $targetQuery->whereBelongsTo($user->unit);
                                                })
                                                ->orWhereJsonContains('unit_id', $user->unit_id);
                                            });
                                    }
                                });
                        })
                            ->orderBy('number')
                            ->select([
                                'name AS ikk',
                                'id',

                                'sasaran_kegiatan_id',
                            ]);
                    },
                    'indikatorKinerjaKegiatan.programStrategis' => function (HasMany $query) use ($user): void {
                        $query->whereHas('indikatorKinerjaProgram', function (Builder $query) use ($user): void {
                            $query->where('status', 'aktif')
                                ->where(function (Builder $query) use ($user): void {
                                    if ($user->role === 'admin') {
                                        // Admin users see ONLY admin assignments
                                        $query->where('assigned_to_type', 'admin');
                                    } else {
                                        // KK users only see KK assignments for their unit
                                        $query->where('assigned_to_type', 'kk')
                                            ->where(function (Builder $kkQuery) use ($user): void {
                                                $kkQuery->whereHas('target', function (Builder $targetQuery) use ($user): void {
                                                    $targetQuery->whereBelongsTo($user->unit);
                                                })
                                                ->orWhereJsonContains('unit_id', $user->unit_id);
                                            });
                                    }
                                });
                        })
                            ->orderBy('number')
                            ->select([
                                'name AS ps',
                                'id',

                                'indikator_kinerja_kegiatan_id',
                            ])
                            ->withCount([
                                'indikatorKinerjaProgram AS rowspan' => function (Builder $query) use ($user): void {
                                    $query->where('status', 'aktif')
                                        ->where(function (Builder $query) use ($user): void {
                                            if ($user->role === 'admin') {
                                                // Admin users see ONLY admin assignments
                                                $query->where('assigned_to_type', 'admin');
                                            } else {
                                                // KK users only see KK assignments for their unit
                                                $query->where('assigned_to_type', 'kk')
                                                    ->where(function (Builder $kkQuery) use ($user): void {
                                                        $kkQuery->whereHas('target', function (Builder $targetQuery) use ($user): void {
                                                            $targetQuery->whereBelongsTo($user->unit);
                                                        })
                                                        ->orWhereJsonContains('unit_id', $user->unit_id);
                                                    });
                                            }
                                        });
                                }
                            ]);
                    },
                    'indikatorKinerjaKegiatan.programStrategis.indikatorKinerjaProgram' => function (HasMany $query) use ($periodInstance, $user): void {
                        $query->where('status', 'aktif')
                            ->where(function (Builder $query) use ($user): void {
                                if ($user->role === 'admin') {
                                    // Admin users see ONLY admin assignments
                                    $query->where('assigned_to_type', 'admin');
                                } else {
                                    // KK users only see KK assignments for their unit
                                    $query->where('assigned_to_type', 'kk')
                                        ->where(function (Builder $kkQuery) use ($user): void {
                                            $kkQuery->whereHas('target', function (Builder $targetQuery) use ($user): void {
                                                $targetQuery->whereBelongsTo($user->unit);
                                            })
                                            ->orWhereJsonContains('unit_id', $user->unit_id);
                                        });
                                }
                            })
                            ->orderBy('number')
                            ->select([
                                'name AS ikp',
                                'definition',
                                'mode',
                                'type',
                                'id',

                                'program_strategis_id',
                            ])
                            ->withAggregate([
                                'target AS target' => function (Builder $query) use ($user): void {
                                    $query->whereBelongsTo($user->unit);
                                }
                            ], 'target')
                            ->withExists([
                                'target AS hasTarget' => function (Builder $query) use ($user): void {
                                    $query->whereBelongsTo($user->unit);
                                }
                            ])
                            ->withAggregate([
                                'unitStatus AS unitStatus' => function (Builder $query) use ($periodInstance, $user): void {
                                    $query->whereBelongsTo($user->unit)
                                        ->whereBelongsTo($periodInstance, 'period');
                                }
                            ], 'status')
                            ->withAggregate([
                                'singleAchievements AS valueSingle' => function (Builder $query) use ($periodInstance, $user): void {
                                    $query->whereBelongsTo($user->unit)
                                        ->whereBelongsTo($periodInstance, 'period');
                                }
                            ], 'value')
                            ->withAggregate([
                                'singleAchievements AS linkSingle' => function (Builder $query) use ($periodInstance, $user): void {
                                    $query->whereBelongsTo($user->unit)
                                        ->whereBelongsTo($periodInstance, 'period');
                                }
                            ], 'link')
                            ->withAvg([
                                'singleAchievements AS allSingle' => function (Builder $query) use ($user): void {
                                    $query->whereBelongsTo($user->unit);
                                }
                            ], 'value')
                            ->withCount([
                                'unitStatus AS yearUnitStatus' => function (Builder $query) use ($user): void {
                                    $query->whereBelongsTo($user->unit);
                                },
                                'achievements AS all' => function (Builder $query) use ($user): void {
                                    $query->where('status', true)
                                        ->whereBelongsTo($user->unit);
                                },
                                'achievements AS achievements' => function (Builder $query) use ($periodInstance, $user): void {
                                    $query->where('status', true)
                                        ->whereBelongsTo($user->unit)
                                        ->whereBelongsTo($periodInstance, 'period');
                                }
                            ]);
                    },
                ])
                ->orderBy('number')
                ->select([
                    'name AS sk',
                    'number',
                    'id',
                ])
                ->get()
                ->map(function ($item): array {
                    $temp = $item->indikatorKinerjaKegiatan->map(function ($item): array {
                        return [
                            ...$item->toArray(),

                            'rowspan' => $item->programStrategis->sum('rowspan')
                        ];
                    });

                    return [
                        ...$item->only(['number', 'sk', 'id']),

                        'indikator_kinerja_kegiatan' => $temp->toArray(),
                        'rowspan' => $temp->sum('rowspan'),
                    ];
                })
                ->toArray();

            $periodReq = $periods->firstWhere('value', $period);
            $badge = [$periodReq['title'], $year];

            $periods = $periods->toArray();
        }

        return view('admin.iku.home', compact([
            'periods',
            'period',
            'badge',
            'years',
            'year',
            'data',
            'user',
        ]));
    }
}
