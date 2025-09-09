@php
    $breadCrumbs = [
        [
            'link' => 'super-admin-users',
            'name' => 'Pengguna',
        ],
        [
            'link' => 'super-admin-users-edit',
            'name' => 'Ubah',
            'params' => [
                'user' => $user['id'],
            ],
        ],
    ];
@endphp

<x-super-admin-template title="Ubah Pengguna - Super Admin">
    <x-partials.breadcrumbs.default :$breadCrumbs />
    <x-partials.heading.h2 text="ubah pengguna" previous="super-admin-users" />
    <form action="" method="POST" class="flex flex-col gap-2">
        @csrf
        @method('PUT')

        <x-partials.label.default for="name" title="Nama pengguna" text="Nama Pengguna" required />
        <x-partials.input.text name="name" title="Nama pengguna" value="{{ $user['name'] }}" autofocus required />
        <x-partials.label.default for="email" title="Email" text="Email" required />
        <x-partials.input.text name="email" title="Email" value="{{ $user['email'] }}" required />
        <div class="*:p-2.5 max-sm:text-sm max-[320px]:text-xs">
            <div class="*:flex-1 *:rounded-lg *:p-1 *:bg-primary/80 flex gap-2.5 text-white">
                <button id="super-admin-button" type="button" title="Tombol akses super admin" onclick="switchSelection('super-admin-button', ['admin-button', 'kk-button'])" class="hover:bg-primary/70">Super Admin</button>
                <button id="admin-button" type="button" title="Tombol akses admin" onclick="switchSelection('admin-button', ['super-admin-button', 'kk-button'])" class="hover:bg-primary/70">Admin</button>
                <button id="kk-button" type="button" title="Tombol akses KK" onclick="switchSelection('kk-button', ['super-admin-button', 'admin-button'])" class="hover:bg-primary/70">KK</button>
            </div>
            <div id="selection" class="*:rounded-lg *:border *:border-slate-100 *:shadow *:p-1.5 *:gap-1 flex flex-wrap items-center justify-center gap-2 text-primary">
            </div>
        </div>

        @error('access')
            <p class="text-xs text-red-500 lg:text-sm">{{ $message }}</p>
        @enderror

        @error('unit')
            <p class="text-xs text-red-500 lg:text-sm">{{ $message }}</p>
        @enderror

        <x-partials.button.edit />
    </form>

    <div class="hidden">
        <div id="super-admin-selection">
            <div class="flex items-center justify-center">

                @if ($user['role'] === 'super admin' && $user['access'] === 'editor')
                    <x-partials.input.radio title="Super admin semua akses" name="access" id="editor" value="super-admin-editor" checked required />
                @else
                    <x-partials.input.radio title="Super admin semua akses" name="access" id="editor" value="super-admin-editor" required />
                @endif

                <label for="editor" title="Super admin semua akses">Semua akses</label>
            </div>
            <div class="flex items-center justify-center">

                @if ($user['role'] === 'super admin' && $user['access'] === 'viewer')
                    <x-partials.input.radio title="Super admin akses hanya melihat" name="access" id="viewer-super-admin" value="super-admin-viewer" checked required />
                @else
                    <x-partials.input.radio title="Super admin akses hanya melihat" name="access" id="viewer-super-admin" value="super-admin-viewer" required />
                @endif

                <label for="viewer-super-admin" title="Super admin akses hanya melihat">Hanya melihat</label>
            </div>
        </div>
        <div id="admin-selection">
            <x-partials.input.select name="unit" title="Pilih unit" :data="$data" />
            <div class="flex items-center justify-center">
                <input type="checkbox" title="Admin akses hanya melihat" name="access" id="viewer-admin" value="admin-viewer" class="rounded-md border-0 bg-primary/25 checked:bg-primary/80 focus:ring-primary/90" @checked($user['role'] === 'admin' && $user['access'] === 'viewer')>
                <label for="viewer-admin" title="Admin akses hanya melihat">Hanya melihat</label>
            </div>
        </div>
        <div id="kk-selection">
            <x-partials.input.select name="unit" title="Pilih Unit" :data="$kk_data" />
            <div class="flex items-center justify-center">
                <input type="checkbox" title="Unit akses hanya melihat" name="access" id="viewer-kk" value="kk-viewer" class="rounded-md border-0 bg-primary/25 checked:bg-primary/80 focus:ring-primary/90" @checked($user['role'] === 'kk' && $user['access'] === 'viewer')>
                <label for="viewer-kk" title="Unit akses hanya melihat">Hanya melihat</label>
            </div>
        </div>
    </div>

    @if ($errors->has('unit') || $user['role'] === 'admin')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('admin-button').click();
            });
        </script>
    @elseif ($user['role'] === 'kk')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('kk-button').click();
            });
        </script>
    @else
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('super-admin-button').click();
            });
        </script>
    @endif

    @pushOnce('script')
        <script>
            function addClass(id, arr) {
                let elementClass = document.getElementById(id).classList;
                arr.forEach(item => {
                    if (!elementClass.contains(item)) {
                        elementClass.add(item);
                    }
                });
            }

            function removeClass(id, arr) {
                let elementClass = document.getElementById(id).classList;
                arr.forEach(item => {
                    if (elementClass.contains(item)) {
                        elementClass.remove(item);
                    }
                });
            }

            function switchSelection(first, others) {
                document.getElementById(first).removeAttribute('onclick');
                
                if (Array.isArray(others)) {
                    others.forEach(other => {
                        document.getElementById(other).setAttribute('onclick', `switchSelection('${ other }', ['${ first }', '${ others.filter(o => o !== other).join("', '") }'])`);
                        removeClass(other, ['outline', 'outline-2', 'outline-offset-1', 'outline-primary']);
                    });
                } else {
                    document.getElementById(others).setAttribute('onclick', `switchSelection('${ others }', '${ first }')`);
                    removeClass(others, ['outline', 'outline-2', 'outline-offset-1', 'outline-primary']);
                }

                addClass(first, ['outline', 'outline-2', 'outline-offset-1', 'outline-primary']);
                
                let selectionId;
                if (first === 'super-admin-button') {
                    selectionId = 'super-admin-selection';
                } else if (first === 'admin-button') {
                    selectionId = 'admin-selection';
                } else if (first === 'kk-button') {
                    selectionId = 'kk-selection';
                }
                
                let newSelection = document.getElementById(selectionId);
                document.getElementById('selection').innerHTML = newSelection.innerHTML;
            }
        </script>
    @endPushOnce

</x-super-admin-template>
