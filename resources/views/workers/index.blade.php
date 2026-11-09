@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="users"></i></div>
                        Manajemen Pekerja
                    </h1>
                    <div class="page-header-subtitle">Daftar semua tukang dan tenaga kerja lapangan</div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-n10">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Import Excel Card -->
    <div class="card card-icon mb-4">
        <div class="row no-gutters">
            <div class="col-auto card-icon-aside bg-success">
                <i class="fas fa-file-excel text-white-50"></i>
            </div>
            <div class="col">
                <div class="card-body py-4">
                    <h5 class="card-title text-success mb-2">Import Pekerja dari Excel</h5>
                    <p class="card-text small text-muted mb-3">Upload file Excel daftar pekerja (format: template_worker.xlsx). Kolom C = TUKANG, Kolom D = TENAGA.</p>
                    <div class="row align-items-end gx-3">
                        <div class="col-md-6">
                            <input type="file" id="workerExcelInput" class="form-control" accept=".xlsx,.xls">
                        </div>
                        <div class="col-md-3 mt-2 mt-md-0">
                            <button type="button" class="btn btn-success w-100" id="btn-import-worker" disabled>
                                <i class="fas fa-upload me-2"></i>Import
                            </button>
                        </div>
                    </div>
                    <div id="import-worker-status" class="mt-2 d-none">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div>
                            <span class="small text-muted">Sedang membaca file Excel...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Daftar Nama Pekerja</span>
            <a href="{{ route('workers.create') }}" class="btn btn-primary btn-sm">
                <i class="me-1" data-feather="plus"></i> Tambah Pekerja
            </a>
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workers as $worker)
                    <tr>
                        <td><strong>{{ $worker->name }}</strong></td>
                        <td><span class="badge bg-blue-soft text-blue">{{ $worker->role->role_name }}</span></td>
                        <td>{{ $worker->phone ?? '-' }}</td>
                        <td class="small">{{ $worker->address ?? '-' }}</td>
                        <td>
                            <!-- Tambahan title untuk Tooltip -->
                            <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="{{ route('workers.edit', $worker->id) }}" title="Edit Pekerja">
                                <i data-feather="edit"></i>
                            </a>
                            <form action="{{ route('workers.destroy', $worker->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data pekerja ini?');">
                                @csrf 
                                @method('DELETE')
                                <!-- Tambahan title untuk Tooltip -->
                                <button type="submit" class="btn btn-datatable btn-icon btn-transparent-dark border-0" title="Hapus Pekerja">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Input Gaji Role Baru -->
<div class="modal fade" id="modalNewRoles" tabindex="-1" aria-labelledby="modalNewRolesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalNewRolesLabel"><i class="fas fa-exclamation-triangle me-2"></i>Role Baru Ditemukan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Role berikut belum terdaftar di database. Tentukan <strong>Upah Standar (Rp)</strong> untuk setiap role:</p>
                <div id="pending-roles-form"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-save-new-roles">
                    <i class="fas fa-save me-2"></i>Simpan & Import
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#datatablesSimple').DataTable({
            language: {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(disaring dari total _MAX_ data)",
                "zeroRecords": "Data tidak ditemukan",
                "emptyTable": "Tidak ada data yang tersedia di tabel",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Lanjut",
                    "previous": "Kembali"
                }
            }
        });

        // ========== IMPORT WORKER EXCEL ==========
        let pendingWorkersData = [];

        $('#workerExcelInput').on('change', function() {
            $('#btn-import-worker').prop('disabled', !this.files.length);
        });

        function showAlert(message, type) {
            $('.import-alert').remove();
            let icon = type === 'success' ? 'fa-check-circle' : (type === 'danger' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle');
            let html = `
                <div class="alert alert-${type} alert-dismissible fade show import-alert shadow-lg" role="alert" 
                     style="position:fixed;top:20px;right:20px;z-index:9999;min-width:320px;max-width:450px;animation:slideInRight .4s ease;">
                    <i class="fas ${icon} me-2"></i><strong>${message}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
            $('body').append(html);
            setTimeout(() => { $('.import-alert').fadeOut(500, function() { $(this).remove(); }); }, 8000);
        }

        $('#btn-import-worker').click(function() {
            let fileInput = $('#workerExcelInput')[0];
            if (!fileInput.files.length) return;

            let formData = new FormData();
            formData.append('worker_file', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            let $btn = $(this);
            let origText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Membaca...');
            $('#import-worker-status').removeClass('d-none');

            $.ajax({
                url: '{{ route("workers.import") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        showAlert(res.message, 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else if (res.needs_roles) {
                        // Show modal for new roles
                        pendingWorkersData = res.workers;
                        let formHtml = '';
                        res.pending_roles.forEach(function(role, i) {
                            formHtml += `
                                <div class="mb-3 p-3 bg-light rounded">
                                    <label class="small fw-bold mb-1">${role}</label>
                                    <input type="hidden" class="role-name" value="${role}">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control role-wage" placeholder="Contoh: 150000" min="0" required>
                                    </div>
                                </div>`;
                        });
                        $('#pending-roles-form').html(formHtml);
                        new bootstrap.Modal(document.getElementById('modalNewRoles')).show();
                    } else {
                        showAlert(res.message || 'Gagal import.', 'warning');
                    }
                },
                error: function(xhr) {
                    let msg = 'Gagal import file.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showAlert(msg, 'danger');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(origText);
                    $('#import-worker-status').addClass('d-none');
                    $('#workerExcelInput').val('');
                    $('#btn-import-worker').prop('disabled', true);
                }
            });
        });

        // Save new roles + import workers
        $('#btn-save-new-roles').click(function() {
            let roles = [];
            let valid = true;
            $('#pending-roles-form .mb-3').each(function() {
                let name = $(this).find('.role-name').val();
                let wage = $(this).find('.role-wage').val();
                if (!wage || wage <= 0) {
                    valid = false;
                    $(this).find('.role-wage').addClass('is-invalid');
                } else {
                    $(this).find('.role-wage').removeClass('is-invalid');
                    roles.push({ role_name: name, default_wage: parseFloat(wage) });
                }
            });

            if (!valid) {
                showAlert('Lengkapi semua upah standar!', 'warning');
                return;
            }

            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');

            $.ajax({
                url: '{{ route("workers.store-role-ajax") }}',
                type: 'POST',
                data: JSON.stringify({ roles: roles, workers: pendingWorkersData, _token: '{{ csrf_token() }}' }),
                contentType: 'application/json',
                success: function(res) {
                    if (res.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalNewRoles')).hide();
                        showAlert(res.message, 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showAlert(res.message || 'Gagal menyimpan.', 'danger');
                    }
                },
                error: function(xhr) {
                    let msg = 'Gagal menyimpan role.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showAlert(msg, 'danger');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Simpan & Import');
                }
            });
        });
    });
</script>
<style>
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
</style>
@endsection