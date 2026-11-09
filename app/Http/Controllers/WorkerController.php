<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\WorkerRole;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WorkerController extends Controller
{
    public function index()
    {
        $workers = Worker::with('role')->latest()->get();
        $roles = WorkerRole::all();
        return view('workers.index', compact('workers', 'roles'));
    }

    public function create()
    {
        $roles = WorkerRole::all(); // Untuk dropdown pilihan role
        return view('workers.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'worker_role_id' => 'required|exists:worker_roles,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Worker::create($request->all());
        return redirect()->route('workers.index')->with('success', 'Pekerja berhasil didaftarkan!');
    }

    public function edit($id)
    {
        $worker = Worker::findOrFail($id);
        $roles = WorkerRole::all();
        return view('workers.edit', compact('worker', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $worker = Worker::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'worker_role_id' => 'required|exists:worker_roles,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $worker->update($request->all());
        return redirect()->route('workers.index')->with('success', 'Data pekerja berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $worker = Worker::findOrFail($id);
        $worker->delete();
        return redirect()->route('workers.index')->with('success', 'Pekerja berhasil dihapus!');
    }

    /**
     * Import workers from Excel (format: template_worker.xlsx)
     * Sheet1 (2): A=No, B=Nama, C=TUKANG, D=TENAGA, E=ALAMAT, F=NO.HP
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'worker_file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $file = $request->file('worker_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            
            // Try to use sheet "Sheet1 (2)" first, else use first sheet
            $sheetNames = $spreadsheet->getSheetNames();
            $sheet = null;
            foreach ($sheetNames as $name) {
                if (stripos($name, 'Sheet1 (2)') !== false || stripos($name, 'Sheet1(2)') !== false) {
                    $sheet = $spreadsheet->getSheetByName($name);
                    break;
                }
            }
            if (!$sheet) {
                $sheet = $spreadsheet->getActiveSheet();
            }

            $workers = [];
            $pendingRoles = [];
            $maxRow = $sheet->getHighestRow();

            // Start from row 4 (row 3 is header: No, Nama, TUKANG, TENAGA, ALAMAT, NO.HP)
            for ($row = 4; $row <= $maxRow; $row++) {
                $no   = $sheet->getCell("A{$row}")->getValue();
                $name = trim((string) $sheet->getCell("B{$row}")->getValue());
                $colC = trim((string) $sheet->getCell("C{$row}")->getValue()); // TUKANG
                $colD = trim((string) $sheet->getCell("D{$row}")->getValue()); // TENAGA
                $addr = trim((string) $sheet->getCell("E{$row}")->getValue());
                $phone = trim((string) $sheet->getCell("F{$row}")->getValue());

                if (empty($name) || !is_numeric($no)) continue;

                // Determine role name from column C or D
                $roleName = '';
                if (!empty($colC)) {
                    $roleName = ucfirst(strtolower($colC)); // e.g. "Tukang"
                } elseif (!empty($colD)) {
                    $roleName = ucfirst(strtolower($colD)); // e.g. "Tenaga"
                }

                if (empty($roleName)) continue;

                // Check if this role exists
                $existingRole = WorkerRole::whereRaw('LOWER(role_name) = ?', [strtolower($roleName)])->first();

                if (!$existingRole && !in_array(strtolower($roleName), array_map('strtolower', $pendingRoles))) {
                    $pendingRoles[] = $roleName;
                }

                $workers[] = [
                    'name'      => $name,
                    'role_name' => $roleName,
                    'address'   => $addr,
                    'phone'     => $phone,
                ];
            }

            if (empty($workers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data pekerja ditemukan di file Excel.',
                ], 422);
            }

            // If there are pending roles, return them so the frontend can show modal
            if (!empty($pendingRoles)) {
                return response()->json([
                    'success'       => false,
                    'needs_roles'   => true,
                    'pending_roles' => $pendingRoles,
                    'workers'       => $workers,
                    'message'       => 'Ada ' . count($pendingRoles) . ' role baru yang belum terdaftar. Silakan tentukan upah standarnya.',
                ]);
            }

            // All roles exist, save workers
            $saved = $this->saveImportedWorkers($workers);

            return response()->json([
                'success' => true,
                'message' => "Berhasil import {$saved} pekerja dari Excel.",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new WorkerRole via AJAX (from import modal)
     */
    public function storeRoleAjax(Request $request)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*.role_name' => 'required|string|max:255',
            'roles.*.default_wage' => 'required|numeric|min:0',
            'workers' => 'nullable|array',
        ]);

        // Create all pending roles
        foreach ($request->roles as $roleData) {
            $existing = WorkerRole::whereRaw('LOWER(role_name) = ?', [strtolower($roleData['role_name'])])->first();
            if (!$existing) {
                WorkerRole::create([
                    'role_name'    => $roleData['role_name'],
                    'default_wage' => $roleData['default_wage'],
                ]);
            }
        }

        // Now save the workers if provided
        if ($request->workers) {
            $saved = $this->saveImportedWorkers($request->workers);
            return response()->json([
                'success' => true,
                'message' => "Role berhasil dibuat. {$saved} pekerja berhasil diimport.",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil dibuat.',
        ]);
    }

    /**
     * Helper: save imported workers array to database
     */
    private function saveImportedWorkers(array $workers): int
    {
        $count = 0;
        foreach ($workers as $w) {
            $role = WorkerRole::whereRaw('LOWER(role_name) = ?', [strtolower($w['role_name'])])->first();
            if (!$role) continue;

            // Skip duplicate (same name + same role)
            $exists = Worker::where('name', $w['name'])
                ->where('worker_role_id', $role->id)
                ->exists();
            if ($exists) continue;

            Worker::create([
                'name'           => $w['name'],
                'worker_role_id' => $role->id,
                'address'        => $w['address'] ?? null,
                'phone'          => $w['phone'] ?? null,
            ]);
            $count++;
        }
        return $count;
    }
}