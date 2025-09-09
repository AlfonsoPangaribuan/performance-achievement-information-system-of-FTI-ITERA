@php
    $breadCrumbs = [
        [
            'link' => 'super-admin-iku-sk',
            'name' => 'IKU - Sasaran Kegiatan',
        ],
        [
            'link' => 'super-admin-iku-ikk',
            'name' => 'IKU - Indikator Kinerja Kegiatan',
            'params' => [
                'sk' => $sk['id'],
            ],
        ],
        [
            'link' => 'super-admin-iku-ps',
            'name' => 'IKU - Program Strategis',
            'params' => [
                'sk' => $sk['id'],
                'ikk' => $ikk['id'],
            ],
        ],
        [
            'link' => 'super-admin-iku-ikp',
            'name' => 'IKU - Indikator Kinerja Program',
            'params' => [
                'sk' => $sk['id'],
                'ikk' => $ikk['id'],
                'ps' => $ps['id'],
            ],
        ],
        [
            'link' => 'super-admin-iku-ikp-edit',
            'name' => 'Ubah',
            'params' => [
                'ikp' => $ikp['id'],
                'sk' => $sk['id'],
                'ikk' => $ikk['id'],
                'ps' => $ps['id'],
            ],
        ],
    ];
@endphp

<x-super-admin-template title="Ubah Indikator Kinerja Program - Super Admin">
    <x-partials.breadcrumbs.default :$breadCrumbs />
    <x-partials.heading.h2 text="ubah indikator kinerja program" :$previousRoute />
    <x-partials.heading.h3 title="Sasaran kinerja" dataNumber="{{ $sk['number'] }}" dataText="{{ $sk['name'] }}" />
    <x-partials.heading.h3 title="Indikator kinerja kegiatan" dataNumber="{{ $ikk['number'] }}"
        dataText="{{ $ikk['name'] }}" />
    <x-partials.heading.h3 title="Program strategis" dataNumber="{{ $ps['number'] }}" dataText="{{ $ps['name'] }}" />

    @if ($current)
        <label
            onclick="pushURL('status-toggle-confirmation', '{{ url(route('super-admin-iku-ikp-status', ['ikp' => $ikp['id'], 'sk' => $sk['id'], 'ikk' => $ikk['id'], 'ps' => $ps['id']])) }}')"
            class="flex items-center justify-start" data-modal-target="status-toggle-confirmation"
            data-modal-toggle="status-toggle-confirmation">
            <input type="checkbox" value="{{ $ikp['status'] }}" class="peer sr-only" @checked($ikp['status'] === 'aktif')
                disabled>
            <div class="peer flex w-11 cursor-pointer rounded-full bg-red-400 p-0.5 peer-checked:bg-green-400">
                <div
                    class="{{ $ikp['status'] === 'aktif' ? 'ml-auto' : 'mr-auto' }} aspect-square w-4 rounded-full bg-white">
                </div>
            </div>
        </label>
        <p class="text-xs font-bold text-red-400">*Merubah status akan menghapus realisasi capaian yang telah diinputkan
            setiap unit</p>
    @else
        <div title="Status penugasan : {{ $ikp['status'] }}"
            class="{{ $ikp['status'] === 'aktif' ? 'bg-green-500' : 'bg-red-500' }} ml-auto rounded-full p-3"></div>
    @endif

    <form action="" method="POST" class="flex flex-col gap-2">
        @csrf
        @method('PUT')

        <div class="flex flex-wrap gap-2">
            <div class="flex min-w-28 flex-col gap-2 max-sm:flex-1">
                <x-partials.label.default for="mode" title="Mode" text="Mode" required />
                <x-partials.input.select name="mode" title="Mode" :data="[['text' => $ikp['mode'], 'value' => $ikp['mode']]]" disabled />
            </div>
            <div class="flex min-w-28 flex-col gap-2 max-sm:flex-1">
                <x-partials.label.default for="number" title="Nomor" text="Nomor" required />
                <x-partials.input.select name="number" title="Nomor" :$data required />

                @error('number')
                    <p class="text-xs text-red-500 lg:text-sm">{{ $message }}</p>
                @enderror

            </div>
            <div class="flex min-w-28 flex-col gap-2 max-sm:flex-1">
                <x-partials.label.default for="type" title="Tipe pendukung" text="Tipe Pendukung" required />
                <x-partials.input.select name="type" title="Tipe pendukung" :data="$types" required />

                @error('type')
                    <p class="text-xs text-red-500 lg:text-sm">{{ $message }}</p>
                @enderror

            </div>
            <div class="flex flex-1 flex-col gap-2">
                <x-partials.label.default for="name" title="Indikator kinerja program"
                    text="Indikator Kinerja Program" required />
                <x-partials.input.text name="name" title="Indikator kinerja program" value="{{ $ikp['name'] }}"
                    autofocus required />
            </div>
        </div>
        <div class="flex flex-1 flex-col gap-2">
            <x-partials.label.default for="definition" title="Definisi operasional" text="Definisi Operasional"
                required />
            <x-partials.input.text name="definition" title="Definisi operasional" value="{{ $ikp['definition'] }}"
                required />
        </div>

        <div class="flex flex-col gap-2">
            <x-partials.label.default for="assigned_to_type" title="Tugaskan kepada" text="Tugaskan kepada" required />
            <div class="flex gap-2">
                <input type="radio" name="assigned_to_type" id="admin" value="admin" {{ $ikp['assigned_to_type'] === 'admin' ? 'checked' : '' }}>
                <label for="admin">Admin</label>
                <input type="radio" name="assigned_to_type" id="kk" value="kk" {{ $ikp['assigned_to_type'] === 'kk' ? 'checked' : '' }}>
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

        @if ($ikp['mode'] === 'table')
            <x-partials.label.default for="" title="Kolom" text="Kolom" />
            <div class="w-full overflow-x-auto rounded-lg">
                <table class="min-w-full max-lg:text-sm max-md:text-xs">
                    <thead>
                        <tr class="bg-primary/80 text-white *:whitespace-nowrap *:border *:px-5 *:py-2.5 *:font-normal">
                            <th title="Nomor">No</th>

                            @foreach ($columns as $column)
                                <th title="{{ $column['name'] }}">
                                    <div class="flex items-center justify-center gap-1">
                                        <p>{{ $column['name'] }}</p>

                                        @if ($column['file'])
                                            <p
                                                class="rounded-lg bg-white/50 px-1 py-0.5 text-xs text-primary xl:text-sm">
                                                File</p>
                                        @endif

                                    </div>
                                </th>
                            @endforeach

                        </tr>
                    </thead>
                </table>
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
