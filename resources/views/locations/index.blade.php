@extends('layouts.app') <!-- AdminLTE 4 Layout -->

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h3 class="card-title">Master Data Lokasi</h3>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">Tambah Lokasi</button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="locationTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Jurusan</th>
                        <th>Nama Lokasi</th>
                        <th>Tipe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locations as $loc)
                    <tr>
                        <td>{{ $loc->id }}</td>
                        <td>{{ $loc->department->name ?? 'Fasilitas Umum' }}</td>
                        <td>{{ $loc->name }}</td>
                        <td><span class="badge bg-secondary">{{ $loc->type }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection