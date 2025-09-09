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
            'link' => 'super-admin-rs-ik-add',
            'name' => 'Tambah',
            'params' => [
                'ss' => $ss['id'],
                'k' => $k['id'],
            ],
        ],
    ];
@endphp

<x-super-admin-template title="Tambah Indikator Kinerja - Super Admin">
    <x-partials.breadcrumbs.default :$breadCrumbs />
    <x-partials.heading.h2 text="tambah indikator kinerja" previousRoute="{{ route('super-admin-rs-ik', ['ss' => $ss['id'], 'k' => $k['id']]) }}" />
    <x-partials.heading.h3 title="Sasaran strategis" dataNumber="{{ $ss['number'] }}" dataText="{{ $ss['name'] }}" />
    <x-partials.heading.h3 title="Kegiatan" dataNumber="{{ $k['number'] }}" dataText="{{ $k['name'] }}" />
    <form action="" method="POST" class="flex flex-col gap-2">
        @csrf

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
                <x-partials.input.text name="name" title="Indikator kinerja" value="{{ old('name') }}" autofocus required />
            </div>
            <div class="flex flex-col gap-2 max-xl:flex-1">
                <x-partials.label.default for="type" title="Tipe data" text="Tipe Data" required />
                <x-partials.input.select name="type" title="Tipe data" :data="$type" onchange="typeAction(this.value)" required />

                @error('type')
                    <p class="text-xs text-red-500 lg:text-sm">{{ $message }}</p>
                @enderror

            </div>
        </div>

        <div class="flex flex-col gap-2">
            <x-partials.label.default for="assigned_to_type" title="Tugaskan kepada" text="Tugaskan kepada" required />
            <div class="flex gap-2">
                <input type="radio" name="assigned_to_type" id="admin" value="admin" checked>
                <label for="admin">Admin</label>
                <input type="radio" name="assigned_to_type" id="kk" value="kk">
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
                            <input type="checkbox" name="unit_id[]" id="unit_{{ $unit['value'] }}" value="{{ $unit['value'] }}" class="unit-checkbox rounded border-gray-300 text-primary focus:ring-primary">
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

        <div id="textSelection" class="hidden flex-col gap-3 rounded-lg border-2 border-dashed border-primary p-3">
            <div class="flex items-center gap-1">
                <x-partials.input.text name="selectionInput" title="Pilihan teks" />
                <button type="button" title="Tombol tambah pilihan" onclick="addSelection()" class="rounded-full bg-green-500 p-0.5 text-white hover:bg-green-400">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="aspect-square w-3 sm:w-4">
                        <path d="m12 0a12 12 0 1 0 12 12 12.013 12.013 0 0 0 -12-12zm0 22a10 10 0 1 1 10-10 10.011 10.011 0 0 1 -10 10zm1-11h4v2h-4v4h-2v-4h-4v-2h4v-4h2z" />
                    </svg>
                </button>
            </div>
            <div id="selectionContainer" class="flex flex-wrap items-center justify-start gap-1.5 text-primary max-md:text-sm">
            </div>
        </div>
        <x-partials.button.add style="ml-auto" submit />
    </form>

    <div id="selectionTemplate" class="hidden gap-1.5 rounded-lg border-2 border-primary p-1">
        <input id="selectionValue" type="hidden" name="selection[]" value="ada">
        <p id="selectionText">ada</p>
        <button type="button" title="Hapus" onclick="this.parentElement.remove()" class="aspect-square w-3 text-primary">
            <svg class="aspect-square w-full" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
            </svg>
            <span class="sr-only">Close modal</span>
        </button>
    </div>

    @pushOnce('script')
        <script>
            const selectionContainer = document.getElementById('selectionContainer');
            const selectionTemplate = document.getElementById('selectionTemplate');
            const selectionInput = document.getElementById('selectionInput');
            const selectionValue = document.getElementById('selectionValue');
            const selectionText = document.getElementById('selectionText');

            function typeAction(value) {
                document.getElementById('textSelection').classList.remove('hidden');
                document.getElementById('textSelection').classList.remove('flex');
                if (value === 'teks') {
                    document.getElementById('textSelection').classList.add('flex');
                } else {
                    document.getElementById('textSelection').classList.add('hidden');
                }
            }

            function addSelection() {
                if (selectionInput.value !== "") {
                    selectionText.innerText = selectionInput.value;
                    selectionValue.value = selectionInput.value;

                    const selection = selectionTemplate.cloneNode(true);

                    selection.classList.toggle('flex');
                    selection.classList.toggle('hidden');

                    selection.id = "";

                    selectionContainer.appendChild(selection);
                    selectionInput.value = "";
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                typeAction(document.getElementById('type').value);

                // Assignment controls
                const adminRadio = document.getElementById('admin');
                const kkRadio = document.getElementById('kk');
                const unitSelection = document.getElementById('unit-selection');
                const manualSelectionControls = document.getElementById('manual-selection-controls');
                const autoSelectionNotice = document.getElementById('auto-selection-notice');
                const selectAllCheckbox = document.getElementById('select-all-units');
                const unitCheckboxes = document.querySelectorAll('.unit-checkbox');

                function toggleUnitSelection() {
                    if (kkRadio.checked) {
                        unitSelection.classList.remove('hidden');
                        manualSelectionControls.classList.add('hidden');
                        autoSelectionNotice.classList.remove('hidden');

                        // Auto-check all unit checkboxes when KK is selected
                        unitCheckboxes.forEach(checkbox => {
                            checkbox.checked = true;
                        });
                    } else {
                        unitSelection.classList.add('hidden');
                        manualSelectionControls.classList.remove('hidden');
                        autoSelectionNotice.classList.add('hidden');

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
            });
        </script>
    @endPushOnce

</x-super-admin-template>
