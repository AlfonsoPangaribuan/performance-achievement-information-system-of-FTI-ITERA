@php
    $breadCrumbs = [
        [
            'link' => 'super-admin-rs-ss',
            'name' => 'Renstra - Sasaran Strategis',
        ],
        [
            'link' => 'super-admin-rs-k',
            'name' => 'Renstra - Kegiatan',
            'params' => [
                'ss' => $ss['id'],
            ],
        ],
        [
            'link' => 'super-admin-rs-ik',
            'name' => 'Renstra - Indikator Kinerja',
            'params' => [
                'ss' => $ss['id'],
                'k' => $k['id'],
            ],
        ],
        [
            'link' => 'super-admin-rs-ik-edit',
            'name' => 'Ubah',
            'params' => [
                'ik' => $ik['id'],
                'ss' => $ss['id'],
                'k' => $k['id'],
            ],
        ],
    ];
@endphp

<x-super-admin-template title="Ubah Indikator Kinerja - Super Admin">
    <x-partials.breadcrumbs.default :$breadCrumbs />
    <x-partials.heading.h2 text="ubah indikator kinerja" :$previousRoute />
    <x-partials.heading.h3 title="Sasaran strategis" dataNumber="{{ $ss['number'] }}" dataText="{{ $ss['name'] }}" />
    <x-partials.heading.h3 title="Kegiatan" dataNumber="{{ $k['number'] }}" dataText="{{ $k['name'] }}" />

    @if ($current)
        <label
            onclick="pushURL('status-toggle-confirmation', '{{ url(route('super-admin-rs-ik-status', ['ik' => $ik['id'], 'ss' => $ss['id'], 'k' => $k['id']])) }}')"
            class="flex items-center justify-start" data-modal-target="status-toggle-confirmation"
            data-modal-toggle="status-toggle-confirmation">
            <input type="checkbox" value="{{ $ik['status'] }}" class="peer sr-only" @checked($ik['status'] === 'aktif')
                disabled>
            <div class="peer flex w-11 cursor-pointer rounded-full bg-red-400 p-0.5 peer-checked:bg-green-400">
                <div
                    class="{{ $ik['status'] === 'aktif' ? 'ml-auto' : 'mr-auto' }} aspect-square w-4 rounded-full bg-white">
                </div>
            </div>
        </label>
        <p class="text-xs font-bold text-red-400">*Merubah status akan menghapus realisasi capaian yang telah diinputkan
            setiap unit</p>
    @else
        <div title="Status penugasan : {{ $ik['status'] }}"
            class="{{ $ik['status'] === 'aktif' ? 'bg-green-500' : 'bg-red-500' }} ml-auto rounded-full p-3"></div>
    @endif

    <form action="" method="POST" class="flex flex-col gap-2">
        @csrf
        @method('PUT')

        <div class="flex flex-wrap gap-2">
            <div class="flex min-w-28 flex-col gap-2 max-sm:flex-1">
                <x-partials.label.default for="number" title="Nomor" text="Nomor" required />
                <x-partials.input.select name="number" title="Nomor" :$data required />

                @error('number')
                    <p class="text-xs text-red-500 lg:text-sm">{{ $message }}</p>
                @enderror

            </div>
            <div class="flex flex-1 flex-col gap-2">
                <x-partials.label.default for="name" title="Indikator kinerja" text="Indikator Kinerja" required />
                <x-partials.input.text name="name" title="Indikator kinerja" value="{{ $ik['name'] }}" autofocus
                    required />
            </div>
            <div class="flex flex-col gap-2 max-xl:flex-1">
                <x-partials.label.default for="type" title="Tipe data" text="Tipe Data" required />
                <x-partials.input.select name="type" title="Tipe data" :data="$type" disabled required />

                @error('type')
                    <p class="text-xs text-red-500 lg:text-sm">{{ $message }}</p>
                @enderror

            </div>
        </div>

        <div class="flex flex-col gap-2">
            <x-partials.label.default for="assigned_to_type" title="Tugaskan kepada" text="Tugaskan kepada" required />
            <div class="flex gap-2">
                <input type="radio" name="assigned_to_type" id="admin" value="admin" {{ $ik['assigned_to_type'] === 'admin' ? 'checked' : '' }}>
                <label for="admin">Admin</label>
                <input type="radio" name="assigned_to_type" id="kk" value="kk" {{ $ik['assigned_to_type'] === 'kk' ? 'checked' : '' }}>
                <label for="kk">KK</label>
            </div>
        </div>

        <div id="unit-selection" class="hidden flex flex-col gap-2">
            <x-partials.label.default for="unit_id" title="Pilih Unit (KK)" text="Pilih Unit (KK)" />
            <div class="text-sm text-gray-600 mb-2">
                <strong>Catatan:</strong> Saat memilih "KK", semua unit akan otomatis dipilih.
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($units as $unit)
                    @if($unit['value'] !== '')
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="unit_id[]" id="unit_{{ $unit['value'] }}" value="{{ $unit['value'] }}" class="unit-checkbox rounded border-gray-300 text-primary focus:ring-primary" {{ isset($unit['selected']) && $unit['selected'] ? 'checked' : '' }}>
                            <label for="unit_{{ $unit['value'] }}" class="text-sm text-gray-700">{{ $unit['text'] }}</label>
                        </div>
                    @endif
                @endforeach
            </div>
            <div id="manual-selection-controls" class="flex items-center gap-4 mt-2">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="select-all-units" class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="select-all-units" class="text-sm text-gray-700">Pilih Semua Unit</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="unselect-all-units" class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="unselect-all-units" class="text-sm text-gray-700">Hapus Semua Pilihan</label>
                </div>
            </div>
            <div id="auto-selection-notice" class="hidden text-sm text-blue-600 font-medium">
                ✅ Semua unit telah otomatis dipilih untuk penugasan KK
            </div>
        </div>

        @if ($ik['type'] === \App\Models\IndikatorKinerja::TYPE_TEXT)
            <div class="flex flex-col gap-3 rounded-lg border-2 border-dashed border-primary p-3 text-primary">
                <p>Pilihan</p>
                @if (count($ik['textSelections']))
                    <div class="flex flex-wrap items-center justify-start gap-1.5 max-md:text-sm">
                        @foreach ($ik['textSelections'] as $item)
                            <p class="rounded-lg border-2 border-primary p-1">{{ $item['value'] }}</p>
                        @endforeach
                    </div>
                @else
                    <p class="text-red-500 max-md:text-sm">Tidak ada pilihan teks</p>
                @endif
            </div>
        @endif

        <x-partials.button.edit />
    </form>

    @if ($current)
        <x-partials.modal.confirmation id="status-toggle-confirmation"
            message="Apakah anda yakin ingin mengubah status?"
            note="*Merubah status akan menghapus realisasi capaian yang telah diinputkan setiap unit" />
    @endif

    @pushOnce('script')
        <script>
            // Assignment controls
            const adminRadio = document.getElementById('admin');
            const kkRadio = document.getElementById('kk');
            const unitSelection = document.getElementById('unit-selection');
            const manualSelectionControls = document.getElementById('manual-selection-controls');
            const autoSelectionNotice = document.getElementById('auto-selection-notice');
            const selectAllCheckbox = document.getElementById('select-all-units');
            const unitCheckboxes = document.querySelectorAll('.unit-checkbox');

            function toggleUnitSelection() {
                if (kkRadio && kkRadio.checked) {
                    if (unitSelection) unitSelection.classList.remove('hidden');
                    if (manualSelectionControls) manualSelectionControls.classList.add('hidden');
                    if (autoSelectionNotice) autoSelectionNotice.classList.remove('hidden');

                    // Auto-check all unit checkboxes when KK is selected
                    unitCheckboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                } else {
                    if (unitSelection) unitSelection.classList.add('hidden');
                    if (manualSelectionControls) manualSelectionControls.classList.remove('hidden');
                    if (autoSelectionNotice) autoSelectionNotice.classList.add('hidden');

                    // Uncheck all unit checkboxes when switching to Admin
                    unitCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });

                    // Reset control checkboxes
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = false;
                    }
                    const unselectAllCheckbox = document.getElementById('unselect-all-units');
                    if (unselectAllCheckbox) {
                        unselectAllCheckbox.checked = false;
                    }
                }
            }

            function handleSelectAll() {
                if (selectAllCheckbox && selectAllCheckbox.checked) {
                    // Check all unit checkboxes
                    unitCheckboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                    // Uncheck unselect all
                    const unselectAllCheckbox = document.getElementById('unselect-all-units');
                    if (unselectAllCheckbox) {
                        unselectAllCheckbox.checked = false;
                    }
                }
            }

            function handleUnselectAll() {
                const unselectAllCheckbox = document.getElementById('unselect-all-units');
                if (unselectAllCheckbox && unselectAllCheckbox.checked) {
                    // Uncheck all unit checkboxes
                    unitCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    // Uncheck select all
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = false;
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                // Initialize assignment controls
                if (adminRadio && kkRadio) {
                    adminRadio.addEventListener('change', toggleUnitSelection);
                    kkRadio.addEventListener('change', toggleUnitSelection);

                    if (selectAllCheckbox) {
                        selectAllCheckbox.addEventListener('change', handleSelectAll);
                    }

                    const unselectAllCheckbox = document.getElementById('unselect-all-units');
                    if (unselectAllCheckbox) {
                        unselectAllCheckbox.addEventListener('change', handleUnselectAll);
                    }

                    // Initial check
                    toggleUnitSelection();
                }
            });
        </script>
    @endPushOnce

</x-super-admin-template>
