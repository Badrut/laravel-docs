@extends('layouts.app')

@section('title', 'Edit SPJ')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Penyesuaian Select2 agar setinggi form-control Bootstrap 5 */
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px);
            padding: 0.375rem 0.75rem;
            border: 1px solid #c5ccd6;
            border-radius: 0.375rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 2px);
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
            padding-left: 0;
        }
        .border-dashed {
            border: 2px dashed #c5ccd6 !important;
        }

        .preview-item {
            position: relative;
            display: inline-block;
        }
        .btn-remove-image {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e81500;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 12px;
            line-height: 24px;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 10;
        }
        .btn-remove-image:hover {
            background: #bd1100;
        }

        .cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        .cal-day-label {
            font-size: 11px;
            font-weight: 600;
            text-align: center;
            color: #6c757d;
            padding: 4px 0;
        }
        .cal-cell-empty { min-height: 52px; }
        .cal-cell-day {
            border-radius: 8px;
            border: 1.5px solid #dee2e6;
            background: #fff;
            padding: 5px 3px;
            min-height: 52px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            transition: border-color .15s, background .15s;
        }
        .cal-cell-day:hover:not(.cal-out):not(.cal-sunday) { border-color: #0d6efd; }
        .cal-cell-day.cal-checked  { background: #cfe2ff; border-color: #0d6efd; }
        .cal-cell-day.cal-sunday   { background: #f8d7da; border-color: #f5c2c7; cursor: not-allowed; }
        .cal-cell-day.cal-out      { background: transparent; border-color: transparent; }
        .cal-num { font-size: 13px; font-weight: 600; color: #212529; }
        .cal-checked .cal-num  { color: #084298; }
        .cal-sunday .cal-num   { color: #842029; }
        .cal-mo { font-size: 9px; text-transform: uppercase; letter-spacing: .4px; color: #adb5bd; }
        .cal-checked .cal-mo   { color: #084298; opacity:.7; }

        /* Wizard locked state (Untuk edit kita buka semua) */
        .nav-wizard .nav-link.wizard-locked {
            opacity: 0.45;
            pointer-events: none;
            cursor: not-allowed;
            background: #e9ecef !important;
            color: #6c757d !important;
        }
        .nav-wizard .nav-link.wizard-locked .wizard-step-icon {
            background: #adb5bd !important;
            color: #fff !important;
        }
        .nav-wizard .nav-link.wizard-completed .wizard-step-icon {
            background: #198754 !important;
            color: #fff !important;
        }

        /* Validation toast */
        .wizard-validation-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 320px;
            max-width: 450px;
            animation: slideInRight 0.4s ease;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
@endsection

@section('content')

@php
    $fmt = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('Y-m-d') : '';
    
    $schedules     = $project->schedules->sortBy('working_date');
    $firstSchedule = $schedules->first()?->working_date;
    $lastSchedule  = $schedules->last()?->working_date;
    
    $startVal = \Carbon\Carbon::canBeCreatedFromFormat($project->start_date ?? '', 'Y-m-d')
        ? $fmt($project->start_date)
        : ($firstSchedule ? $fmt($firstSchedule) : '');
        
    $endVal = \Carbon\Carbon::canBeCreatedFromFormat($project->end_date ?? '', 'Y-m-d')
        ? $fmt($project->end_date)
        : ($lastSchedule ? $fmt($lastSchedule) : '');
@endphp

<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-xl px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="edit"></i></div>
                            Edit Data Proyek {{ $project->project_name }}
                        </h1>
                        <div class="page-header-subtitle">Ubah informasi dokumen yang sudah tersimpan secara bertahap</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-xl px-4 mt-n10">
        
        <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data" id="spjForm">
            @csrf
            @method('PUT')

            @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <strong><i class="fas fa-exclamation-triangle me-2"></i>Validasi Gagal! Periksa kembali isian berikut:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="nav nav-pills nav-justified flex-column flex-xl-row nav-wizard" id="cardTab" role="tablist">
                        <a class="nav-item nav-link active wizard-completed" id="wizard1-tab" href="#wizard1" data-bs-toggle="tab" role="tab" aria-controls="wizard1" aria-selected="true" data-step="1">
                            <div class="wizard-step-icon">1</div>
                            <div class="wizard-step-text">
                                <div class="wizard-step-text-name">Informasi Kegiatan</div>
                                <div class="wizard-step-text-details">Data utama & pejabat</div>
                            </div>
                        </a>
                        <a class="nav-item nav-link wizard-completed" id="wizard2-tab" href="#wizard2" data-bs-toggle="tab" role="tab" aria-controls="wizard2" aria-selected="false" data-step="2">
                            <div class="wizard-step-icon">2</div>
                            <div class="wizard-step-text">
                                <div class="wizard-step-text-name">RAB & Pajak</div>
                                <div class="wizard-step-text-details">Pengaturan anggaran & item</div>
                            </div>
                        </a>
                        <a class="nav-item nav-link wizard-completed" id="wizard3-tab" href="#wizard3" data-bs-toggle="tab" role="tab" aria-controls="wizard3" aria-selected="false" data-step="3">
                            <div class="wizard-step-icon">3</div>
                            <div class="wizard-step-text">
                                <div class="wizard-step-text-name">Daftar Pekerja</div>
                                <div class="wizard-step-text-details">Daftar hadir tukang/tenaga</div>
                            </div>
                        </a>
                        <a class="nav-item nav-link wizard-completed" id="wizard4-tab" href="#wizard4" data-bs-toggle="tab" role="tab" aria-controls="wizard4" aria-selected="false" data-step="4">
                            <div class="wizard-step-icon">4</div>
                            <div class="wizard-step-text">
                                <div class="wizard-step-text-name">Jadwal Absensi</div>
                                <div class="wizard-step-text-details">Checklist hari kerja</div>
                            </div>
                        </a>
                        <a class="nav-item nav-link wizard-completed" id="wizard5-tab" href="#wizard5" data-bs-toggle="tab" role="tab" aria-controls="wizard5" aria-selected="false" data-step="5">
                            <div class="wizard-step-icon">5</div>
                            <div class="wizard-step-text">
                                <div class="wizard-step-text-name">Dokumentasi</div>
                                <div class="wizard-step-text-details">Upload foto & simpan</div>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="tab-content" id="cardTabContent">
                        
                        <div class="tab-pane py-5 py-xl-10 fade show active" id="wizard1" role="tabpanel" aria-labelledby="wizard1-tab">
                            <div class="row justify-content-center">
                                <div class="col-xxl-10 col-xl-12">
                                    <h3 class="text-primary mb-4"> Informasi Kegiatan & Administrasi</h3>
                                    <div class="row gx-5">
                                        
                                        <div class="col-lg-4">
                                            <h5 class="mb-3 text-muted"><i class="me-2" data-feather="info"></i>Data Utama</h5>
                                            <div class="mb-3">
                                                <label class="small mb-1">Nama Kegiatan <span class="text-danger">*</span></label>
                                                <input type="text" name="project_name" class="form-control" value="{{ old('project_name', $project->project_name) }}" required>
                                            </div>
                                            <div class="row gx-3">
                                                <div class="col-md-6 mb-3">
                                                    <label class="small mb-1">Tanggal Mulai <span class="text-danger">*</span></label>
                                                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $startVal) }}" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="small mb-1">Tanggal Selesai <span class="text-danger">*</span></label>
                                                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $endVal) }}" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="small mb-1">Kontraktor / Toko</label>
                                                <select name="contractor_id" id="contractor_id" class="form-select select2" style="width: 100%;">
                                                    <option value="">Pilih Kontraktor</option>
                                                    @foreach($contractors as $c)
                                                        <option value="{{ $c->id }}" {{ ($project->contractor_id == $c->id) ? 'selected' : '' }}>
                                                            {{ $c->company_name }} ({{ $c->person_in_charge ?? '-' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="row gx-3">
                                                <div class="col-md-6 mb-3">
                                                    <label class="small mb-1">Tanggal Dokumen RAB</label>
                                                    <input type="date" name="budget_date" class="form-control" value="{{ old('budget_date', $fmt($project->budget_date)) }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="small mb-1">Tgl Masukan Penawaran</label>
                                                    <input type="date" name="offer_date" class="form-control" value="{{ old('offer_date', $fmt($project->offer_date)) }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <h5 class="mb-3 text-muted"><i class="me-2" data-feather="users"></i>Data Pejabat (Snapshot)</h5>
                                            <div class="mb-3">
                                                <label class="small mb-1">Kepala Sekolah</label>
                                                <input type="text" name="principal_name" class="form-control" value="{{ old('principal_name', $project->principal_name) }}">
                                                <input type="text" name="principal_nip" class="form-control mt-1 small" value="{{ old('principal_nip', $project->principal_nip) }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="small mb-1">Waka Sarpras</label>
                                                <input type="text" name="facilities_head_name" class="form-control" value="{{ old('facilities_head_name', $project->facilities_head_name) }}">
                                                <input type="text" name="facilities_head_nip" class="form-control mt-1" value="{{ old('facilities_head_nip', $project->facilities_head_nip) }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="small mb-1">Bendahara</label>
                                                <input type="text" name="treasurer_name" class="form-control" value="{{ old('treasurer_name', $project->treasurer_name) }}">
                                                <input type="text" name="treasurer_nip" class="form-control mt-1" value="{{ old('treasurer_nip', $project->treasurer_nip) }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="small mb-1">Kasubag TU</label>
                                                <input type="text" name="admin_head_name" class="form-control" value="{{ old('admin_head_name', $project->admin_head_name) }}">
                                                <input type="text" name="admin_head_nip" class="form-control mt-1" value="{{ old('admin_head_nip', $project->admin_head_nip) }}">
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <h5 class="mb-3 text-muted"><i class="me-2" data-feather="file-text"></i>Nomor & Tanggal Surat</h5>
                                            <div class="p-3 bg-light rounded mb-3">
                                                <label class="small mb-1 fw-bold">1. Surat Undangan</label>
                                                <input type="text" name="invitation_number" class="form-control mb-1" value="{{ old('invitation_number', $project->invitation_number) }}">
                                                <input type="date" name="invitation_date" class="form-control" value="{{ old('invitation_date', $fmt($project->invitation_date)) }}">
                                            </div>
                                            <div class="p-3 bg-light rounded mb-3">
                                                <label class="small mb-1 fw-bold">2. Surat Kontraktor</label>
                                                <input type="text" name="contractor_number" class="form-control mb-1" value="{{ old('contractor_number', $project->contractor_number) }}">
                                                <input type="date" name="contractor_date" class="form-control" value="{{ old('contractor_date', $fmt($project->contractor_date)) }}">
                                            </div>
                                            <div class="p-3 bg-light rounded mb-3">
                                                <label class="small mb-1 fw-bold">3. Surat Perintah Kerja (SPK)</label>
                                                <input type="text" name="work_order_number" class="form-control mb-1" value="{{ old('work_order_number', $project->work_order_number) }}">
                                                <input type="date" name="work_order_date" class="form-control" value="{{ old('work_order_date', $fmt($project->work_order_date)) }}">
                                            </div>
                                            <div class="p-3 bg-light rounded mb-3">
                                                <label class="small mb-1 fw-bold">4. BA Penyerahan</label>
                                                <input type="text" name="handover_report_number" class="form-control mb-1" value="{{ old('handover_report_number', $project->handover_report_number) }}">
                                                <input type="date" name="handover_report_date" class="form-control" value="{{ old('handover_report_date', $fmt($project->handover_report_date)) }}">
                                            </div>
                                            <div class="p-3 bg-light rounded mb-3">
                                                <label class="small mb-1 fw-bold">5. Laporan Pembayaran</label>
                                                <input type="text" name="payment_report_number" class="form-control mb-1" value="{{ old('payment_report_number', $project->payment_report_number) }}">
                                                <input type="date" name="payment_report_date" class="form-control" value="{{ old('payment_report_date', $fmt($project->payment_report_date)) }}">
                                            </div>
                                            <div class="p-3 bg-light rounded mb-3">
                                                <label class="small mb-1 fw-bold">Kwitansi & Pembayaran</label>
                                                <input type="text" name="receipt_number" class="form-control mb-1" value="{{ old('receipt_number', $project->receipt_number) }}">
                                                <input type="date" name="receipt_date" class="form-control" value="{{ old('receipt_date', $fmt($project->receipt_date)) }}">
                                                <input type="text" name="receipt_receiver" class="form-control mt-1" value="{{ old('receipt_receiver', $project->receipt_receiver) }}">
                                            </div>
                                        </div>

                                    </div>
                                    <hr class="my-4" />
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary nav-btn" data-target="#wizard2-tab" type="button">Lanjut ke Rincian RAB <i class="fas fa-arrow-right ms-2"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane py-5 py-xl-10 fade" id="wizard2" role="tabpanel" aria-labelledby="wizard2-tab">
                            <div class="row justify-content-center">
                                <div class="col-xxl-10 col-xl-12">
                                    <h3 class="text-primary mb-4">Rincian Anggaran Biaya (RAB) & Pajak</h3>

                                    <div class="card card-icon mb-4">
                                        <div class="row no-gutters">
                                            <div class="col-auto card-icon-aside bg-primary">
                                                <i class="fas fa-percent text-white-50"></i>
                                            </div>
                                            <div class="col">
                                                <div class="card-body py-4">
                                                    <h5 class="card-title text-primary mb-2">Potongan Pajak (Opsional)</h5>
                                                    <p class="card-text small text-muted mb-4">Isi dengan angka persen saja. Kosongkan jika tidak ada pemotongan pajak.</p>
                                                    
                                                    <div class="row gx-3">
                                                        <div class="col-md-2 col-sm-4 mb-3 mb-md-0">
                                                            <label class="small mb-1">PPN (%)</label>
                                                            <input type="number" step="0.01" name="tax_vat" class="form-control" value="{{ old('tax_vat', $project->tax_vat) }}">
                                                        </div>
                                                        <div class="col-md-2 col-sm-4 mb-3 mb-md-0">
                                                            <label class="small mb-1">PPh 22 (%)</label>
                                                            <input type="number" step="0.01" name="tax_pph22" class="form-control" value="{{ old('tax_pph22', $project->tax_pph22) }}">
                                                        </div>
                                                        <div class="col-md-2 col-sm-4 mb-3 mb-md-0">
                                                            <label class="small mb-1">PPh 21 (%)</label>
                                                            <input type="number" step="0.01" name="tax_pph21" class="form-control" value="{{ old('tax_pph21', $project->tax_pph21) }}">
                                                        </div>
                                                        <div class="col-md-2 col-sm-4 mb-3 mb-md-0">
                                                            <label class="small mb-1">PPh 23 (%)</label>
                                                            <input type="number" step="0.01" name="tax_pph23" class="form-control" value="{{ old('tax_pph23', $project->tax_pph23) }}">
                                                        </div>
                                                        <div class="col-md-3 col-sm-4">
                                                            <label class="small mb-1">PPh Final (%)</label>
                                                            <input type="number" step="0.01" name="tax_pph_final" class="form-control" value="{{ old('tax_pph_final', $project->tax_pph_final) }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card card-icon mb-4">
                                        <div class="row no-gutters">
                                            <div class="col-auto card-icon-aside bg-success">
                                                <i class="fas fa-file-excel text-white-50"></i>
                                            </div>
                                            <div class="col">
                                                <div class="card-body py-4">
                                                    <h5 class="card-title text-success mb-2">Import RAB dari Excel (Opsional)</h5>
                                                    <p class="card-text small text-muted mb-4">Upload file Excel RAB dengan format sesuai template. Data <strong>Alat & Bahan</strong> akan otomatis diisi ke tabel di bawah.</p>
                                                    
                                                    <div class="row align-items-end gx-3">
                                                        <div class="col-md-8">
                                                            <input type="file" id="rabExcelInput" class="form-control" accept=".xlsx,.xls">
                                                        </div>
                                                        <div class="col-md-4 mt-2 mt-md-0">
                                                            <button type="button" class="btn btn-success w-100" id="btn-import-rab" disabled>
                                                                <i class="fas fa-upload me-2"></i>Import Data RAB
                                                            </button>
                                                        </div>
                                                    </div>
                                                    
                                                    <div id="import-rab-status" class="mt-2 d-none">
                                                        <div class="d-flex align-items-center">
                                                            <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div>
                                                            <span class="small text-muted">Sedang membaca file Excel...</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="text-muted fw-bold mb-3"><i class="fas fa-list me-2"></i>Daftar Item RAB</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover" id="rabTable">
                                            <thead> 
                                                <tr>
                                                    <th>Jenis</th>
                                                    <th>Nama Item</th>
                                                    <th width="10%">Volume</th>
                                                    <th width="15%">Satuan</th>
                                                    <th width="20%">Harga Satuan</th>
                                                    <th width="5%" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="item-body">
                                                @forelse($project->items as $index => $item)
                                                    <tr>
                                                        <td>
                                                            <select name="items[{{ $index }}][type]" class="form-control select2" style="width: 100%;">
                                                                <option value="material" {{ $item->type == 'material' ? 'selected' : '' }}>Alat & Bahan</option>
                                                                <option value="labor" {{ $item->type == 'labor' ? 'selected' : '' }}>Upah Tenaga</option>
                                                            </select>
                                                        </td>
                                                        <td><input type="text" name="items[{{ $index }}][item_name]" class="form-control" value="{{ $item->item_name }}" required></td>
                                                        <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control" value="{{ $item->quantity }}" required></td>
                                                        <td><input type="text" name="items[{{ $index }}][unit]" class="form-control" value="{{ $item->unit }}" required></td>
                                                        <td><input type="number" name="items[{{ $index }}][unit_price]" class="form-control" value="{{ $item->unit_price }}" required></td>
                                                        <td class="text-center align-middle">
                                                            <button type="button" class="btn btn-danger btn-icon hapus-baris"><i class="fas fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td>
                                                            <select name="items[0][type]" class="form-control select2" style="width: 100%;">
                                                                <option value="material">Alat & Bahan</option>
                                                                <option value="labor">Upah Tenaga</option>
                                                            </select>
                                                        </td>
                                                        <td><input type="text" name="items[0][item_name]" class="form-control" required></td>
                                                        <td><input type="number" name="items[0][quantity]" class="form-control" required></td>
                                                        <td><input type="text" name="items[0][unit]" class="form-control" required></td>
                                                        <td><input type="number" name="items[0][unit_price]" class="form-control" required></td>
                                                        <td class="text-center align-middle">
                                                            <button type="button" class="btn btn-danger btn-icon hapus-baris"><i class="fas fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mb-4 mt-2">
                                        <button type="button" class="btn btn-primary btn-sm" id="btn-tambah-item">
                                            <i class="fas fa-plus me-2"></i> Tambah Item RAB
                                        </button>
                                    </div>
                                    <hr class="my-4" />
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-danger nav-btn" data-target="#wizard1-tab" type="button"><i class="fas fa-arrow-left me-2"></i> Kembali</button>
                                        <button class="btn btn-primary nav-btn" data-target="#wizard3-tab" type="button">Selanjutnya <i class="fas fa-arrow-right ms-2"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane py-5 fade" id="wizard3" role="tabpanel" aria-labelledby="wizard3-tab">
                            <div class="row justify-content-center">
                                <div class="col-xxl-8 col-xl-10">
                                    <h3 class="text-primary mb-4">Pilih Pekerja yang Bertugas</h3>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="workerTable">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Nama Pekerja (Ambil dari Master)</th>
                                                    <th width="10%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="worker-body">
                                                @forelse($project->workers as $pw)
                                                    <tr>
                                                        <td>
                                                            <select name="workers[]" class="form-control select2 worker-select" style="width: 100%;">
                                                                <option value="">Pilih Pekerja...</option>
                                                                @foreach($workers as $worker)
                                                                    <option value="{{ $worker->id }}" {{ $pw->id == $worker->id ? 'selected' : '' }}>
                                                                        {{ $worker->name }} ({{ $worker->role->role_name }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-danger btn-icon hapus-baris"><i class="fas fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td>
                                                            <select name="workers[]" class="form-control select2 worker-select" style="width: 100%;">
                                                                <option value="">Pilih Pekerja...</option>
                                                                @foreach($workers as $worker)
                                                                    <option value="{{ $worker->id }}">{{ $worker->name }} ({{ $worker->role->role_name }})</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-danger btn-icon hapus-baris"><i class="fas fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <button type="button" class="btn btn-primary btn-sm" id="btn-tambah-worker">
                                            <i class="fas fa-plus me-2"></i>Tambah Baris Pekerja
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddWorker">
                                            <i class="fas fa-user-plus me-2"></i>Master Worker Baru
                                        </button>
                                    </div>
                                    <hr class="my-4" />
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-danger nav-btn" data-target="#wizard2-tab" type="button">Kembali</button>
                                        <button class="btn btn-primary nav-btn" id="btn-to-step4" data-target="#wizard4-tab" type="button">Atur Jadwal <i class="fas fa-arrow-right ms-2"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane py-5 fade" id="wizard4" role="tabpanel" aria-labelledby="wizard4-tab">
                            <div class="row justify-content-center">
                                <div class="col-xxl-8 col-xl-10">
                                    <h3 class="text-primary mb-4">Atur Hari Kerja (Absensi)</h3>
                                    <p class="text-muted text-center small">Centang tanggal aktif kerja. Data jadwal sebelumnya otomatis termuat.</p>
                                    <div class="card border-primary bg-light">
                                        <div class="card-body">
                                            <div id="calendar-wrapper" class="row g-2 text-center justify-content-center">
                                                <div class="col-12 text-muted py-5">
                                                    <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                                    <p>Silakan pastikan Tanggal Mulai dan Selesai di Langkah 1 sudah terisi.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-4" />
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-danger nav-btn" data-target="#wizard3-tab" type="button">Kembali</button>
                                        <button class="btn btn-primary nav-btn" data-target="#wizard5-tab" type="button">Dokumentasi <i class="fas fa-arrow-right ms-2"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane py-5 py-xl-10 fade" id="wizard5" role="tabpanel" aria-labelledby="wizard5-tab">
                            <div class="row justify-content-center">
                                <div class="col-xxl-8 col-xl-10">
                                    <h3 class="text-primary mb-4 text-center">Upload Dokumentasi & Kop Surat</h3>

                                    {{-- Foto Kegiatan Lama --}}
                                    @if($project->images->count() > 0)
                                    <div class="card bg-light shadow-none border p-3 mb-4">
                                        <h6 class="fw-bold text-muted mb-3"><i class="fas fa-images me-2"></i>Foto Tersimpan Sebelumnya</h6>
                                        <div class="d-flex flex-wrap gap-2" id="old-images-container">
                                            @foreach($project->images as $img)
                                                <div class="preview-item old-image-item" id="old-img-{{ $img->id }}">
                                                    <img src="{{ asset('storage/' . $img->file_path) }}" class="img-thumbnail rounded bg-white" style="height: 110px; width: 110px; object-fit: cover;" alt="Foto Lama">
                                                    <button type="button" class="btn-remove-image remove-old-image" data-id="{{ $img->id }}" title="Hapus Gambar">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Foto Kegiatan Baru --}}
                                    <div class="card bg-light shadow-none border-dashed p-4 mb-4 text-center">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                        <h5>Tambah Foto Kegiatan Baru</h5>
                                        <p class="text-muted small mb-0">Upload foto jika ingin menambahkan dokumentasi ke dokumen SPJ ini.</p>
                                        <input type="file" name="images[]" id="imageInput" class="form-control mt-3" multiple accept="image/*">
                                        <div id="preview-container" class="row g-3 mt-3 text-start"></div>
                                    </div>

                                    {{-- Kop Surat (Dropdown Selector) --}}
                                    <div class="row g-3 mb-4">
                                        {{-- Kop Sekolah --}}
                                        <div class="col-md-6">
                                            <div class="card h-100 shadow-none border">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-school fa-2x text-info mb-2"></i>
                                                    <h6 class="fw-bold">Kop Surat Sekolah</h6>
                                                    <p class="text-muted small">Pilih kop surat sekolah yang akan digunakan.</p>
                                                    <select name="kop_school_id" id="kopSchoolSelect" class="form-select mt-2">
                                                        <option value="">-- Tidak pakai / Default --</option>
                                                        @foreach($kopSekolah as $kop)
                                                            <option value="{{ $kop->id }}" data-img="{{ asset('storage/' . $kop->image_path) }}" {{ $project->kop_school_id == $kop->id ? 'selected' : '' }}>
                                                                {{ $kop->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div id="kop-school-preview" class="mt-3 {{ $project->kop_school_id ? '' : 'd-none' }}">
                                                        <img id="kop-school-img" src="{{ $project->kopSchool ? asset('storage/' . $project->kopSchool->image_path) : '' }}" alt="Preview Kop Sekolah" class="img-fluid rounded border" style="max-height:120px; object-fit:contain;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Kop Kontraktor --}}
                                        <div class="col-md-6">
                                            <div class="card h-100 shadow-none border">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-building fa-2x text-warning mb-2"></i>
                                                    <h6 class="fw-bold">Kop Surat Kontraktor</h6>
                                                    <p class="text-muted small">Pilih kop surat kontraktor yang akan digunakan.</p>
                                                    <select name="kop_contractor_id" id="kopContractorSelect" class="form-select mt-2">
                                                        <option value="">-- Tidak pakai / Default --</option>
                                                        @foreach($kopKontraktor as $kop)
                                                            <option value="{{ $kop->id }}" data-img="{{ asset('storage/' . $kop->image_path) }}" {{ $project->kop_contractor_id == $kop->id ? 'selected' : '' }}>
                                                                {{ $kop->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div id="kop-contractor-preview" class="mt-3 {{ $project->kop_contractor_id ? '' : 'd-none' }}">
                                                        <img id="kop-contractor-img" src="{{ $project->kopContractor ? asset('storage/' . $project->kopContractor->image_path) : '' }}" alt="Preview Kop Kontraktor" class="img-fluid rounded border" style="max-height:120px; object-fit:contain;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4" />
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-danger nav-btn" data-target="#wizard4-tab" type="button"><i class="fas fa-arrow-left me-2"></i> Kembali</button>
                                        <button class="btn btn-success" type="submit" id="btn-submit"><i class="fas fa-save me-2"></i> Update & Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<div class="modal fade" id="modalAddWorker" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Master Pekerja Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-error" class="alert alert-danger d-none"></div>
                <div class="mb-3">
                    <label class="small mb-1">Nama Lengkap</label>
                    <input type="text" id="m_name" class="form-control" placeholder="Nama tukang/tenaga">
                </div>
                <div class="mb-3">
                    <label class="small mb-1">Role</label>
                    <select id="m_role" class="form-select">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-save-master-worker">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();

        // ========== WIZARD VALIDATION ==========
        let completedSteps = [true, true, true, true, true]; // Di edit semua dianggap true

        function showWizardAlert(message, type) {
            $('.wizard-validation-alert').remove();
            let icon = type === 'danger' ? 'fa-exclamation-circle' : (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle');
            let alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show wizard-validation-alert shadow-lg" role="alert">
                    <i class="fas ${icon} me-2"></i><strong>${message}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
            $('body').append(alertHtml);
            setTimeout(() => { $('.wizard-validation-alert').fadeOut(500, function() { $(this).remove(); }); }, 8000);
        }

        function showWizardErrors(errors) {
            $('.wizard-validation-alert').remove();
            let listHtml = errors.map(e => `<li>${e}</li>`).join('');
            let alertHtml = `
                <div class="alert alert-danger alert-dismissible fade show wizard-validation-alert shadow-lg" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>Validasi Gagal!</strong>
                    <ul class="mb-0 mt-2 small">${listHtml}</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
            $('body').append(alertHtml);
            setTimeout(() => { $('.wizard-validation-alert').fadeOut(500, function() { $(this).remove(); }); }, 10000);
        }

        function validateStep(stepNum) {
            let errors = [];
            if (stepNum === 1) {
                if (!$('input[name="project_name"]').val().trim()) errors.push('Nama Kegiatan wajib diisi');
                if (!$('input[name="start_date"]').val()) errors.push('Tanggal Mulai wajib diisi');
                if (!$('input[name="end_date"]').val()) errors.push('Tanggal Selesai wajib diisi');
                if ($('input[name="start_date"]').val() && $('input[name="end_date"]').val()) {
                    if ($('input[name="start_date"]').val() > $('input[name="end_date"]').val()) {
                        errors.push('Tanggal Selesai harus setelah Tanggal Mulai');
                    }
                }
            } else if (stepNum === 2) {
                let hasItem = false;
                $('#item-body tr').each(function() {
                    let name = $(this).find('input[name$="[item_name]"]').val();
                    let qty = $(this).find('input[name$="[quantity]"]').val();
                    let unit = $(this).find('input[name$="[unit]"]').val();
                    let price = $(this).find('input[name$="[unit_price]"]').val();
                    if (name && name.trim()) hasItem = true;
                    if (name && name.trim() && (!qty || !unit || !price)) {
                        errors.push('Lengkapi semua kolom item RAB: ' + name);
                    }
                });
                if (!hasItem) errors.push('Minimal 1 item RAB harus diisi');
            } else if (stepNum === 3) {
                let hasWorker = false;
                $('#worker-body select[name="workers[]"]').each(function() {
                    if ($(this).val()) hasWorker = true;
                });
                if (!hasWorker) errors.push('Minimal 1 pekerja harus dipilih');
            } else if (stepNum === 4) {
                let hasDate = $('input[name="attendance_dates[]"]:checked').length > 0;
                if (!hasDate) errors.push('Minimal 1 hari kerja harus dicentang');
            }
            return errors;
        }

        function goToStep(stepNum) {
            let tab = new bootstrap.Tab(document.querySelector(`#wizard${stepNum}-tab`));
            tab.show();
            if (typeof feather !== 'undefined') feather.replace();
        }

        // Nav-btn click handler with validation
        $(document).on('click', '.nav-btn', function() {
            var targetTab = $(this).data('target');
            var targetStepNum = parseInt(targetTab.replace('#wizard', '').replace('-tab', ''));
            
            let currentStep = parseInt($('.nav-wizard .nav-link.active').data('step'));

            // Going forward: validate current step
            if (targetStepNum > currentStep) {
                let errors = validateStep(currentStep);
                if (errors.length > 0) {
                    showWizardErrors(errors);
                    return;
                }
            }
            
            goToStep(targetStepNum);
        });

        let itemIdx = {{ max($project->items->count(), 1) }};

        $('#btn-tambah-item').click(function() {
            let row = `
            <tr>
                <td>
                    <select name="items[${itemIdx}][type]" class="form-control select2" style="width: 100%;">
                        <option value="material">Alat & Bahan</option>
                        <option value="labor">Upah Tenaga</option>
                    </select>
                </td>
                <td><input type="text" name="items[${itemIdx}][item_name]" class="form-control" required></td>
                <td><input type="number" name="items[${itemIdx}][quantity]" class="form-control" required></td>
                <td><input type="text" name="items[${itemIdx}][unit]" class="form-control" required></td>
                <td><input type="number" name="items[${itemIdx}][unit_price]" class="form-control" required></td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-icon hapus-baris"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>`;
            $('#item-body').append(row);
            $('.select2').select2();
            itemIdx++;
        });

        $('#btn-tambah-worker').click(function() {
            let options = $('.worker-select').first().html(); 
            options = options.replace(/data-select2-id="[^"]*"/g, ''); 
            options = options.replace(/selected="selected"/g, ''); 

            let row = `
            <tr>
                <td>
                    <select name="workers[]" class="form-control select2 worker-select" style="width: 100%;">
                        ${options}
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-icon hapus-baris">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
            
            $('#worker-body').append(row);
            let newSelect = $('#worker-body tr:last-child select');
            newSelect.val(''); 
            newSelect.select2();
        });
        
        $(document).on('click', '.hapus-baris', function() {
            $(this).closest('tr').remove();
        });

        // ========== IMPORT RAB DARI EXCEL ==========
        $('#rabExcelInput').on('change', function() {
            $('#btn-import-rab').prop('disabled', !this.files.length);
        });

        $('#btn-import-rab').click(function() {
            let fileInput = $('#rabExcelInput')[0];
            if (!fileInput.files.length) return;

            let formData = new FormData();
            formData.append('rab_file', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            let $btn = $(this);
            let originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Membaca Excel...');
            $('#import-rab-status').removeClass('d-none');

            $.ajax({
                url: '{{ route("projects.import-rab") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success && res.items.length > 0) {
                        $('#item-body').empty();
                        itemIdx = 0;

                        res.items.forEach(function(item) {
                            let typeSelected = {
                                material: item.type === 'material' ? 'selected' : '',
                                labor: item.type === 'labor' ? 'selected' : ''
                            };

                            let row = `
                            <tr>
                                <td>
                                    <select name="items[${itemIdx}][type]" class="form-control select2" style="width: 100%;">
                                        <option value="material" ${typeSelected.material}>Alat & Bahan</option>
                                        <option value="labor" ${typeSelected.labor}>Upah Tenaga</option>
                                    </select>
                                </td>
                                <td><input type="text" name="items[${itemIdx}][item_name]" class="form-control" value="${item.item_name}" required></td>
                                <td><input type="number" name="items[${itemIdx}][quantity]" class="form-control" value="${item.quantity}" required></td>
                                <td><input type="text" name="items[${itemIdx}][unit]" class="form-control" value="${item.unit}" required></td>
                                <td><input type="number" name="items[${itemIdx}][unit_price]" class="form-control" value="${item.unit_price}" required></td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-danger btn-icon hapus-baris" title="Hapus"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>`;
                            $('#item-body').append(row);
                            itemIdx++;
                        });

                        $('.select2').select2();
                        showWizardAlert(res.message, 'success');
                    } else {
                        showWizardAlert(res.message || 'Tidak ada data ditemukan.', 'warning');
                    }
                },
                error: function(xhr) {
                    let msg = 'Gagal import file Excel.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    showWizardAlert(msg, 'danger');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalText);
                    $('#import-rab-status').addClass('d-none');
                    $('#rabExcelInput').val('');
                    $('#btn-import-rab').prop('disabled', true);
                }
            });
        });
    });

// ========== PREVIEW FOTO BARU ==========
let selectedFiles = new DataTransfer(); 

$('#imageInput').on('change', function(e) {
    let previewContainer = $('#preview-container');
    previewContainer.empty();
    selectedFiles = new DataTransfer(); 

    let files = e.target.files;

    if (files.length > 0) {
        $.each(files, function(index, file) {
            if (file.type.match('image.*')) {
                selectedFiles.items.add(file); 
                
                let reader = new FileReader();
                reader.onload = function(e) {
                    let imgHtml = `
                        <div class="col-4 col-sm-3 preview-item" id="file-${index}">
                            <img src="${e.target.result}" class="img-thumbnail rounded w-100 bg-white" style="height: 110px; object-fit: cover;" alt="Preview">
                            <button type="button" class="btn-remove-image" data-index="${index}" title="Hapus Gambar">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    `;
                    previewContainer.append(imgHtml);
                }
                reader.readAsDataURL(file);
            }
        });
        $('#imageInput')[0].files = selectedFiles.files;
    }
});

// ========== HAPUS PREVIEW FOTO BARU ==========
$(document).on('click', '.btn-remove-image', function() {
    let fileIndexToRemove = $(this).data('index'); 
    let newFiles = new DataTransfer(); 
    for (let i = 0; i < selectedFiles.files.length; i++) {
        if (i !== fileIndexToRemove) {
            newFiles.items.add(selectedFiles.files[i]);
        }
    }
    selectedFiles = newFiles;
    $('#imageInput')[0].files = selectedFiles.files;
    $(this).closest('.preview-item').fadeOut(300, function() { 
        $(this).remove(); 
        if(selectedFiles.files.length === 0){ $('#imageInput').val(''); }
    });
});

// ========== HAPUS FOTO LAMA (KHUSUS EDIT) ==========
$(document).on('click', '.remove-old-image', function() {
    let imgId = $(this).data('id');
    $('#spjForm').append(`<input type="hidden" name="delete_images[]" value="${imgId}">`);
    $(this).closest('.old-image-item').fadeOut(300, function() { 
        $(this).remove(); 
    });
});

// ========== KALENDER ABSENSI ==========
let dbSchedules = @json($project->schedules->pluck('working_date')->map(function($date) {
    return \Carbon\Carbon::parse($date)->format('Y-m-d');
})->toArray());

function generateCalendar() {
    let start = $('input[name="start_date"]').val();
    let end = $('input[name="end_date"]').val();
    let wrapper = $('#calendar-wrapper');

    if (!start || !end) return;

    const MONTHS_ID = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const DAY_LABELS = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

    let [sy, sm, sd] = start.split('-').map(Number);
    let [ey, em, ed] = end.split('-').map(Number);
    let startDate = new Date(sy, sm - 1, sd);
    let endDate   = new Date(ey, em - 1, ed);
    let state     = {};

    let cur = new Date(startDate);
    let isInitialLoad = (window.calendarInitialized === undefined && dbSchedules.length > 0);

    while (cur <= endDate) {
        let key = toKey(cur);
        if (isInitialLoad) {
            state[key] = dbSchedules.includes(key);
        } else {
            state[key] = cur.getDay() !== 0;
        }
        cur.setDate(cur.getDate() + 1);
    }
    
    window.calendarInitialized = true;

    function toKey(d) {
        let y = d.getFullYear();
        let m = String(d.getMonth() + 1).padStart(2, '0');
        let dd = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${dd}`;
    }

    function renderAll() {
        wrapper.empty();

        wrapper.append(`
            <div class="d-flex justify-content-between align-items-center mb-3 w-100">
                <div>
                    <span class="fw-semibold" style="font-size:14px;">Jadwal Hari Kerja</span>
                    <div class="text-muted" style="font-size:12px;">
                        ${startDate.getDate()} ${MONTHS_ID[startDate.getMonth()]} ${startDate.getFullYear()} – 
                        ${endDate.getDate()} ${MONTHS_ID[endDate.getMonth()]} ${endDate.getFullYear()}
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-select-all">Pilih Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-clear-all">Kosongkan</button>
                </div>
            </div>
            <div class="d-flex gap-3 flex-wrap mb-3 w-100" style="font-size:12px;">
                <span class="d-flex align-items-center gap-1">
                    <span style="width:12px;height:12px;border-radius:3px;background:#cfe2ff;border:1.5px solid #0d6efd;display:inline-block;"></span> Hari kerja aktif
                </span>
                <span class="d-flex align-items-center gap-1">
                    <span style="width:12px;height:12px;border-radius:3px;background:#f8d7da;border:1.5px solid #f5c2c7;display:inline-block;"></span> Minggu / tidak aktif
                </span>
                <span class="d-flex align-items-center gap-1">
                    <span style="width:12px;height:12px;border-radius:3px;background:#fff;border:1.5px solid #dee2e6;display:inline-block;"></span> Tidak aktif
                </span>
            </div>
        `);

        let mc = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
        let lastMonth = new Date(endDate.getFullYear(), endDate.getMonth() + 1, 0);

        while (mc <= lastMonth) {
            let monthStart = new Date(mc.getFullYear(), mc.getMonth(), 1);
            let monthEnd   = new Date(mc.getFullYear(), mc.getMonth() + 1, 0);

            let monthEl = $(`<div class="w-100 mb-3">
                <div class="fw-semibold text-muted mb-2" style="font-size:13px;">
                    ${MONTHS_ID[mc.getMonth()]} ${mc.getFullYear()}
                </div>
                <div class="cal-grid"></div>
            </div>`);

            let grid = monthEl.find('.cal-grid');

            DAY_LABELS.forEach((d, i) => {
                grid.append(`<div class="cal-day-label${i===0?' text-danger':''}">${d}</div>`);
            });

            let firstDow = monthStart.getDay();
            for (let i = 0; i < firstDow; i++) {
                grid.append('<div class="cal-cell-empty"></div>');
            }

            for (let d = 1; d <= monthEnd.getDate(); d++) {
                let date = new Date(mc.getFullYear(), mc.getMonth(), d);
                let key  = toKey(date);
                let inRange  = date >= startDate && date <= endDate;
                let isSunday = date.getDay() === 0;

                let cellClass = 'cal-cell-day';
                if (!inRange)      cellClass += ' cal-out';
                else if (isSunday) cellClass += ' cal-sunday';
                else if (state[key]) cellClass += ' cal-checked';

                let checkIcon = (!isSunday && inRange && state[key])
                    ? '<i class="fas fa-check" style="font-size:10px;color:#0d6efd;"></i>' : '';
                let hiddenInput = (!isSunday && inRange && state[key])
                    ? `<input type="checkbox" name="attendance_dates[]" value="${key}" checked style="display:none;">` : '';

                let cell = $(`
                    <div class="${cellClass}" data-key="${key}" data-inrange="${inRange}" data-sunday="${isSunday}">
                        <span class="cal-num">${d}</span>
                        <span class="cal-mo">${MONTHS_ID[mc.getMonth()]}</span>
                        ${checkIcon}
                        ${hiddenInput}
                    </div>
                `);

                if (inRange && !isSunday) {
                    cell.css('cursor', 'pointer');
                    cell.on('click', function() {
                        state[key] = !state[key];
                        renderAll();
                        updateSummary();
                    });
                }

                grid.append(cell);
            }

            wrapper.append(monthEl);
            mc.setMonth(mc.getMonth() + 1);
        }

        updateSummary();

        $('#btn-select-all').on('click', function() {
            Object.keys(state).forEach(k => { if(new Date(k).getDay()!==0) state[k]=true; });
            renderAll();
        });
        $('#btn-clear-all').on('click', function() {
            Object.keys(state).forEach(k => { state[k]=false; });
            renderAll();
        });
    }

    function updateSummary() {
        let active=0, off=0, total=0;
        let c = new Date(startDate);
        while (c <= endDate) {
            total++;
            let k = toKey(c);
            if (c.getDay()===0 || !state[k]) off++; else active++;
            c.setDate(c.getDate() + 1);
        }
        $('#cal-summary').remove();
        wrapper.append(`
            <div id="cal-summary" class="d-flex gap-2 flex-wrap mt-3 w-100">
                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2">
                    <i class="fas fa-calendar-check me-1"></i> ${active} hari kerja
                </span>
                <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2">
                    <i class="fas fa-calendar-times me-1"></i> ${off} tidak aktif
                </span>
                <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2">
                    <i class="fas fa-calendar me-1"></i> ${total} total hari
                </span>
            </div>
        `);
    }

    renderAll();
}

$('#wizard4-tab').on('show.bs.tab', function() {
    generateCalendar();
});

// Jalankan saat load agar absensi yang diedit termuat
setTimeout(function() {
    if($('#start_date').val() && $('#end_date').val()){
        generateCalendar();
    }
}, 500);


// ========== KOP SURAT DROPDOWN PREVIEW ==========
function initKopDropdownPreview(selectId, previewId, imgId) {
    $('#' + selectId).on('change', function() {
        let $selected = $(this).find(':selected');
        let imgUrl = $selected.data('img');
        let $preview = $('#' + previewId);
        let $img = $('#' + imgId);
        
        if (imgUrl) {
            $img.attr('src', imgUrl);
            $preview.removeClass('d-none');
        } else {
            $preview.addClass('d-none');
            $img.attr('src', '');
        }
    });
}
initKopDropdownPreview('kopSchoolSelect', 'kop-school-preview', 'kop-school-img');
initKopDropdownPreview('kopContractorSelect', 'kop-contractor-preview', 'kop-contractor-img');


// ========== MASTER WORKER MODAL AJAX ==========
$('#btn-save-master-worker').click(function() {
    let data = {
        name: $('#m_name').val(),
        worker_role_id: $('#m_role').val(),
        _token: '{{ csrf_token() }}'
    };

    $.post("{{ route('workers.store') }}", data)
    .done(function(response) {
        alert('Worker berhasil ditambah ke Master!');
        $('#modalAddWorker').modal('hide');
        location.reload(); 
    })
    .fail(function() {
        $('#modal-error').removeClass('d-none').text('Gagal simpan. Cek kembali data.');
    });
});

</script>
@endsection