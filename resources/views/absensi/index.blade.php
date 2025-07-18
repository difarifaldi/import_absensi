@extends('layouts.section')
@section('content')
    <div class="px-3 py-4">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="font-weight-bold card-title">Buat Laporan PDF</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('generate.pdf') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">Upload File Excel</label>
                                        <div class="col-sm-10">
                                            <input type="file" class="form-control" name="file" accept=".xlsx, .xls"
                                                required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">Dari Tanggal</label>
                                        <div class="col-sm-10">
                                            <input type="date" class="form-control" name="start_date" required
                                                max="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">Sampai Tanggal</label>
                                        <div class="col-sm-10">
                                            <input type="date" class="form-control" name="end_date" required
                                                max="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-2 col-form-label">Pendapatan Host Live</label>
                                        <div class="col-sm-10">
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="pendapatan" value="0">
                                                <input class="form-check-input" type="checkbox" id="pendapatan"
                                                    name="pendapatan" value="1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">Generate PDF</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
