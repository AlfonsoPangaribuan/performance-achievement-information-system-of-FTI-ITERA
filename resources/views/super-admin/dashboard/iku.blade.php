<x-super-admin-template title="Beranda Indikator Kinerja Utama - Super Admin">

    <x-partials.heading.h2 text="Indikator Kinerja Utama Tahun {{ $year }}" :$previousRoute />

    <!-- Admin Section -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-4 text-primary">Data Admin</h3>
        <div class="flex w-full flex-col gap-1 text-xs sm:text-sm md:text-base 2xl:text-lg">
            @if($adminData->isEmpty())
                <p class="text-gray-500 italic">Tidak ada data Indikator Kinerja Program untuk Admin</p>
            @else
                @foreach ($adminData as $sk)
                    <div class="flex w-full flex-col gap-1">
                        <h4>
                            {{ $loop->iteration }}. {{ $sk->sk }}
                        </h4>

                        @foreach ($sk->indikatorKinerjaKegiatan as $ikk)
                            <div class="flex w-full flex-col gap-1 border-l-2 border-dashed border-primary pl-2.5 md:pl-5 2xl:pl-8">
                                <h5>
                                    {{ $loop->parent->iteration }}.{{ $loop->iteration }}. {{ $ikk->ikk }}
                                </h5>

                                @foreach ($ikk->programStrategis as $ps)
                                    <div class="flex w-full flex-col gap-1 border-l-2 border-dashed border-primary/90 pl-2.5 md:pl-5 2xl:pl-8">
                                        <h6>
                                            {{ $loop->parent->parent->iteration }}.{{ $loop->parent->iteration }}.{{ $loop->iteration }}.
                                            {{ $ps->ps }}
                                        </h6>

                                        @foreach ($ps->indikatorKinerjaProgram as $ikp)
                                            <div class="flex w-full flex-col gap-1 border-l-2 border-dashed border-primary/80 pl-2.5 md:pl-5 2xl:pl-8">
                                                <h6>
                                                    {{ $loop->parent->parent->parent->iteration }}.{{ $loop->parent->parent->iteration }}.{{ $loop->parent->iteration }}.{{ $loop->iteration }}.
                                                    {{ $ikp->ikp }}
                                                </h6>
                                                <div class="flex w-full items-center justify-center overflow-x-auto">
                                                    <div class="aspect-video w-full min-w-96 max-w-screen-lg px-5">
                                                        <canvas id="admin-chart-{{ $ikp->id }}"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- KK Section -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-4 text-primary">Data KK</h3>
        <div class="flex w-full flex-col gap-1 text-xs sm:text-sm md:text-base 2xl:text-lg">
            @if($kkData->isEmpty())
                <p class="text-gray-500 italic">Tidak ada data Indikator Kinerja Program untuk KK</p>
            @else
                @foreach ($kkData as $sk)
                    <div class="flex w-full flex-col gap-1">
                        <h4>
                            {{ $loop->iteration }}. {{ $sk->sk }}
                        </h4>

                        @foreach ($sk->indikatorKinerjaKegiatan as $ikk)
                            <div class="flex w-full flex-col gap-1 border-l-2 border-dashed border-primary pl-2.5 md:pl-5 2xl:pl-8">
                                <h5>
                                    {{ $loop->parent->iteration }}.{{ $loop->iteration }}. {{ $ikk->ikk }}
                                </h5>

                                @foreach ($ikk->programStrategis as $ps)
                                    <div class="flex w-full flex-col gap-1 border-l-2 border-dashed border-primary/90 pl-2.5 md:pl-5 2xl:pl-8">
                                        <h6>
                                            {{ $loop->parent->parent->iteration }}.{{ $loop->parent->iteration }}.{{ $loop->iteration }}.
                                            {{ $ps->ps }}
                                        </h6>

                                        @foreach ($ps->indikatorKinerjaProgram as $ikp)
                                            <div class="flex w-full flex-col gap-1 border-l-2 border-dashed border-primary/80 pl-2.5 md:pl-5 2xl:pl-8">
                                                <h6>
                                                    {{ $loop->parent->parent->parent->iteration }}.{{ $loop->parent->parent->iteration }}.{{ $loop->parent->iteration }}.{{ $loop->iteration }}.
                                                    {{ $ikp->ikp }}
                                                    @if($ikp->unit)
                                                        <span class="text-sm text-gray-600">({{ $ikp->unit->name }})</span>
                                                    @endif
                                                </h6>
                                                <div class="flex w-full items-center justify-center overflow-x-auto">
                                                    <div class="aspect-video w-full min-w-96 max-w-screen-lg px-5">
                                                        <canvas id="kk-chart-{{ $ikp->id }}"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    @push('script')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            const setChart = (id, dataset) => {
                const canvas = document.getElementById(id);
                if (!canvas) return;

                const chartOptions = {
                    type: 'bar',
                    data: {
                        labels: dataset.unit,
                        datasets: [{
                                label: 'Target',
                                data: dataset.target,
                                backgroundColor: 'rgb(14 165 233)',
                                borderWidth: 1
                            },
                            {
                                label: 'Realisasi',
                                data: dataset.realization,
                                backgroundColor: 'rgb(244 63 94)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        resizeDelay: 250,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    },
                };

                new Chart(canvas, chartOptions);
            }

            // Admin charts
            @foreach ($adminIdLists->toArray() as $id)
                setChart("admin-chart-{{ $id }}", {!! json_encode($adminDatasets->toArray()[$id]) !!});
            @endforeach

            // KK charts
            @foreach ($kkIdLists->toArray() as $id)
                setChart("kk-chart-{{ $id }}", {!! json_encode($kkDatasets->toArray()[$id]) !!});
            @endforeach
        </script>
    @endpush

</x-super-admin-template>
