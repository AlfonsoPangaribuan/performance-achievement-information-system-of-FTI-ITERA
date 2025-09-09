<?php

namespace Database\Seeders;

use App\Models\IndikatorKinerja;
use App\Models\IndikatorKinerjaProgram;
use App\Models\Unit;
use App\Models\RSTarget;
use App\Models\IKUTarget;
use Illuminate\Database\Seeder;

class FixTargetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = Unit::all();

        // Fix RS targets
        $rsIndicators = IndikatorKinerja::where('assigned_to_type', 'admin')
            ->where('status', 'aktif')
            ->get();

        foreach ($rsIndicators as $indicator) {
            $existingTargets = $indicator->target()->pluck('unit_id')->toArray();
            $missingUnits = $units->whereNotIn('id', $existingTargets);

            foreach ($missingUnits as $unit) {
                RSTarget::create([
                    'indikator_kinerja_id' => $indicator->id,
                    'unit_id' => $unit->id,
                    'target' => 0,
                ]);
            }
        }

        // Fix IKU targets
        $ikuPrograms = IndikatorKinerjaProgram::where('assigned_to_type', 'admin')
            ->where('status', 'aktif')
            ->get();

        foreach ($ikuPrograms as $program) {
            $existingTargets = $program->target()->pluck('unit_id')->toArray();
            $missingUnits = $units->whereNotIn('id', $existingTargets);

            foreach ($missingUnits as $unit) {
                IKUTarget::create([
                    'indikator_kinerja_program_id' => $program->id,
                    'unit_id' => $unit->id,
                    'target' => 0,
                ]);
            }
        }
    }
}