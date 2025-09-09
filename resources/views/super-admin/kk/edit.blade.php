@extends('template')

@section('title', 'Edit Kelompok Keahlian')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Edit Kelompok Keahlian</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('super-admin-kk-edit', ['kk' => $kk->id]) }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="short_name">Singkatan</label>
                                <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name"
                                    name="short_name" value="{{ old('short_name', $kk->short_name) }}" maxlength="10" required>
                                @error('short_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="name">Nama</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name', $kk->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="unit_id">Unit</label>
                                <select class="form-control @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id"
                                    required>
                                    <option value="" selected disabled>Pilih Unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}" @if (old('unit_id', $kk->unit_id) == $unit->id) selected @endif>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <a href="{{ route('super-admin-kk') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection