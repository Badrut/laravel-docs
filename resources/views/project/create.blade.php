@extends('layouts.app')

@section('title', 'Buat SPJ Baru')

@section('css')
    <!-- CDN Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Sedikit penyesuaian agar Select2 setinggi form-control Bootstrap 5 */
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
    </style>
@endsection

@section('content')
<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-xl px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="file-plus"></i></div>
                            Buat SPJ Baru
                        </h1>
                        <div class="page-header-subtitle">Isi formulir secara bertahap untuk membuat dokumen SPJ baru</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main page content-->
    <div class="container-xl px-4 mt-n10">
        
        <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" id="spjForm">
            @csrf

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
                    <!-- Wizard navigation-->
                    <div class="nav nav-pills nav-justified flex-column flex-xl-row nav-wizard" id="cardTab" role="tablist">
                        <!-- Step 1 -->
                        <a class="nav-item nav-link active" id="wizard1-tab" href="#wizard1" data-bs-toggle="tab" role="tab" aria-controls="wizard1" aria-selected="true">
                            <div class="wizard-step-icon">1</div>
                            <div class="wizard-step-text">
                                <div class="wizard-step-text-name">Informasi Kegiatan</div>
                                <div class="wizard-step-text-details">Data utama & pejabat</div>
                            </div>
                        </a>
                        <!-- Step 2 -->
                        <a class="nav-item nav-link" id="wizard2-tab" href="#wizard2" data-bs-toggle="tab" role="tab" aria-controls="wizard2" aria-selected="false">
                            <div class="wizard-step-icon">2</div>
                            <div class="wizard-step-text">
                                <div class="wizard-step-text-name">RAB & Pajak</div>
                                <div class="wizard-step-text-details">Pengaturan anggaran & item</div>
                            </div>
                        </a>
                        <!-- Step 3 -->
                        <a class="nav-item nav-link" id="wizard3-tab" href="#wizard3" data-bs-toggle="tab" role="tab" aria-controls="wizard3" aria-selected="false">
                            <div class="wizard-step-icon">3</div>
                            <div class="wizard-step-text">
                                <div class="wizard-step-text-name">Daftar Pekerja</div>
                                <div class="wizard-step-text-details">Daftar hadir tukang/tenaga</div>
                            </div>
                        </a>
                        <a class="nav-item nav-link" id="wizard4-tab" href="#wizard4" data-bs-toggle="tab" role="tab" aria-controls="wizard4" aria-selected="false">
                            <div class="wizard-step-icon">4</div>
                            <div class="wizard-step-text">
                                <div class="wizard-step-text-name">Jadwal Absensi</div>
                                <div class="wizard-step-text-details">Checklist hari kerja</div>
                            </div>
                        </a>
                        <a class="nav-item nav-link" id="wizard5-tab" href="#wizard5" data-bs-toggle="tab" role="tab" aria-controls="wizard5" aria-selected="false">
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
                        
                        <!-- TAB 1: INFORMASI -->
                        <div class="tab-pane py-5 py-xl-10 fade show active" id="wizard1" role="tabpanel" aria-labelledby="wizard1-tab">
                            <div class="row justify-content-center">
                                <div class="col-xxl-10 col-xl-12">
                                    <h3 class="text-primary mb-4"> Informasi Kegiatan & Administrasi</h3>
                                    <div class="row gx-5">
                                        
                                        <!-- Kolom 1 -->
                                        <div class="col-lg-4">
                                            <h5 class="mb-3 text-muted"><i class="me-2" data-feather="info"></i>Data Utama</h5>
                                            <div class="mb-3">
                                                <label class="small mb-1">Nama Kegiatan</label>
                                                <input type="text" name="project_name" class="form-control" required placeholder="Contoh: Pemeliharaan Ringan Gedung A">
                                            </div>
                                            <div class="row gx-3">
                                                <div class="col-md-6 mb-3">
                                                    <label class="small mb-1">Tanggal Mulai</label>
                                                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="small mb-1">Tanggal Selesai</label>
                                                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="small mb-1">Kontraktor / Toko</label>
                                                <input type="text" name="contractor_name" class="form-control" placeholder="Contoh: TB. Sukses Abadi">
                                                <input type="text" name="contractor_address" class="form-control mt-1" placeholder="Alamat Kontraktor">
                                            </div>
                                            <div class="mb-3">
                                                <label class="small mb-1">Penanggung Jawab Kontraktor</label>
                                                <input type="text" name="person_in_charge" class="form-control" placeholder="Nama pemilik/pimpinan toko">
                                            </div>
                                            <div class="mb-3">
                                                <label class="small mb-1">Tanggal Dokumen RAB</label>
                                                <input type="date" name="budget_date" class="form-control">
                                            </div>
                                        </div>

                                        <!-- Kolom 2 -->
                                        <div class="col-lg-4">
                                            <h5 class="mb-3 text-muted"><i class="me-2" data-feather="users"></i>Data Pejabat (Snapshot)</h5>
                                            <div class="mb-3">
                                                <label class="small mb-1">Kepala Sekolah</label>
                                                <input type="text" name="principal_name" class="form-control" value="Sukidi, S.Pd., M.Pd.">
                                                <input type="text" name="principal_nip" class="form-control mt-1 small" placeholder="NIP Kepala Sekolah" value="19670101XXXXXXXXXX">
                                            </div>
                                            <div class="mb-3">
                                                <label class="small mb-1">Waka Sarpras</label>
                                                <input type="text" name="facilities_head_name" class="form-control" value="Sukasno, S.Pd.">
                                                <input type="text" name="facilities_head_nip" class="form-control mt-1" placeholder="NIP Waka Sarpras" value="19750101XXXXXXXXXX">
                                            </div>
                                            <div class="mb-3">
                                                <label class="small mb-1">Bendahara</label>
                                                <input type="text" name="treasurer_name" class="form-control" value="Guncoro Singgih Saputro">
                                                <input type="text" name="treasurer_nip" class="form-control mt-1" placeholder="NIP Bendahara" value="19800101XXXXXXXXXX">
                                            </div>
                                            <div class="mb-3">
                                                <label class="small mb-1">Kasubag TU</label>
                                                <input type="text" name="admin_head_name" class="form-control" placeholder="Nama Kasubag TU">
                                                <input type="text" name="admin_head_nip" class="form-control mt-1" placeholder="NIP Kasubag TU">
                                            </div>
                                        </div>

                                        <!-- Kolom 3 -->
                                        <div class="col-lg-4">
                                            <h5 class="mb-3 text-muted"><i class="me-2" data-feather="file-text"></i>Nomor & Tanggal Surat</h5>
                                            <div class="p-3 bg-light rounded mb-3">
                                                <label class="small mb-1 fw-bold">Surat Perintah Kerja (SPK)</label>
                                                <input type="text" name="work_order_number" class="form-control mb-1" placeholder="No. SPK">
                                                <input type="date" name="work_order_date" class="form-control">
                                            </div>
                                            <div class="p-3 bg-light rounded mb-3">
                                                <label class="small mb-1 fw-bold">Kontraktor</label>
                                                <input type="text" name="contractor_number" class="form-control mb-1" placeholder="No. Kontrak">
                                                <input type="date" name="contractor_date" class="form-control">
                                            </div>
                                            <div class="p-3 bg-light rounded mb-3">
                                                <label class="small mb-1 fw-bold">BA Penyerahan</label>
                                                <input type="text" name="handover_report_number" class="form-control mb-1" placeholder="No. BAST">
                                                <input type="date" name="handover_report_date" class="form-control">
                                            </div>
                                            <div class="p-3 bg-light rounded mb-3">
                                                <label class="small mb-1 fw-bold">Laporan Pembayaran</label>
                                                <input type="text" name="payment_report_number" class="form-control mb-1" placeholder="No. Laporan Pembayaran">
                                                <input type="date" name="payment_report_date" class="form-control">
                                            </div>
                                            <div class="p-3 bg-light rounded mb-3">
                                                <label class="small mb-1 fw-bold">Kwitansi & Pembayaran</label>
                                                <input type="text" name="receipt_number" class="form-control mb-1" placeholder="No. Kwitansi">
                                                <input type="date" name="receipt_date" class="form-control">
                                                <input type="text" name="receipt_receiver" class="form-control mt-1" placeholder="Penerima Kwitansi">
                                            </div>
                                            <div class="p-3 bg-light rounded mb-3">
                                                <label class="small mb-1 fw-bold">Surat Undangan</label>
                                                <input type="text" name="invitation_number" class="form-control mb-1" placeholder="No. Undangan">
                                                <input type="date" name="invitation_date" class="form-control">
                                            </div>
                                        </div>

                                    </div>
                                    <hr class="my-4" />
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary btn-lg nav-btn" data-target="#wizard2-tab" type="button">Lanjut ke Rincian RAB <i class="fas fa-arrow-right ms-2"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: RAB & PAJAK -->
                        <div class="tab-pane py-5 py-xl-10 fade" id="wizard2" role="tabpanel" aria-labelledby="wizard2-tab">
                            <div class="row justify-content-center">
                                <div class="col-xxl-10 col-xl-12">
                                    <h3 class="text-primary mb-4">Rincian Anggaran Biaya (RAB) & Pajak</h3>
                                    
                                    <!-- SEGMEN INPUT PAJAK -->
                                    <div class="card mb-4 border-start-lg border-start-primary shadow-none bg-light">
                                        <div class="card-body">
                                            <h6 class="text-muted fw-bold mb-3"><i class="fas fa-percent me-2 text-primary"></i>Potongan Pajak (Opsional)</h6>
                                            <p class="small text-muted mb-3">Isi dengan angka persen saja (Contoh: <b>11</b> untuk PPN 11%, <b>1.5</b> untuk PPh 22). Kosongkan jika tidak ada pemotongan pajak.</p>
                                            <div class="row gx-3">
                                                <div class="col-md-2 col-sm-4 mb-3 mb-md-0">
                                                    <label class="small mb-1">PPN (%)</label>
                                                    <input type="number" step="0.01" name="tax_ppn" class="form-control" placeholder="11">
                                                </div>
                                                <div class="col-md-2 col-sm-4 mb-3 mb-md-0">
                                                    <label class="small mb-1">PPh 22 (%)</label>
                                                    <input type="number" step="0.01" name="tax_pph22" class="form-control" placeholder="1.5">
                                                </div>
                                                <div class="col-md-2 col-sm-4 mb-3 mb-md-0">
                                                    <label class="small mb-1">PPh 21 (%)</label>
                                                    <input type="number" step="0.01" name="tax_pph21" class="form-control" placeholder="5">
                                                </div>
                                                <div class="col-md-2 col-sm-4 mb-3 mb-md-0">
                                                    <label class="small mb-1">PPh 23 (%)</label>
                                                    <input type="number" step="0.01" name="tax_pph23" class="form-control" placeholder="2">
                                                </div>
                                                <div class="col-md-3 col-sm-4">
                                                    <label class="small mb-1">PPh Final (%)</label>
                                                    <input type="number" step="0.01" name="tax_pph_final" class="form-control" placeholder="4">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- SEGMEN AKHIR PAJAK -->

                                    <h6 class="text-muted fw-bold mb-3"><i class="fas fa-list me-2"></i>Daftar Item RAB</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover">
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
                                                <tr>
                                                    <td>
                                                        <select name="items[0][type]" class="form-control select2" style="width: 100%;">
                                                            <option value="material">Alat & Bahan</option>
                                                            <option value="labor">Upah Tenaga</option>
                                                        </select>
                                                    </td>
                                                    <td><input type="text" name="items[0][item_name]" class="form-control" placeholder="Contoh: Cat Eksterior" required></td>
                                                    <td><input type="number" name="items[0][quantity]" class="form-control" required></td>
                                                    <td><input type="text" name="items[0][unit]" class="form-control" placeholder="galon" required></td>
                                                    <td><input type="number" name="items[0][unit_price]" class="form-control" required></td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-danger btn-icon hapus-baris" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mb-4 mt-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-tambah-item">
                                            <i class="fas fa-plus me-2"></i> Tambah Item RAB
                                        </button>
                                    </div>
                                    <hr class="my-4" />
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-light nav-btn" data-target="#wizard1-tab" type="button"><i class="fas fa-arrow-left me-2"></i> Kembali</button>
                                        <button class="btn btn-primary nav-btn" data-target="#wizard3-tab" type="button">Selanjutnya <i class="fas fa-arrow-right ms-2"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: DAFTAR PEKERJA -->
                        <div class="tab-pane py-5 fade" id="wizard3" role="tabpanel" aria-labelledby="wizard3-tab">
                            <div class="row justify-content-center">
                                <div class="col-xxl-8 col-xl-10">
                                    <h3 class="text-primary mb-4">Pilih Pekerja yang Bertugas</h3>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Nama Pekerja (Ambil dari Master)</th>
                                                    <th width="10%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="worker-body">
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
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-tambah-worker">
                                            <i class="fas fa-plus me-2"></i>Tambah Baris Pekerja
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddWorker">
                                            <i class="fas fa-user-plus me-2"></i>Master Worker Baru
                                        </button>
                                    </div>
                                    <hr class="my-4" />
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-light nav-btn" data-target="#wizard2-tab" type="button">Kembali</button>
                                        <button class="btn btn-primary nav-btn" id="btn-to-step4" data-target="#wizard4-tab" type="button">Atur Jadwal <i class="fas fa-arrow-right ms-2"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TAB 4: JADWAL ABSENSI -->
                        <div class="tab-pane py-5 fade" id="wizard4" role="tabpanel" aria-labelledby="wizard4-tab">
                            <div class="row justify-content-center">
                                <div class="col-xxl-8 col-xl-10">
                                    <h3 class="text-primary mb-4">Atur Hari Kerja (Absensi)</h3>
                                    <p class="text-muted text-center small">Centang tanggal aktif kerja (Hari Minggu otomatis tidak dicentang).</p>
                                    <div class="card border-primary bg-light">
                                        <div class="card-body">
                                            <div id="calendar-wrapper" class="row g-2 text-center justify-content-center">
                                                <div class="col-12 text-muted py-5">
                                                    <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                                    <p>Silakan isi Tanggal di Langkah 1 dahulu.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-4" />
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-light nav-btn" data-target="#wizard3-tab" type="button">Kembali</button>
                                        <button class="btn btn-primary nav-btn" data-target="#wizard5-tab" type="button">Dokumentasi <i class="fas fa-arrow-right ms-2"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 5: DOKUMENTASI -->
                        <div class="tab-pane py-5 py-xl-10 fade" id="wizard5" role="tabpanel" aria-labelledby="wizard5-tab">
                            <div class="row justify-content-center">
                                <div class="col-xxl-8 col-xl-10">
                                    <h3 class="text-primary mb-4 text-center">Upload Dokumentasi & Kop Surat</h3>

                                    {{-- Foto Kegiatan --}}
                                    <div class="card bg-light shadow-none border-dashed p-4 mb-4 text-center">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                        <h5>Foto Kegiatan</h5>
                                        <p class="text-muted small mb-0">Upload foto dokumentasi pelaksanaan kegiatan (maks. 5 MB/foto, JPEG/PNG).</p>
                                        <input type="file" name="images[]" id="imageInput" class="form-control mt-3" multiple accept="image/*">
                                        <div id="preview-container" class="row g-3 mt-3 text-start"></div>
                                    </div>

                                    {{-- Kop Surat --}}
                                    <div class="row g-3 mb-4">
                                        {{-- Kop Sekolah --}}
                                        <div class="col-md-6">
                                            <div class="card h-100 shadow-none border">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-school fa-2x text-info mb-2"></i>
                                                    <h6 class="fw-bold">Kop Surat Sekolah</h6>
                                                    <p class="text-muted small">Gambar kop yang akan menggantikan kop sekolah di template Word. Kosongkan jika tidak ingin mengganti.</p>
                                                    <input type="file" name="kop_school" id="kopSchoolInput" class="form-control mt-2" accept="image/jpeg,image/png,image/jpg">
                                                    <div id="kop-school-preview" class="mt-2"></div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Kop Kontraktor --}}
                                        <div class="col-md-6">
                                            <div class="card h-100 shadow-none border">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-building fa-2x text-warning mb-2"></i>
                                                    <h6 class="fw-bold">Kop Surat Kontraktor</h6>
                                                    <p class="text-muted small">Gambar kop yang akan menggantikan kop kontraktor di template Word. Kosongkan jika tidak ingin mengganti.</p>
                                                    <input type="file" name="kop_contractor" id="kopContractorInput" class="form-control mt-2" accept="image/jpeg,image/png,image/jpg">
                                                    <div id="kop-contractor-preview" class="mt-2"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4" />
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-light nav-btn" data-target="#wizard4-tab" type="button"><i class="fas fa-arrow-left me-2"></i> Kembali</button>
                                        <button class="btn btn-success" type="submit" id="btn-submit"><i class="fas fa-save me-2"></i> Simpan & Export</button>
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

<!-- Modal Add Worker -->
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

        $('.nav-btn').click(function() {
            var targetTab = $(this).data('target');
            var tab = new bootstrap.Tab(document.querySelector(targetTab));
            tab.show();
            if (typeof feather !== 'undefined') feather.replace(); 
        });

        let itemIdx = 1;
        let workerIdx = 1;

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
    });

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
        if(selectedFiles.files.length === 0){
             $('#imageInput').val(''); 
        }
    });
});

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
    while (cur <= endDate) {
        let key = toKey(cur);
        state[key] = cur.getDay() !== 0;
        cur.setDate(cur.getDate() + 1);
    }

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

// Preview Kop Surat
function initKopPreview(inputId, previewId) {
    $('#' + inputId).on('change', function(e) {
        let file = e.target.files[0];
        let $preview = $('#' + previewId);
        $preview.empty();
        if (!file) return;

        let reader = new FileReader();
        reader.onload = function(ev) {
            $preview.html(`
                <div class="position-relative d-inline-block mt-1">
                    <img src="${ev.target.result}" class="img-thumbnail rounded" style="max-height:90px; max-width:100%; object-fit:contain;" alt="Preview Kop">
                    <button type="button" class="btn-remove-image" title="Hapus" onclick="$('#${inputId}').val(''); $('#${previewId}').empty();">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            `);
        };
        reader.readAsDataURL(file);
    });
}
initKopPreview('kopSchoolInput', 'kop-school-preview');
initKopPreview('kopContractorInput', 'kop-contractor-preview');

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