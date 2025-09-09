<?php

namespace App\Http\Controllers;

use App\Models\IndikatorKinerja;
use App\Models\RSYear;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\IndikatorKinerjaProgram;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use App\Models\IKUYear;
use App\Models\Unit;

class DashboardController extends Controller
{
    /**
     * Super admin iku dashboard function
     * @param string $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function iku(string $year): Factory|View
    {
        $yearInstance = IKUYear::withTrashed()->where('year', $year)->firstOrFail();

        $datasets = collect();
        $idLists = collect();

        // Get data for Admin (no assignment or assigned_to_type = 'admin')
        $adminData = $yearInstance->sasaranKegiatan()
            ->whereRelation('indikatorKinerjaKegiatan.programStrategis.indikatorKinerjaProgram', function ($query) {
                $query->where('status', 'aktif')
                      ->where(function ($q) {
                          $q->whereNull('assigned_to_type')
                            ->orWhere('assigned_to_type', 'admin');
                      });
            })
            ->with([
                'indikatorKinerjaKegiatan' => function (HasMany $query): void {
                    $query->whereRelation('programStrategis.indikatorKinerjaProgram', function ($q) {
                        $q->where('status', 'aktif')
                          ->where(function ($sq) {
                              $sq->whereNull('assigned_to_type')
                                ->orWhere('assigned_to_type', 'admin');
                          });
                    })
                        ->select([
                            'name AS ikk',
                            'id',
                            'sasaran_kegiatan_id',
                        ])
                        ->orderBy('number');
                },
                'indikatorKinerjaKegiatan.programStrategis' => function (HasMany $query): void {
                    $query->whereRelation('indikatorKinerjaProgram', function ($q) {
                        $q->where('status', 'aktif')
                          ->where(function ($sq) {
                              $sq->whereNull('assigned_to_type')
                                ->orWhere('assigned_to_type', 'admin');
                          });
                    })
                        ->select([
                            'name AS ps',
                            'id',
                            'indikator_kinerja_kegiatan_id',
                        ])
                        ->orderBy('number');
                },
                'indikatorKinerjaKegiatan.programStrategis.indikatorKinerjaProgram' => function (HasMany $query): void {
                    $query->select([
                        'name AS ikp',
                        'mode',
                        'id',
                        'program_strategis_id',
                    ])
                        ->where('status', 'aktif')
                        ->where(function ($q) {
                            $q->whereNull('assigned_to_type')
                              ->orWhere('assigned_to_type', 'admin');
                        })
                        ->orderBy('number');
                },
                'indikatorKinerjaKegiatan.programStrategis.indikatorKinerjaProgram.singleAchievements',
                'indikatorKinerjaKegiatan.programStrategis.indikatorKinerjaProgram.achievements',
                'indikatorKinerjaKegiatan.programStrategis.indikatorKinerjaProgram.target',
            ])
            ->select([
                'name AS sk',
                'id',
            ])
            ->orderBy('number')
            ->get();

        // Get data for KK (assigned_to_type = 'kk')
        $kkData = $yearInstance->sasaranKegiatan()
            ->whereRelation('indikatorKinerjaKegiatan.programStrategis.indikatorKinerjaProgram', function ($query) {
                $query->where('status', 'aktif')
                      ->where('assigned_to_type', 'kk');
            })
            ->with([
                'indikatorKinerjaKegiatan' => function (HasMany $query): void {
                    $query->whereRelation('programStrategis.indikatorKinerjaProgram', function ($q) {
                        $q->where('status', 'aktif')
                          ->where('assigned_to_type', 'kk');
                    })
                        ->select([
                            'name AS ikk',
                            'id',
                            'sasaran_kegiatan_id',
                        ])
                        ->orderBy('number');
                },
                'indikatorKinerjaKegiatan.programStrategis' => function (HasMany $query): void {
                    $query->whereRelation('indikatorKinerjaProgram', function ($q) {
                        $q->where('status', 'aktif')
                          ->where('assigned_to_type', 'kk');
                    })
                        ->select([
                            'name AS ps',
                            'id',
                            'indikator_kinerja_kegiatan_id',
                        ])
                        ->orderBy('number');
                },
                'indikatorKinerjaKegiatan.programStrategis.indikatorKinerjaProgram' => function (HasMany $query): void {
                    $query->select([
                        'name AS ikp',
                        'mode',
                        'id',
                        'program_strategis_id',
                        'unit_id',
                    ])
                        ->where('status', 'aktif')
                        ->where('assigned_to_type', 'kk')
                        ->orderBy('number');
                },
                'indikatorKinerjaKegiatan.programStrategis.indikatorKinerjaProgram.singleAchievements',
                'indikatorKinerjaKegiatan.programStrategis.indikatorKinerjaProgram.achievements',
                'indikatorKinerjaKegiatan.programStrategis.indikatorKinerjaProgram.target',
            ])
            ->select([
                'name AS sk',
                'id',
            ])
            ->orderBy('number')
            ->get();

        $units = Unit::withTrashed()
            ->where(function (Builder $query) use ($year): void {
                $query->whereNotNull('deleted_at')->where(function (Builder $query) use ($year): void {
                    $query->whereHas('indikatorKinerjaUtama', function (Builder $query) use ($year): void {
                        $query->whereHas('period', function (Builder $query) use ($year): void {
                            $query->whereHas('year', function (Builder $query) use ($year): void {
                                $query->where('year', $year);
                            });
                        });
                    })
                        ->orWhereHas('singleIndikatorKinerjaUtama', function (Builder $query) use ($year): void {
                            $query->whereHas('period', function (Builder $query) use ($year): void {
                                $query->whereHas('year', function (Builder $query) use ($year): void {
                                    $query->where('year', $year);
                                });
                            });
                        })
                        ->orWhereHas('indikatorKinerjaUtamaTarget', function (Builder $query) use ($year): void {
                            $query->whereHas('indikatorKinerjaProgram', function (Builder $query) use ($year): void {
                                $query->whereHas('programStrategis', function (Builder $query) use ($year): void {
                                    $query->whereHas('indikatorKinerjaKegiatan', function (Builder $query) use ($year): void {
                                        $query->whereHas('sasaranKegiatan', function (Builder $query) use ($year): void {
                                            $query->whereHas('time', function (Builder $query) use ($year): void {
                                                $query->where('year', $year);
                                            });
                                        });
                                    });
                                });
                            });
                        });
                });
            })
            ->orWhereNull('deleted_at')
            ->select([
                'short_name',
                'name',
                'id',
            ])
            ->latest()
            ->get();

        // Process Admin data
        $adminDatasets = collect();
        $adminIdLists = collect();
        $this->processIkuData($adminData, $adminIdLists, $adminDatasets, $units);

        // Process KK data
        $kkDatasets = collect();
        $kkIdLists = collect();
        $this->processIkuData($kkData, $kkIdLists, $kkDatasets, $units);

        $previousRoute = route('super-admin-dashboard', ['ikuYear' => $year]);

        return view('super-admin.dashboard.iku', compact([
            'previousRoute',
            'adminDatasets',
            'adminIdLists',
            'adminData',
            'kkDatasets',
            'kkIdLists',
            'kkData',
            'year',
        ]));
    }

    /**
     * Super admin rs dashboard function
     * @param string $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function rs(string $year): Factory|View
    {
        $yearInstance = RSYear::withTrashed()->where('year', $year)->firstOrFail();

        $datasets = collect();
        $idLists = collect();

        // Get data for Admin (no assignment or assigned_to_type = 'admin')
        $adminData = $yearInstance->sasaranStrategis()
            ->whereRelation('kegiatan.indikatorKinerja', function ($query) {
                $query->where('status', 'aktif')
                      ->where(function ($q) {
                          $q->whereNull('assigned_to_type')
                            ->orWhere('assigned_to_type', 'admin');
                      });
            })
            ->with([
                'kegiatan' => function (HasMany $query): void {
                    $query->whereRelation('indikatorKinerja', function ($q) {
                        $q->where('status', 'aktif')
                          ->where(function ($sq) {
                              $sq->whereNull('assigned_to_type')
                                ->orWhere('assigned_to_type', 'admin');
                          });
                    })
                        ->select([
                            'name AS k',
                            'id',
                            'sasaran_strategis_id',
                        ])
                        ->orderBy('number');
                },
                'kegiatan.indikatorKinerja' => function (HasMany $query): void {
                    $query->select([
                        'name AS ik',
                        'type',
                        'id',
                        'kegiatan_id',
                    ])
                        ->where('status', 'aktif')
                        ->where(function ($q) {
                            $q->whereNull('assigned_to_type')
                              ->orWhere('assigned_to_type', 'admin');
                        })
                        ->orderBy('number');
                },
                'kegiatan.indikatorKinerja.textSelections',
                'kegiatan.indikatorKinerja.realization',
                'kegiatan.indikatorKinerja.target',
            ])
            ->select([
                'name AS ss',
                'id',
            ])
            ->orderBy('number')
            ->get();

        // Get data for KK (assigned_to_type = 'kk')
        $kkData = $yearInstance->sasaranStrategis()
            ->whereRelation('kegiatan.indikatorKinerja', function ($query) {
                $query->where('status', 'aktif')
                      ->where('assigned_to_type', 'kk');
            })
            ->with([
                'kegiatan' => function (HasMany $query): void {
                    $query->whereRelation('indikatorKinerja', function ($q) {
                        $q->where('status', 'aktif')
                          ->where('assigned_to_type', 'kk');
                    })
                        ->select([
                            'name AS k',
                            'id',
                            'sasaran_strategis_id',
                        ])
                        ->orderBy('number');
                },
                'kegiatan.indikatorKinerja' => function (HasMany $query): void {
                    $query->select([
                        'name AS ik',
                        'type',
                        'id',
                        'kegiatan_id',
                        'unit_id',
                    ])
                        ->where('status', 'aktif')
                        ->where('assigned_to_type', 'kk')
                        ->orderBy('number');
                },
                'kegiatan.indikatorKinerja.textSelections',
                'kegiatan.indikatorKinerja.realization',
                'kegiatan.indikatorKinerja.target',
            ])
            ->select([
                'name AS ss',
                'id',
            ])
            ->orderBy('number')
            ->get();

        $units = Unit::withTrashed()
            ->where(function (Builder $query) use ($year): void {
                $query->whereNotNull('deleted_at')->where(function (Builder $query) use ($year): void {
                    $query->whereHas('rencanaStrategis', function (Builder $query) use ($year): void {
                        $query->whereHas('period', function (Builder $query) use ($year): void {
                            $query->whereHas('year', function (Builder $query) use ($year): void {
                                $query->where('year', $year);
                            });
                        });
                    })
                        ->orWhereHas('rencanaStrategisTarget', function (Builder $query) use ($year): void {
                            $query->whereHas('indikatorKinerja', function (Builder $query) use ($year): void {
                                $query->whereHas('kegiatan', function (Builder $query) use ($year): void {
                                    $query->whereHas('sasaranStrategis', function (Builder $query) use ($year): void {
                                        $query->whereHas('time', function (Builder $query) use ($year): void {
                                            $query->where('year', $year);
                                        });
                                    });
                                });
                            });
                        });
                });
            })
            ->orWhereNull('deleted_at')
            ->select([
                'short_name',
                'name',
                'id',
            ])
            ->latest()
            ->get();

        // Process Admin data
        $adminDatasets = collect();
        $adminIdLists = collect();
        $this->processRsData($adminData, $adminIdLists, $adminDatasets, $units);

        // Process KK data
        $kkDatasets = collect();
        $kkIdLists = collect();
        $this->processRsData($kkData, $kkIdLists, $kkDatasets, $units);

        $previousRoute = route('super-admin-dashboard', ['rsYear' => $year]);

        return view('super-admin.dashboard.rs', compact([
            'previousRoute',
            'adminDatasets',
            'adminIdLists',
            'adminData',
            'kkDatasets',
            'kkIdLists',
            'kkData',
            'year',
        ]));
    }

    /**
     * Process RS data for charts
     */
    private function processRsData($data, $idLists, $datasets, $units): void
    {
        $data->each(function ($item) use ($idLists, $datasets, $units): void {
            $item->kegiatan->each(function ($item) use ($idLists, $datasets, $units): void {
                $item->indikatorKinerja->each(function ($item) use ($idLists, $datasets, $units): void {
                    $realizationTemp = collect();
                    $targetTemp = collect();
                    $unitTemp = collect();
                    $units->each(function ($unit) use ($realizationTemp, $targetTemp, $unitTemp, $item): void {
                        $target = $item->target->firstWhere('unit_id', $unit->id)?->target ?? 0;
                        if ($item->type === IndikatorKinerja::TYPE_PERCENT) {
                            $realizationTemp->push($item->realization->where('unit_id', $unit->id)->average('realization'));
                        } else if ($item->type === IndikatorKinerja::TYPE_NUMBER) {
                            $realizationTemp->push($item->realization->where('unit_id', $unit->id)->sum('realization'));
                        } else {
                            $realizationTemp->push($item->realization->where('unit_id', $unit->id)->where('realization', $target ?? '')->count());
                            $target = $item->realization->where('unit_id', $unit->id)->count();
                        }
                        $targetTemp->push($target);
                        $unitTemp->push($unit->short_name);
                    });
                    $datasets->put($item->id, [
                        'realization' => $realizationTemp->toArray(),
                        'target' => $targetTemp->toArray(),
                        'unit' => $unitTemp->toArray(),
                    ]);
                    $idLists->push($item->id);
                });
            });
        });
    }

    /**
     * Process IKU data for charts
     */
    private function processIkuData($data, $idLists, $datasets, $units): void
    {
        $data->each(function ($item) use ($idLists, $datasets, $units): void {
            $item->indikatorKinerjaKegiatan->each(function ($item) use ($idLists, $datasets, $units): void {
                $item->programStrategis->each(function ($item) use ($idLists, $datasets, $units): void {
                    $item->indikatorKinerjaProgram->each(function ($item) use ($idLists, $datasets, $units): void {
                        $realizationTemp = collect();
                        $targetTemp = collect();
                        $unitTemp = collect();
                        $units->each(function ($unit) use ($realizationTemp, $targetTemp, $unitTemp, $item): void {
                            if ($item->mode === IndikatorKinerjaProgram::MODE_TABLE) {
                                $realizationTemp->push($item->achievements->where('unit_id', $unit->id)->where('status', true)->count());
                            } else {
                                $realizationTemp->push($item->singleAchievements->where('unit_id', $unit->id)->average('value'));
                            }
                            $targetTemp->push($item->target->firstWhere('unit_id', $unit->id)?->target ?? 0);
                            $unitTemp->push($unit->short_name);
                        });
                        $datasets->put($item->id, [
                            'realization' => $realizationTemp->toArray(),
                            'target' => $targetTemp->toArray(),
                            'unit' => $unitTemp->toArray(),
                        ]);
                        $idLists->push($item->id);
                    });
                });
            });
        });
    }
}
