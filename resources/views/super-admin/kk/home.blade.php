@extends('template')

@section('title', 'Kelompok Keahlian')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Daftar Kelompok Keahlian</h3>
                            @if ($user->role === 'super-admin' && $user->is_editor)
                                <a href="{{ route('super-admin-kk-add') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tambah
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form action="{{ route('super-admin-kk') }}" method="get">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Cari..." name="search"
                                            value="{{ $searchQuery }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Singkatan</th>
                                        <th>Nama</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item['short_name'] }}</td>
                                            <td>{{ $item['name'] }}</td>
                                            <td>
                                                @if ($user->role === 'super-admin' && $user->is_editor)
                                                    <a href="{{ route('super-admin-kk-edit', ['kk' => $item['id']]) }}"
                                                        class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> Ubah
                                                    </a>
                                                    <a href="{{ route('super-admin-kk-delete', ['kk' => $item['id']]) }}"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">Tidak ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection