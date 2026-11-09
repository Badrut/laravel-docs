<?php

namespace App\Http\Controllers;
use App\Models\Project;
use App\Models\Worker;
use App\Models\WorkerRole;
use App\Models\Contractor;
use App\Models\KopSurat;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\CarbonPeriod;
class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['items', 'workers', 'images', 'contractor'])->latest()->get();
        
        return view('dashboard', compact('projects'));
    }

    public function create()
    {   
        $projects = Project::all();
        $workers = Worker::all();
        $roles = WorkerRole::all();
        return view('project.createv2', compact('projects', 'workers', 'roles'));
    }
    
    public function createv2(){
        $projects = Project::all();
        $workers = Worker::all();
        $roles = WorkerRole::all();
        $contractors = Contractor::all();
        $kopSekolah = KopSurat::sekolah()->get();
        $kopKontraktor = KopSurat::kontraktor()->get();
        return view('project.createv2', compact('projects', 'workers', 'roles', 'contractors', 'kopSekolah', 'kopKontraktor'));
    }

    public function edit(Project $project) {
        $projects = Project::all();
        $workers = Worker::all();
        $roles = WorkerRole::all();
        $contractors = Contractor::all();
        $kopSekolah = KopSurat::sekolah()->get();
        $kopKontraktor = KopSurat::kontraktor()->get();
        return view('project.edit', compact('project', 'projects', 'workers', 'roles', 'contractors', 'kopSekolah', 'kopKontraktor'));
    }

        public function editv2(Project $project) {
        $projects = Project::all();
        $workers = Worker::all();
        $roles = WorkerRole::all();
        $contractors = Contractor::all();
        $kopSekolah = KopSurat::sekolah()->get();
        $kopKontraktor = KopSurat::kontraktor()->get();
        return view('project.editv2', compact('project', 'projects', 'workers', 'roles', 'contractors', 'kopSekolah', 'kopKontraktor'));
    }



    /**
     * Import RAB items from Excel file (format: template_excelv2.xlsx)
     * Reads Section A (ALAT DAN BAHAN → material) and Section B (UPAH TUKANG DAN TENAGA → labor)
     * Columns: A=No, B=Nama, C=Volume, D=Satuan, E=Harga Satuan, F=Jumlah
     */
    public function importRab(Request $request)
{
    // Tambahkan 'csv' pada mimes jika user mengunggah file dalam format CSV
    $request->validate([
        'rab_file' => 'required|file|mimes:xlsx,xls,csv',
    ]);

    try {
        $file = $request->file('rab_file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $items = [];

        // Baris 1 adalah Header (NO, NAMA BARANG, dst), jadi data dimulai dari baris 2
        $startRow = 2;
        $maxRow = $sheet->getHighestRow();
        $currentType = 'material'; // Default awal

        for ($row = $startRow; $row <= $maxRow; $row++) {
            // Gunakan getCalculatedValue() untuk mengantisipasi jika cell adalah hasil rumus/formula
            $colA = trim((string) $sheet->getCell("A{$row}")->getCalculatedValue());
            $colB = trim((string) $sheet->getCell("B{$row}")->getCalculatedValue());
            $colC = $sheet->getCell("C{$row}")->getCalculatedValue();
            $colD = trim((string) $sheet->getCell("D{$row}")->getCalculatedValue());
            $colE = $sheet->getCell("E{$row}")->getCalculatedValue();

            // 1. Deteksi Header Bagian: "A" -> Alat & Bahan, "B" -> Upah Tukang
            if (preg_match('/^[A-Z]$/i', $colA) && !is_numeric($colA)) {
                if (strtoupper($colA) === 'A') {
                    $currentType = 'material';
                } elseif (strtoupper($colA) === 'B') {
                    $currentType = 'labor';
                }
                continue;
            }

            // 2. Lewati baris rekapitulasi "Jumlah Total"
            if (stripos($colB, 'Jumlah Total') !== false) {
                continue;
            }

            // 3. Lewati baris kosong atau baris yang Kolom A-nya bukan angka (bukan nomor urut)
            if (!is_numeric($colA) || empty($colB)) {
                continue;
            }

            // 4. Ambil data: Kolom C (Volume) dan Kolom E (Harga Satuan) harus berupa angka
            if (is_numeric($colC) && is_numeric($colE)) {
                $items[] = [
                    'type'       => $currentType,
                    'item_name'  => $colB,
                    'quantity'   => (float) $colC,
                    'unit'       => $colD,
                    'unit_price' => (float) $colE,
                ];
            }
        }

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ditemukan data item RAB di file Excel. Pastikan format sesuai template.',
            ], 422);
        }

        $materialCount = count(array_filter($items, fn($i) => $i['type'] === 'material'));
        $laborCount = count(array_filter($items, fn($i) => $i['type'] === 'labor'));

        return response()->json([
            'success' => true,
            'message' => "Berhasil import {$materialCount} item Alat & Bahan dan {$laborCount} item Upah Tukang dari Excel.",
            'items'   => $items,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal membaca file Excel: ' . $e->getMessage(),
        ], 500);
    }
}

    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'project_name'              => 'required|string|max:255',
            'start_date'                => 'required|date',
            'end_date'                  => 'required|date|after_or_equal:start_date',
            'contractor_id'             => 'nullable|exists:contractors,id',
            'contractor_number'         => 'nullable|string|max:255',
            'contractor_date'           => 'nullable|date',
            'budget_date'               => 'nullable|date',
            'offer_date'                => 'nullable|date',
            'principal_name'            => 'nullable|string|max:255',
            'principal_nip'             => 'nullable|string|max:50',
            'facilities_head_name'      => 'nullable|string|max:255',
            'facilities_head_nip'       => 'nullable|string|max:50',
            'treasurer_name'            => 'nullable|string|max:255',
            'treasurer_nip'             => 'nullable|string|max:50',
            'admin_head_name'           => 'nullable|string|max:255',
            'admin_head_nip'            => 'nullable|string|max:50',
            'work_order_number'         => 'nullable|string|max:100',
            'work_order_date'           => 'nullable|date',
            'handover_report_number'    => 'nullable|string|max:100',
            'handover_report_date'      => 'nullable|date',
            'payment_report_number'     => 'nullable|string|max:255',
            'payment_report_date'       => 'nullable|date',
            'receipt_receiver'          => 'nullable|string|max:255',
            'invitation_number'         => 'nullable|string|max:100',
            'invitation_date'           => 'nullable|date',
            'tax_ppn'                   => 'nullable|numeric|min:0',
            'tax_pph21'                 => 'nullable|numeric|min:0',
            'tax_pph22'                 => 'nullable|numeric|min:0',
            'tax_pph23'                 => 'nullable|numeric|min:0',
            'tax_pph_final'             => 'nullable|numeric|min:0',
            'items'                     => 'required|array|min:1',
            'items.*.type'              => 'required|in:material,labor',
            'items.*.item_name'         => 'required|string|max:255',
            'items.*.quantity'          => 'required|numeric|min:0',
            'items.*.unit'              => 'required|string|max:50',
            'items.*.unit_price'        => 'required|numeric|min:0',
            'workers'                   => 'required|array|min:1',
            'workers.*'                 => 'nullable|exists:workers,id',
            'attendance_dates'          => 'nullable|array',
            'attendance_dates.*'        => 'date',
            'images'                    => 'nullable|array',
            'images.*'                  => 'image|mimes:jpeg,png,jpg',
            'kop_school'                => 'nullable|image|mimes:jpeg,png,jpg',
            'kop_contractor'            => 'nullable|image|mimes:jpeg,png,jpg',
            'kop_school_id'             => 'nullable|exists:kop_surats,id',
            'kop_contractor_id'         => 'nullable|exists:kop_surats,id',
        ]);             

        DB::beginTransaction();

        try {
            $attendanceDates = $request->attendance_dates ?? [];
            $targetDays      = count($attendanceDates);
            $startCarbon = \Carbon\Carbon::parse($request->start_date);
            $endCarbon   = \Carbon\Carbon::parse($request->end_date);
            $implementationPeriod = $startCarbon->translatedFormat('d F Y'). ' s/d ' . $endCarbon->translatedFormat('d F Y');
            
            $attendancePeriod = null;
            if (!empty($attendanceDates)) {
                $sorted = $attendanceDates;
                sort($sorted);
                $firstActive = \Carbon\Carbon::parse(reset($sorted))->translatedFormat('d F Y');
                $lastActive  = \Carbon\Carbon::parse(end($sorted))->translatedFormat('d F Y');
                $attendancePeriod = $firstActive . ' s/d ' . $lastActive;
            }

            $project = Project::create([
                'project_name'              => $request->project_name,
                'start_date'                => $request->start_date,
                'end_date'                  => $request->end_date,
                'target_days'               => $targetDays,
                'implementation_period'     => $implementationPeriod,
                'attendance_period'         => $attendancePeriod,
                'contractor_id'             => $request->contractor_id,
                'contractor_number'         => $request->contractor_number,
                'contractor_date'           => $request->contractor_date,
                'principal_name'            => $request->principal_name,
                'principal_nip'             => $request->principal_nip,
                'facilities_head_name'      => $request->facilities_head_name,
                'facilities_head_nip'       => $request->facilities_head_nip,
                'treasurer_name'            => $request->treasurer_name,
                'treasurer_nip'             => $request->treasurer_nip,
                'admin_head_name'           => $request->admin_head_name,
                'admin_head_nip'            => $request->admin_head_nip,
                'budget_date'               => $request->budget_date,
                'offer_date'                => $request->offer_date,
                'work_order_number'         => $request->work_order_number,
                'work_order_date'           => $request->work_order_date,
                'handover_report_number'    => $request->handover_report_number,
                'handover_report_date'      => $request->handover_report_date,
                'payment_report_number'     => $request->payment_report_number,
                'payment_report_date'       => $request->payment_report_date,
                'receipt_number'            => $request->receipt_number,
                'receipt_date'              => $request->receipt_date,
                'receipt_receiver'          => $request->receipt_receiver,
                'invitation_number'         => $request->invitation_number,
                'invitation_date'           => $request->invitation_date,
                'tax_vat'                   => $request->tax_ppn ?? 0,
                'tax_pph21'                 => $request->tax_pph21 ?? 0,
                'tax_pph22'                 => $request->tax_pph22 ?? 0,
                'tax_pph23'                 => $request->tax_pph23 ?? 0,
                'tax_pph_final'             => $request->tax_pph_final ?? 0,
                'kop_school_id'             => $request->kop_school_id,
                'kop_contractor_id'         => $request->kop_contractor_id,
            ]);

            foreach ($request->items as $item) {
                $project->items()->create([
                    'type'       => $item['type'],
                    'item_name'  => $item['item_name'],
                    'quantity'   => $item['quantity'],
                    'unit'       => $item['unit'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            $workerIds = array_filter($request->workers);
            if (!empty($workerIds)) {
                $project->workers()->attach($workerIds);
            }

            foreach ($attendanceDates as $date) {
                $project->schedules()->create([
                    'working_date' => $date,
                ]);
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    if (!$image->isValid()) {
                        continue; // skip file rusak/gagal upload
                    }
                    $path = $image->store('dokumentasi_spj', 'public');
                    if ($path === false) {
                        continue; // skip jika store() gagal (disk penuh, permission, dll)
                    }
                    $project->images()->create([
                        'file_path' => $path,
                    ]);
                }
            }

            // Handle kop_school file upload (legacy) - jika pakai dropdown, skip ini
            if ($request->hasFile('kop_school')) {
                $kop = $request->file('kop_school');
                if ($kop->isValid()) {
                    $kopSchoolPath = $kop->store('kop', 'public');
                    if ($kopSchoolPath !== false) {
                        $project->update(['kop_school' => $kopSchoolPath]);
                    }
                }
            } elseif ($request->kop_school_id) {
                // Copy path dari kop_surats ke kop_school untuk backward compatibility
                $kopSurat = KopSurat::find($request->kop_school_id);
                if ($kopSurat) {
                    $project->update(['kop_school' => $kopSurat->image_path]);
                }
            }

            if ($request->hasFile('kop_contractor')) {
                $kop = $request->file('kop_contractor');
                if ($kop->isValid()) {
                    $kopContractorPath = $kop->store('kop', 'public');
                    if ($kopContractorPath !== false) {
                        $project->update(['kop_contractor' => $kopContractorPath]);
                    }
                }
            } elseif ($request->kop_contractor_id) {
                $kopSurat = KopSurat::find($request->kop_contractor_id);
                if ($kopSurat) {
                    $project->update(['kop_contractor' => $kopSurat->image_path]);
                }
            }

            DB::commit();

            return redirect()->route('projects.index')
                ->with('success', 'Data SPJ berhasil disimpan!')
                ->with('auto_download_id', $project->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Gagal menyimpan data! Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(){

    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'project_name'              => 'required|string|max:255',
            'start_date'                => 'required|date',
            'end_date'                  => 'required|date|after_or_equal:start_date',
            'contractor_id'             => 'nullable|exists:contractors,id',
            'contractor_number'         => 'nullable|string|max:255',
            'contractor_date'           => 'nullable|date',
            'budget_date'               => 'nullable|date',
            'offer_date'                => 'nullable|date',
            'principal_name'            => 'nullable|string|max:255',
            'principal_nip'             => 'nullable|string|max:50',
            'facilities_head_name'      => 'nullable|string|max:255',
            'facilities_head_nip'       => 'nullable|string|max:50',
            'treasurer_name'            => 'nullable|string|max:255',
            'treasurer_nip'             => 'nullable|string|max:50',
            'admin_head_name'           => 'nullable|string|max:255',
            'admin_head_nip'            => 'nullable|string|max:50',
            'work_order_number'         => 'nullable|string|max:100',
            'work_order_date'           => 'nullable|date',
            'handover_report_number'    => 'nullable|string|max:100',
            'handover_report_date'      => 'nullable|date',
            'payment_report_number'     => 'nullable|string|max:255',
            'payment_report_date'       => 'nullable|date',
            'receipt_receiver'          => 'nullable|string|max:255',
            'invitation_number'         => 'nullable|string|max:100',
            'invitation_date'           => 'nullable|date',
            'tax_ppn'                   => 'nullable|numeric|min:0',
            'tax_pph21'                 => 'nullable|numeric|min:0',
            'tax_pph22'                 => 'nullable|numeric|min:0',
            'tax_pph23'                 => 'nullable|numeric|min:0',
            'tax_pph_final'             => 'nullable|numeric|min:0',
            'items'                     => 'required|array|min:1',
            'items.*.type'              => 'required|in:material,labor',
            'items.*.item_name'         => 'required|string|max:255',
            'items.*.quantity'          => 'required|numeric|min:0',
            'items.*.unit'              => 'required|string|max:50',
            'items.*.unit_price'        => 'required|numeric|min:0',
            'workers'                   => 'required|array|min:1',
            'workers.*'                 => 'nullable|exists:workers,id',
            'attendance_dates'          => 'nullable|array',
            'attendance_dates.*'        => 'date',
            'images'                    => 'nullable|array',
            'images.*'                  => 'image|mimes:jpeg,png,jpg',
            'kop_school'                => 'nullable|image|mimes:jpeg,png,jpg',
            'kop_contractor'            => 'nullable|image|mimes:jpeg,png,jpg',
        ]);

        DB::beginTransaction();

        try {
            $project = Project::findOrFail($id);
            $attendanceDates = $request->attendance_dates ?? [];
            $targetDays      = count($attendanceDates);       
            $startCarbon = \Carbon\Carbon::parse($request->start_date);
            $endCarbon   = \Carbon\Carbon::parse($request->end_date);
            $implementationPeriod = $startCarbon->translatedFormat('d F Y') . ' s/d ' . $endCarbon->translatedFormat('d F Y');
            $attendancePeriod = null;
            if (!empty($attendanceDates)) {
                $sorted = $attendanceDates;
                sort($sorted);
                $firstActive = \Carbon\Carbon::parse(reset($sorted))->translatedFormat('d F Y');
                $lastActive  = \Carbon\Carbon::parse(end($sorted))->translatedFormat('d F Y');
                $attendancePeriod = $firstActive . ' s/d ' . $lastActive;
            }

            $project->update([
                'project_name'              => $request->project_name,
                'start_date'                => $request->start_date,
                'end_date'                  => $request->end_date,
                'target_days'               => $targetDays,
                'implementation_period'     => $implementationPeriod,
                'attendance_period'         => $attendancePeriod,
                'contractor_id'             => $request->contractor_id,
                'contractor_number'         => $request->contractor_number,
                'contractor_date'           => $request->contractor_date,
                'principal_name'            => $request->principal_name,
                'principal_nip'             => $request->principal_nip,
                'facilities_head_name'      => $request->facilities_head_name,
                'facilities_head_nip'       => $request->facilities_head_nip,
                'treasurer_name'            => $request->treasurer_name,
                'treasurer_nip'             => $request->treasurer_nip,
                'admin_head_name'           => $request->admin_head_name,
                'admin_head_nip'            => $request->admin_head_nip,
                'budget_date'               => $request->budget_date,
                'offer_date'                => $request->offer_date,
                'work_order_number'         => $request->work_order_number,
                'work_order_date'           => $request->work_order_date,
                'handover_report_number'    => $request->handover_report_number,
                'handover_report_date'      => $request->handover_report_date,
                'payment_report_number'     => $request->payment_report_number,
                'payment_report_date'       => $request->payment_report_date,
                'receipt_number'            => $request->receipt_number,
                'receipt_date'              => $request->receipt_date,
                'receipt_receiver'          => $request->receipt_receiver,
                'invitation_number'         => $request->invitation_number,
                'invitation_date'           => $request->invitation_date,
                'tax_vat'                   => $request->tax_ppn ?? $project->tax_vat,
                'tax_pph21'                 => $request->tax_pph21 ?? $project->tax_pph21,
                'tax_pph22'                 => $request->tax_pph22 ?? $project->tax_pph22,
                'tax_pph23'                 => $request->tax_pph23 ?? $project->tax_pph23,
                'tax_pph_final'             => $request->tax_pph_final ?? $project->tax_pph_final,
            ]);
            $project->items()->delete();
            foreach ($request->items as $item) {
                $project->items()->create([
                    'type'       => $item['type'],
                    'item_name'  => $item['item_name'],
                    'quantity'   => $item['quantity'],
                    'unit'       => $item['unit'],
                    'unit_price' => $item['unit_price'],
                ]);
            }
            $workerIds = array_filter($request->workers);
            $project->workers()->sync($workerIds);
            $project->schedules()->delete();
            foreach ($attendanceDates as $date) {
                $project->schedules()->create([
                    'working_date' => $date,
                ]);
            }
            if ($request->filled('delete_images')) {
                $idsToDelete = array_filter($request->input('delete_images'));
                foreach ($idsToDelete as $imgId) {
                    $img = $project->images()->find($imgId);
                    if ($img) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($img->file_path);
                        $img->delete();
                    }
                }
            }
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('dokumentasi_spj', 'public'); 

                    $project->images()->create([
                        'file_path' => $path,
                    ]);
                }
            }
            if ($request->hasFile('kop_school')) {
                if ($project->kop_school) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($project->kop_school);
                }
                $project->update(['kop_school' => $request->file('kop_school')->store('kop', 'public')]);
            }
            if ($request->hasFile('kop_contractor')) {
                if ($project->kop_contractor) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($project->kop_contractor);
                }
                $project->update(['kop_contractor' => $request->file('kop_contractor')->store('kop', 'public')]);
            }

            if ($request->input('clear_kop_school') == '1') {
                if ($project->kop_school) {
                    \Storage::disk('public')->delete($project->kop_school);
                }
                $project->update(['kop_school' => null]);
            }

            if ($request->input('clear_kop_contractor') == '1') {
                if ($project->kop_contractor) {
                    \Storage::disk('public')->delete($project->kop_contractor);
                }
                $project->update(['kop_contractor' => null]);
            }
            DB::commit();
            return redirect()->route('projects.index')->with('success', 'Data SPJ berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Gagal memperbarui data! Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $project = Project::with('images')->findOrFail($id);
            $project->workers()->detach();
            $project->items()->delete();
            $project->schedules()->delete();
            if ($project->images->count() > 0) {
                foreach ($project->images as $img) {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($img->file_path)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($img->file_path);
                    }
                }
                $project->images()->delete();
            }
            $project->delete();
            DB::commit();
            return redirect()->route('projects.index')
                ->with('success', 'Data SPJ beserta file dokumentasi berhasil dihapus permanen!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data! Error: ' . $e->getMessage());
        }
    }

    public function exportWord($id)
    {   
        Carbon::setLocale('id');
        $project = Project::with([
            'items',
            'projectWorkers.worker.role',
            'schedules',
            'images',
            'contractor'
        ])->findOrFail($id);

        $templatePath = storage_path('app/public/template_spj.docx');
        if (!file_exists($templatePath)) {
            return back()->with('error', 'File template_spj.docx tidak ditemukan di server.');
        }

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $safeName        = preg_replace('/[^A-Za-z0-9_\-]/', '_', $project->project_name);
        $workingTemplate = $tempDir . '/tpl_' . $safeName . '_' . time() . '.docx';
        copy($templatePath, $workingTemplate);

        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);

        $this->replaceKopDocx($workingTemplate, $project);

        $templateProcessor = new TemplateProcessor($workingTemplate);
        $images = $project->images;
        foreach ($images as $index => $img) {
            $i = $index + 1;
            if ($i > 3) break; 
            
            $path = storage_path('app/public/' . $img->file_path);


            if (file_exists($path)) {
                $templateProcessor->setImageValue('img_manual_' . $i, [
                    'path' => $path, 'width' => 600, 'height' => 350, 'ratio' => true
                ]);
            }
        }
        for ($j = $images->count() + 1; $j <= 3; $j++) {
            $templateProcessor->setValue('img_manual_' . $j, '');
        }
        if ($images->count() > 0) {
            $templateProcessor->cloneBlock('block_foto', $images->count(), true, true);

            foreach ($images as $index => $img) {
                $i = $index + 1;
                $path = storage_path('app/public/' . $img->file_path);

                if (file_exists($path)) {
                    $templateProcessor->setImageValue('img_item#' . $i, [
                        'path' => $path, 'width' => 630, 'height' => 350, 'ratio' => true
                    ]);
                }
            }
        } else {
            $templateProcessor->deleteBlock('block_foto');
        }


        
        $formatHarga = function($angka) {
            $format = number_format($angka, 0, ',', '.');
            if ($angka >= 1000000) return "Rp   " . $format;
            if ($angka >= 100000) return "Rp     " . $format;
            if ($angka >= 10000) return "Rp       " . $format;
            return "Rp         " . $format; 
        };

        $formatSubtotal = function($angka) {
            $format = number_format($angka, 0, ',', '.');
            if ($angka >= 10000000) return "Rp  " . $format;
            if ($angka >= 1000000) return "Rp    " . $format;
            if ($angka >= 100000) return "Rp       " . $format;
            if ($angka >= 10000) return "Rp         " . $format;
            return "Rp            " . $format; 
        };


        $templateProcessor->setValue('project_name', strtoupper($project->project_name)); 
        $templateProcessor->setValue('year', Carbon::parse($project->start_date)->format('Y')); 
        $templateProcessor->setValue('principal_name', $project->principal_name ?? '-');
        $templateProcessor->setValue('principal_nip', $project->principal_nip ?? '-');
        $templateProcessor->setValue('facilities_head_name', $project->facilities_head_name ?? '-');
        $templateProcessor->setValue('facilities_head_nip', $project->facilities_head_nip ?? '-');
        $templateProcessor->setValue('treasurer_name', $project->treasurer_name ?? '-');
        $templateProcessor->setValue('treasurer_nip', $project->treasurer_nip ?? '-');
        $templateProcessor->setValue('admin_head_name', $project->admin_head_name ?? '-');
        $templateProcessor->setValue('admin_head_nip', $project->admin_head_nip ?? '-');
        $formatHanyaTanggal = function($tanggal) {
            if (!$tanggal) return '..........';
            return Carbon::parse($tanggal)->translatedFormat('d F Y');
        };

        $formatTanggalDenganHari = function($tanggal) {
            if (!$tanggal) return '.........., ..........';
            return Carbon::parse($tanggal)->translatedFormat('l, d F Y');
        };

        $formatTanggalAngka = function($tanggal) {
            if (!$tanggal) return '..-..-....';
            return Carbon::parse($tanggal)->format('d-m-Y');
        };

        $startDate = Carbon::parse($project->start_date);
        $endDate = Carbon::parse($project->end_date);
        $dtHandover = Carbon::parse($project->handover_report_date);
        $dtPayment = Carbon::parse($project->payment_report_date);
        
        if ($startDate->year == $endDate->year) {
            if ($startDate->month == $endDate->month) {
                $rangeTanggal = $startDate->format('d') . ' s.d ' . $endDate->translatedFormat('d F Y');
            } else {
                $rangeTanggal = $startDate->translatedFormat('d F') . ' s.d ' . $endDate->translatedFormat('d F Y');
            }
        } else {
            $rangeTanggal = $startDate->translatedFormat('d F Y') . ' s.d ' . $endDate->translatedFormat('d F Y');
        }

        $templateProcessor->setValue('invitation_date', $formatHanyaTanggal($project->invitation_date));
        $templateProcessor->setValue('invitation_date_monthly', $formatTanggalAngka($project->invitation_date));
        $templateProcessor->setValue('contractor_date', $formatTanggalDenganHari($project->contractor_date));
        $templateProcessor->setValue('budget_date', $formatHanyaTanggal($project->budget_date));
        $templateProcessor->setValue('offer_date', $formatHanyaTanggal($project->offer_date));
        $templateProcessor->setValue('work_order_date', $formatHanyaTanggal($project->work_order_date));
        $templateProcessor->setValue('handover_report_date', $formatHanyaTanggal($project->handover_report_date));
        $templateProcessor->setValue('receipt_date', $formatHanyaTanggal($project->receipt_date));
        $templateProcessor->setValue('company_name', $project->contractor->company_name ?? '-');
        $templateProcessor->setValue('company_address', $project->contractor->address ?? '-');
        $templateProcessor->setValue('invitation_number', $project->invitation_number ?? '-');
        $templateProcessor->setValue('contractor_number', $project->contractor_number ?? '-');
        $templateProcessor->setValue('work_order_number', $project->work_order_number ?? '-');
        $templateProcessor->setValue('handover_report_number', $project->handover_report_number ?? '-');
        $templateProcessor->setValue('receipt_number', $project->receipt_number ?? '-');
        $templateProcessor->setValue('receipt_receiver', $project->receipt_receiver ?? '-');
        $templateProcessor->setValue('target_days' , $project->target_days);
        $templateProcessor->setValue('person_in_charge', $project->contractor->person_in_charge ?? '-');
        $materials = $project->items->where('type', 'material')->values();
        $labors = $project->items->where('type', 'labor')->values();

        $total_material = 0;
        $total_labor = 0;

        $jmlHari = $project->target_days ? (int)$project->target_days : $startDate->diffInDays($endDate) + 1;

                        $templateProcessor->cloneRow('m_name', max(1, $materials->count())); 
        if ($materials->count() > 0) {
            foreach ($materials as $index => $item) {
                $i = $index + 1;
                $subtotal = $item->quantity * $item->unit_price;
                $total_material += $subtotal;

                $templateProcessor->setValue('m_no#' . $i, $i);
                $templateProcessor->setValue('m_name#' . $i, $item->item_name);
                $templateProcessor->setValue('m_qty#' . $i, $item->quantity);
                $templateProcessor->setValue('m_unit#' . $i, $item->unit);
                
                $templateProcessor->setValue('m_price#' . $i, $formatHarga($item->unit_price));
                $templateProcessor->setValue('m_sub#' . $i, $formatSubtotal($subtotal));
            }   
        } else {
            $templateProcessor->setValue('m_no#1', '-');
            $templateProcessor->setValue('m_name#1', 'Tidak ada data');
            $templateProcessor->setValue('m_qty#1', '-');
            $templateProcessor->setValue('m_unit#1', '-');
            $templateProcessor->setValue('m_price#1', '-');
            $templateProcessor->setValue('m_sub#1', '-');
        }
        $templateProcessor->setValue('subtotal_m', $formatSubtotal($total_material));

        $terbilang = function ($angka) use (&$terbilang) {
            $angka = abs($angka);
            $baca = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
            $hasil = "";
            
            if ($angka < 12) {
                $hasil = " " . $baca[$angka];
            } else if ($angka < 20) {
                $hasil = $terbilang($angka - 10) . " Belas";
            } else if ($angka < 100) {
                $hasil = $terbilang(floor($angka / 10)) . " Puluh" . $terbilang($angka % 10);
            } else if ($angka < 200) {
                $hasil = " Seratus" . $terbilang($angka - 100);
            } else if ($angka < 1000) {
                $hasil = $terbilang(floor($angka / 100)) . " Ratus" . $terbilang($angka % 100);
            } else if ($angka < 2000) {
                $hasil = " Seribu" . $terbilang($angka - 1000);
            } else if ($angka < 1000000) {
                $hasil = $terbilang(floor($angka / 1000)) . " Ribu" . $terbilang($angka % 1000);
            } else if ($angka < 1000000000) {
                $hasil = $terbilang(floor($angka / 1000000)) . " Juta" . $terbilang($angka % 1000000);
            } else if ($angka < 1000000000000) {
                $hasil = $terbilang(floor($angka / 1000000000)) . " Milyar" . $terbilang(fmod($angka, 1000000000));
            }
            return $hasil;
        };

        $formatTerbilang = function($angka) use ($terbilang) {
            if ($angka == 0) return "Rp. 0,- (Nol Rupiah)";
            $rupiah = number_format($angka, 0, ',', '.');
            $teks = trim($terbilang($angka));
            return "Rp. " . $rupiah . ",- (" . $teks . " Rupiah)";
        };

        $templateProcessor->setValue('total_material_terbilang', $formatTerbilang($total_material));
        $hariTerbilang = strtolower(trim($terbilang($jmlHari)));

        $kalimatWaktu = $jmlHari . ' (' . $hariTerbilang . ') hari, tanggal ' . $rangeTanggal;
        $templateProcessor->setValue('execution_time_full', $kalimatWaktu);

        $hariBA = $dtHandover->translatedFormat('l'); 
        $tglTerbilang = trim($terbilang($dtHandover->day)); 
        $bulanBA = $dtHandover->translatedFormat('F'); 
        $thnTerbilang = trim($terbilang($dtHandover->year)); 
        $tglAngkaBA = $dtHandover->format('d-m-Y'); 

        $formatBA = "$hariBA tanggal $tglTerbilang bulan $bulanBA Tahun $thnTerbilang ($tglAngkaBA)";
        $templateProcessor->setValue('handover_date_tgl', $tglAngkaBA);
        $templateProcessor->setValue('handover_date_formal', $formatBA);

        $hariPay = $dtPayment->translatedFormat('l'); 
        $tglTerbilangPay = trim($terbilang($dtPayment->day)); 
        $bulanPay = $dtPayment->translatedFormat('F'); 
        $thnTerbilangPay = trim($terbilang($dtPayment->year)); 
        $tglAngkaPay = $dtPayment->format('d-m-Y'); 

        $formatPay = "$hariPay tanggal $tglTerbilangPay bulan $bulanPay Tahun $thnTerbilangPay ($tglAngkaPay)";
        
        $templateProcessor->setValue('payment_date_formal', $formatPay);
        $templateProcessor->setValue('payment_report_number', $project->payment_report_number ?? '-');

        $hariTerbilang = strtolower(trim($terbilang($jmlHari))); 
        $tahunProyek = $startDate->format('Y');

        $executionTimeBaru = "$tahunProyek dengan jangka waktu $jmlHari ($hariTerbilang) hari kalender pada tanggal $rangeTanggal";
        $templateProcessor->setValue('execution_time_full_v2', $executionTimeBaru);
        
        if ($startDate->year == $endDate->year) {
            if ($startDate->month == $endDate->month) {
                $rangeTanggal = $startDate->format('d') . ' s.d ' . $endDate->translatedFormat('d F Y');
            } else {
                $rangeTanggal = $startDate->translatedFormat('d F') . ' s.d ' . $endDate->translatedFormat('d F Y');
            }
        } else {
            $rangeTanggal = $startDate->translatedFormat('d F Y') . ' s.d ' . $endDate->translatedFormat('d F Y');
        }
        $laborsData = [];
        $total_labor = 0;
        
        $totalHariKerja = $project->schedules->count(); 

        $groupedWorkers = $project->projectWorkers->groupBy(function ($pw) {
            return $pw->worker->worker_role_id;
        });

        foreach ($groupedWorkers as $roleId => $pws) {
            $role = $pws->first()->worker->role;
            $jumlahPekerja = $pws->count();
            $qty = $totalHariKerja * $jumlahPekerja;
            
            $laborsData[] = (object)[
                'item_name'  => $role->role_name,         
                'quantity'   => $qty,                      
                'unit'       => 'Orang',                   
                'unit_price' => $role->default_wage,
            ];
        }
        $labors = collect($laborsData);

        $templateProcessor->cloneRow('l_name', max(1, $labors->count()));
        if ($labors->count() > 0) {
            foreach ($labors as $index => $item) {
                $i = $index + 1;
                $subtotal = $item->quantity * $item->unit_price;
                $total_labor += $subtotal;

                $templateProcessor->setValue('l_no#' . $i, $i);
                $templateProcessor->setValue('l_name#' . $i, $item->item_name); 
                $templateProcessor->setValue('l_qty#' . $i, $item->quantity);  
                $templateProcessor->setValue('l_unit#' . $i, $item->unit);     
                
                $templateProcessor->setValue('l_price#' . $i, $formatHarga($item->unit_price)); 
                $templateProcessor->setValue('l_sub#' . $i, $formatSubtotal($subtotal));
            }
        } else {
            $templateProcessor->setValue('l_no#1', '-');
            $templateProcessor->setValue('l_name#1', 'Tidak ada data');
            $templateProcessor->setValue('l_qty#1', '-');
            $templateProcessor->setValue('l_unit#1', '-');
            $templateProcessor->setValue('l_price#1', '-');
            $templateProcessor->setValue('l_sub#1', '-');
        }
        $templateProcessor->setValue('subtotal_l', $formatSubtotal($total_labor));

        $grand_total = $total_material + $total_labor;
        $templateProcessor->setValue('grand_total', $formatSubtotal($grand_total));

        $fileName = 'SPJ_' .$project->project_name .'.docx';
        $tempDir = storage_path('app/temp');
        $tempFile = $tempDir . '/' . $fileName;

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $templateProcessor->cloneRow('m2_name', max(1, $materials->count())); 
        
        if ($materials->count() > 0) {
            foreach ($materials as $index => $item) {
                $i = $index + 1;
                $templateProcessor->setValue('m2_no#' . $i, $i);
                $templateProcessor->setValue('m2_name#' . $i, $item->item_name);
                $templateProcessor->setValue('m2_qty#' . $i, $item->quantity);
                $templateProcessor->setValue('m2_unit#' . $i, $item->unit);
            }   
        } else {
            $templateProcessor->setValue('m2_no#1', '-');
            $templateProcessor->setValue('m2_name#1', 'Tidak ada data');
            $templateProcessor->setValue('m2_qty#1', '-');
            $templateProcessor->setValue('m2_unit#1', '-');
        }

        $templateProcessor->cloneRow('m3_name', max(1, $materials->count())); 
        
        if ($materials->count() > 0) {
            foreach ($materials as $index => $item) {
                $i = $index + 1;
                $subtotal = $item->quantity * $item->unit_price;

                $templateProcessor->setValue('m3_no#' . $i, $i);
                $templateProcessor->setValue('m3_name#' . $i, $item->item_name);
                $templateProcessor->setValue('m3_qty#' . $i, $item->quantity);
                $templateProcessor->setValue('m3_unit#' . $i, $item->unit);
                $templateProcessor->setValue('m3_price#' . $i, $formatHarga($item->unit_price));
                $templateProcessor->setValue('m3_sub#' . $i, $formatSubtotal($subtotal));
            }   
        } else {
            $templateProcessor->setValue('m3_no#1', '-');
            $templateProcessor->setValue('m3_name#1', 'Tidak ada data');
            $templateProcessor->setValue('m3_qty#1', '-');
            $templateProcessor->setValue('m3_unit#1', '-');
            $templateProcessor->setValue('m3_price#1', '-');
            $templateProcessor->setValue('m3_sub#1', '-');
        }

        $templateProcessor->cloneRow('m4_name', max(1, $materials->count())); 
        
        if ($materials->count() > 0) {
            foreach ($materials as $index => $item) {
                $i = $index + 1;
                $subtotal = $item->quantity * $item->unit_price;

                $templateProcessor->setValue('m4_no#' . $i, $i);
                $templateProcessor->setValue('m4_name#' . $i, $item->item_name);
                $templateProcessor->setValue('m4_qty#' . $i, $item->quantity);
                $templateProcessor->setValue('m4_unit#' . $i, $item->unit);
                $templateProcessor->setValue('m4_price#' . $i, $formatHarga($item->unit_price));
                $templateProcessor->setValue('m4_sub#' . $i, $formatSubtotal($subtotal));
            }   
        } else {
            $templateProcessor->setValue('m4_no#1', '-');
            $templateProcessor->setValue('m4_name#1', 'Tidak ada data');
            $templateProcessor->setValue('m4_qty#1', '-');
            $templateProcessor->setValue('m4_unit#1', '-');
            $templateProcessor->setValue('m4_price#1', '-');
            $templateProcessor->setValue('m4_sub#1', '-');
        }

        $templateProcessor->cloneRow('m5_name', max(1, $materials->count())); 
        
        if ($materials->count() > 0) {
            foreach ($materials as $index => $item) {
                $i = $index + 1;
                $subtotal = $item->quantity * $item->unit_price;

                $templateProcessor->setValue('m5_no#' . $i, $i);
                $templateProcessor->setValue('m5_name#' . $i, $item->item_name);
                $templateProcessor->setValue('m5_qty#' . $i, $item->quantity);
                $templateProcessor->setValue('m5_unit#' . $i, $item->unit);
            }   
        } else {
            $templateProcessor->setValue('m5_no#1', '-');
            $templateProcessor->setValue('m5_name#1', 'Tidak ada data');
            $templateProcessor->setValue('m5_qty#1', '-');
            $templateProcessor->setValue('m5_unit#1', '-');
        }

        $templateProcessor->cloneRow('m6_name', max(1, $materials->count())); 
        
        if ($materials->count() > 0) {
            foreach ($materials as $index => $item) {
                $i = $index + 1;
                $subtotal = $item->quantity * $item->unit_price;

                $templateProcessor->setValue('m6_no#' . $i, $i);
                $templateProcessor->setValue('m6_name#' . $i, $item->item_name);
                $templateProcessor->setValue('m6_qty#' . $i, $item->quantity);
                $templateProcessor->setValue('m6_unit#' . $i, $item->unit);
                $templateProcessor->setValue('m6_price#' . $i, $formatHarga($item->unit_price));
                $templateProcessor->setValue('m6_sub#' . $i, $formatSubtotal($subtotal));
            }   
        } else {
            $templateProcessor->setValue('m6_no#1', '-');
            $templateProcessor->setValue('m6_name#1', 'Tidak ada data');
            $templateProcessor->setValue('m6_qty#1', '-');
            $templateProcessor->setValue('m6_unit#1', '-');
            $templateProcessor->setValue('m6_price#1', '-');
            $templateProcessor->setValue('m6_sub#1', '-');
        }

        $templateProcessor->saveAs($tempFile);
        @unlink($workingTemplate);

        // Fix XML orphan tags dari deleteBlock/cloneBlock PhpWord
        $this->repairDocxXml($tempFile);

        $fileName = 'SPJ_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $project->project_name) . '_' . time() . '.docx';

        // Gunakan response()->download() dengan header anti-compression
        // agar Cloudflare/proxy tidak merusak file .docx (yang sudah ZIP)
        $headers = [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Encoding'    => 'identity',
            'Cache-Control'       => 'no-store, no-transform',
            'X-Content-Type-Options' => 'nosniff',
        ];

        return response()->download($tempFile, $fileName, $headers)->deleteFileAfterSend(true);
    }

    private function replaceKopDocx(string $docxPath, $project): void
    {
        $kopMap = [
            'kop_contractor'   => 'image3.jpg',
            'kop_school' => 'image1.jpg',
            
        ];

        foreach ($kopMap as $field => $oldMediaFilename) {
            if (empty($project->$field) || $project->$field === 'null') {
                continue; 
            }
            
            $uploadedPath = storage_path('app/public/' . $project->$field);
            if (!file_exists($uploadedPath)) {
                continue; 
            }
            $this->swapImageInDocx($docxPath, $uploadedPath, $oldMediaFilename);
        }
    }

    private function swapImageInDocx(
    string $docxPath,
    string $newImagePath,
    string $oldMediaFilename
    ): void {
        $zip = new \ZipArchive();
        if ($zip->open($docxPath) !== true) {
            return;
        }

        $newExt   = strtolower(pathinfo($newImagePath, PATHINFO_EXTENSION));
        $oldExt   = strtolower(pathinfo($oldMediaFilename, PATHINFO_EXTENSION));
        $baseName = pathinfo($oldMediaFilename, PATHINFO_FILENAME);
        $newMedia = $baseName . '.' . $newExt;

        // 1. Tulis file gambar baru
        $zip->addFromString('word/media/' . $newMedia, file_get_contents($newImagePath));

        // 2. Kalau ekstensi berubah → update rels & content types
        if ($newExt !== $oldExt) {
            $zip->deleteName('word/media/' . $oldMediaFilename);

            $rels = $zip->getFromName('word/_rels/document.xml.rels');
            $rels = str_replace(
                'Target="media/' . $oldMediaFilename . '"',
                'Target="media/' . $newMedia . '"',
                $rels
            );
            $zip->addFromString('word/_rels/document.xml.rels', $rels);

            $mime = match($newExt) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png'         => 'image/png',
                'gif'         => 'image/gif',
                default       => 'image/png',
            };
            $contentTypes = $zip->getFromName('[Content_Types].xml');
            if (stripos($contentTypes, 'Extension="' . $newExt . '"') === false) {
                $contentTypes = str_replace(
                    '</Types>',
                    '<Default Extension="' . $newExt . '" ContentType="' . $mime . '"/></Types>',
                    $contentTypes
                );
                $zip->addFromString('[Content_Types].xml', $contentTypes);
            }
        }

        $doc = $zip->getFromName('word/document.xml');

 
 
        $doc = preg_replace_callback(
            '/<pic:pic>(.*?)<\/pic:pic>/s',
            function ($matches) use ($oldMediaFilename, $newMedia) {
                $block = $matches[0];
              
                if (
                    str_contains($block, 'name="' . $oldMediaFilename . '"') ||
                    str_contains($block, 'name="' . $newMedia . '"')
                ) {
                    
                    $block = preg_replace(
                        '/<a:srcRect[^\/]*\/>/',
                        '<a:srcRect/>',
                        $block
                    );
                }
                return $block;
            },
            $doc
        );

        $zip->addFromString('word/document.xml', $doc);
        $zip->close();
    }

    /**
     * Repair broken XML in generated .docx files.
     * PhpWord's deleteBlock/cloneBlock can leave orphaned <w:p> tags
     * (opening tag without closing tag), making the XML invalid and
     * Word unable to open the file.
     */
    private function repairDocxXml(string $docxPath): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($docxPath) !== true) {
            return;
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            return;
        }

        // Cari dan hapus <w:p ...> yang orphan (tidak punya </w:p> penutup)
        // Pattern: <w:p ...> langsung diikuti <w:p ...> tanpa </w:p> di antaranya
        // Ini terjadi ketika deleteBlock menghapus konten tapi menyisakan tag pembuka
        $repaired = preg_replace(
            '/<w:p\b[^>]*>(\s*)<w:p\b/',
            '<w:p',
            $xml
        );

        // Jika ada perubahan, simpan kembali
        if ($repaired !== $xml) {
            $zip->addFromString('word/document.xml', $repaired);
        }

        $zip->close();
    }

    private function setTtdKwitansi($sheet, $project) {
        $sheet->setCellValue('A29', $project->treasurer_name ?? 'Gunarni Sugeng Saputra');
        $sheet->setCellValue('A30', 'NIP. ' . ($project->treasurer_nip ?? '19750519 200701 1 009'));
        $sheet->setCellValue('E29', $project->principal_name ?? 'Sukidi, S.Pd., M.Pd.');
        $sheet->setCellValue('E30', 'NIP. ' . ($project->principal_nip ?? '197003101997031004'));
        $sheet->setCellValue('I29', $project->admin_head_name ?? 'Nama Kasubag TU'); 
        $sheet->setCellValue('I30', 'NIP. ' . ($project->admin_head_nip ?? 'NIP Kasubag TU')); 
        $sheet->getStyle('A29')->getFont()->setBold(true)->setUnderline(true);
        $sheet->getStyle('E29')->getFont()->setBold(true)->setUnderline(true);
        $sheet->getStyle('I29')->getFont()->setBold(true)->setUnderline(true);
    }

    private function formatKwitansi($nominal) {
        $huruf = trim($this->terbilang($nominal));
        return "Rp. " . number_format($nominal, 0, ',', '.') . ",- (" . $huruf . " Rupiah)";
    }

    private function terbilang($angka) {
        $angka = abs($angka);
        $baca  = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $terbilang = "";

        if ($angka < 12) {
            $terbilang = " " . $baca[$angka];
        } else if ($angka < 20) {
            $terbilang = $this->terbilang($angka - 10) . " Belas";
        } else if ($angka < 100) {
            $terbilang = $this->terbilang($angka / 10) . " Puluh" . $this->terbilang($angka % 10);
        } else if ($angka < 200) {
            $terbilang = " Seratus" . $this->terbilang($angka - 100);
        } else if ($angka < 1000) {
            $terbilang = $this->terbilang($angka / 100) . " Ratus" . $this->terbilang($angka % 100);
        } else if ($angka < 2000) {
            $terbilang = " Seribu" . $this->terbilang($angka - 1000);
        } else if ($angka < 1000000) {
            $terbilang = $this->terbilang($angka / 1000) . " Ribu" . $this->terbilang($angka % 1000);
        } else if ($angka < 1000000000) {
            $terbilang = $this->terbilang($angka / 1000000) . " Juta" . $this->terbilang($angka % 1000000);
        } else if ($angka < 1000000000000) {
            $terbilang = $this->terbilang($angka / 1000000000) . " Miliar" . $this->terbilang($angka % 1000000000);
        }

        return $terbilang;
    }

    public function exportKwitansi($id)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');

        $project = Project::with(['projectWorkers.worker', 'schedules', 'items', 'contractor'])->findOrFail($id);

        $templatePath = storage_path('app/public/template_kwitansi.xlsx');
        if (!file_exists($templatePath)) {
            return back()->with('error', 'File template_kwitansi  .xlsx tidak ditemukan.');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $sheetBahan  = $spreadsheet->getSheetByName('Bahan');
        $sheetTukang = $spreadsheet->getSheetByName('Tukang');

        if (!$sheetBahan || !$sheetTukang) {
            return back()->with('error', 'Sheet Kwitansi Bahan/Tukang tidak ditemukan di template!');
        }
        $subtotalM = $project->items->sum(function($item) {
            return $item->unit_price * $item->quantity;
        });

        $jadwalKerja = $project->schedules->pluck('working_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d))
            ->filter(fn($d) => !$d->isSunday());
        
        $totalHari = $jadwalKerja->count();
        $subtotalL = 0;
        foreach ($project->projectWorkers as $pw) {
            $subtotalL += $totalHari * ($pw->worker->daily_wage ?? 150000);
        }

        $tglTerakhir = $jadwalKerja->last();
        $tahun = $tglTerakhir ? $tglTerakhir->format('Y') : date('Y');
        $tglFormat = $tglTerakhir ? $tglTerakhir->format('d/m/Y') : date('d/m/Y');
        $sheetBahan->setCellValue('C5', $tahun);
        $sheetBahan->setCellValue('E5', 'No.KK' . substr($tahun, -2)); // Dinamis: No.KK26, No.KK27, dst
        $sheetBahan->setCellValue('C6', $tglFormat);
        $sheetBahan->setCellValue('C9', $this->formatKwitansi($subtotalM));
        $sheetBahan->setCellValue('C10', $project->contractor->company_name ?? 'TB. SUKSES');
        $sheetBahan->setCellValue('C11', 'Bahan dan Material Bangunan ' . $project->project_name);
        $pct_ppn   = $project->tax_vat ?? 0;
        $pct_pph22 = $project->tax_pph22 ?? 0;
        $pct_pph21 = $project->tax_pph21 ?? 0;
        $pct_pph23 = $project->tax_pph23 ?? 0;
        $pct_final = $project->tax_pph_final ?? 0; 

        $totalPajakBahan = 0;
        if ($pct_ppn > 0) {
            $val = $subtotalM * ($pct_ppn / 100);
            $sheetBahan->setCellValue('J6', $val);
            $totalPajakBahan += $val;
        } else {
            $sheetBahan->setCellValue('J6', '-');
        }

        if ($pct_pph22 > 0) {
            $val = $subtotalM * ($pct_pph22 / 100);
            $sheetBahan->setCellValue('J7', $val);
            $totalPajakBahan += $val;
        } else {
            $sheetBahan->setCellValue('J7', '-');
        }

        if ($pct_pph21 > 0) {
            $val = $subtotalM * ($pct_pph21 / 100);
            $sheetBahan->setCellValue('J8', $val);
            $totalPajakBahan += $val;
        } else {
            $sheetBahan->setCellValue('J8', '-');
        }

        if ($pct_pph23 > 0) {
            $val = $subtotalM * ($pct_pph23 / 100);
            $sheetBahan->setCellValue('J9', $val);
            $totalPajakBahan += $val;
        } else {
            $sheetBahan->setCellValue('J9', '-');
        }

        if ($pct_final > 0) {
            $val = $subtotalM * ($pct_final / 100);
            $sheetBahan->setCellValue('J10', $val);
            $totalPajakBahan += $val;
        } else {
            $sheetBahan->setCellValue('J10', '-');
        }

        $sheetBahan->setCellValue('J12', ($totalPajakBahan > 0) ? $totalPajakBahan : '-');

        $this->setTtdKwitansi($sheetBahan, $project);
        $sheetTukang->setCellValue('C5', $tahun);
        $sheetTukang->setCellValue('E5', 'No.KK' . substr($tahun, -2));
        $sheetTukang->setCellValue('C6', $tglFormat);
        $sheetTukang->setCellValue('C9', $this->formatKwitansi($subtotalL));
        $sheetTukang->setCellValue('C10', 'Tukang / Pekerja'); 
        $sheetTukang->setCellValue('C11', 'Honorarium tenaga ' . $project->project_name);
        $sheetTukang->setCellValue('J6', '-');
        $sheetTukang->setCellValue('J7', '-'); 
        $sheetTukang->setCellValue('J8', '-');  
        $sheetTukang->setCellValue('J9', '-');  
        $sheetTukang->setCellValue('J10', '-'); 
        $sheetTukang->setCellValue('J12', '-'); 

        $this->setTtdKwitansi($sheetTukang, $project);
        $sheetsToKeep = ['Bahan', 'Tukang']; 
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (!in_array($sheetName, $sheetsToKeep)) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($sheet));
            }
        }

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $project->project_name);
        $fileName  = 'Kwitansi_' . $safeName . '.xlsx';
        $tempFile = storage_path('app/temp/' . $fileName);

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(false);
        $writer->save($tempFile);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return response()->download($tempFile, $fileName, [
            'Content-Type'     => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Encoding' => 'identity',
            'Cache-Control'    => 'no-store, no-transform',
        ])->deleteFileAfterSend(true);
    }

    public function exportAbsensi($id)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '600');

        $project = Project::with(['projectWorkers.worker.role', 'schedules', 'items', 'contractor'])->findOrFail($id);

        $templatePath = storage_path('app/public/template_absensi.xlsx');
        if (!file_exists($templatePath)) {
            return back()->with('error', 'File template_absensi.xlsx tidak ditemukan.');
        }
        
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        
        $templateAbsensi   = $spreadsheet->getSheetByName('Absensi');
        $templateHonor     = $spreadsheet->getSheetByName('Honor');
        $templateRealisasi = $spreadsheet->getSheetByName('Realisasi Template');
        $templateAlamat    = $spreadsheet->getSheetByName('Alamat Pekerja Template');

        if (!$templateAbsensi || !$templateHonor || !$templateRealisasi || !$templateAlamat) {
            return back()->with('error', 'Nama sheet template salah. Pastikan namanya: Absensi, Honor, Realisasi, Alamat Pekerja');
        }

        if ($project->schedules->isEmpty()) {
            return back()->with('error', 'Jadwal kerja belum diatur!');
        }

        $jadwalKerja = $project->schedules
            ->pluck('working_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d))
            ->filter(fn($d) => !$d->isSunday())
            ->sortBy(fn($d) => $d->timestamp)
            ->values();

        $jadwalPerMinggu = $jadwalKerja->groupBy(fn($d) => $d->format('o-W'))->sortKeys();

        $workers = $project->projectWorkers;
        $workerCount = $workers->count();
        $workerCache = $workers->map(fn($pw) => [
            'name'    => $pw->worker->name,
            'role'    => $pw->worker->role->role_name,
            'wage'    => $pw->worker->daily_wage ?? 150000,
            'address' => $pw->worker->address ?? '-',
            'phone'   => $pw->worker->phone ?? '-',
        ])->values()->toArray();

        $totalSemuaHonor = 0;
        $mingguKe = 1;
        foreach ($jadwalPerMinggu as $weekKey => $dates) {
            $tglAwal      = $dates->first()->format('d');
            $tglAkhirFull = $dates->last()->translatedFormat('d F Y');
            $periodeLabel = "Periode Minggu $mingguKe : $tglAwal - $tglAkhirFull";

            $sheetAbsen = clone $templateAbsensi;
            $sheetAbsen->setTitle($mingguKe);
            $spreadsheet->addSheet($sheetAbsen);
            $sheetAbsen->setCellValue('A2', strtoupper($project->project_name));
            $sheetAbsen->getStyle('A2:I2')->getAlignment()
                       ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheetAbsen->setCellValue('F12', $periodeLabel);

            $colTanggal = 'D';
            foreach ($dates as $date) {
                $sheetAbsen->setCellValue($colTanggal . '14', $date->format('d/n/Y'));
                $colTanggal++;
            }

            $rowAbsen = 15; 
            $sheetAbsen->removeRow($rowAbsen + 1, 3); 

            if ($workerCount > 1) {
                $sheetAbsen->insertNewRowBefore($rowAbsen + 1, $workerCount - 1);
                $style = $sheetAbsen->getStyle("A{$rowAbsen}:I{$rowAbsen}");
                $sheetAbsen->duplicateStyle($style, "A" . ($rowAbsen + 1) . ":I" . ($rowAbsen + $workerCount - 1));
            }

            $dataAbsen = [];
            foreach ($workerCache as $idx => $w) {
                $dataAbsen[] = [$idx + 1, $w['name'], $w['role']];
            }
            
            $sheetAbsen->fromArray($dataAbsen, null, 'A' . $rowAbsen);
            $sheetAbsen->getStyle("B{$rowAbsen}:C" . ($rowAbsen + $workerCount - 1))
                       ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            $this->generateFooter($sheetAbsen, $rowAbsen + $workerCount, $tglAkhirFull, $project, false);
            $mingguKe++;
        }
        $mingguKe = 1;
        foreach ($jadwalPerMinggu as $weekKey => $dates) {
            $tglAwal      = $dates->first()->format('d');
            $tglAkhirFull = $dates->last()->translatedFormat('d F Y');
            $periodeLabel = "Periode Minggu $mingguKe : $tglAwal - $tglAkhirFull";

            $sheetHonor = clone $templateHonor;
            $sheetHonor->setTitle('Honor ' . $mingguKe);
            $spreadsheet->addSheet($sheetHonor);
            $sheetHonor->setCellValue('A2', strtoupper($project->project_name));
            $sheetHonor->getStyle('A2:H2')->getAlignment()
                       ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheetHonor->setCellValue('E12', null);
            $sheetHonor->setCellValue('F12', $periodeLabel);

            $rowHonor = 14; 

            if ($workerCount > 1) {
                $sheetHonor->insertNewRowBefore($rowHonor + 1, $workerCount - 1);
                $styleH = $sheetHonor->getStyle("A{$rowHonor}:H{$rowHonor}");
                $sheetHonor->duplicateStyle($styleH, "A" . ($rowHonor + 1) . ":H" . ($rowHonor + $workerCount - 1));
            }
            $grandTotalWeek = 0;
            $jmlHadir = count($dates);
            $dataHonor = [];

            foreach ($workerCache as $idx => $w) {
                $totalHonor = $jmlHadir * $w['wage'];
                $grandTotalWeek += $totalHonor;

                $dataHonor[] = [
                    $idx + 1, $w['name'], $w['role'], $jmlHadir, $w['wage'], $totalHonor
                ];

                $r = $rowHonor + $idx;
                $colTtd = (($idx + 1) % 2 !== 0) ? 'G' : 'H';
                $sheetHonor->setCellValue($colTtd . $r, $idx + 1);
                $sheetHonor->getStyle($colTtd . $r)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            }

            $sheetHonor->fromArray($dataHonor, null, 'A' . $rowHonor);
            $sheetHonor->getStyle("B{$rowHonor}:C" . ($rowHonor + $workerCount - 1))
                       ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            $lastWorkerRow = $rowHonor + $workerCount - 1;
            $sheetHonor->getStyle("E{$rowHonor}:F{$lastWorkerRow}")
                       ->getNumberFormat()
                       ->setFormatCode('#,##0');

            $rowJumlah = $rowHonor + $workerCount;

            $sheetHonor->setCellValue('A' . $rowJumlah, 'JUMLAH');
            $sheetHonor->setCellValue('F' . $rowJumlah, $grandTotalWeek);
            $sheetHonor->getStyle("F{$rowJumlah}")
                       ->getNumberFormat()
                       ->setFormatCode('#,##0');
            $totalSemuaHonor += $grandTotalWeek;
            $this->generateFooter($sheetHonor, $rowJumlah + 1, $tglAkhirFull, $project, true);
            $mingguKe++;
        }

        $sheetRealisasi = clone $templateRealisasi;
        $sheetRealisasi->setTitle('Realisasi');
        
        $spreadsheet->addSheet($sheetRealisasi);

        $sheetRealisasi->setCellValue('C8',  'Bendahara');
        $sheetRealisasi->setCellValue('C12', $project->treasurer_name ?? 'Gunarni Sugeng Saputra');
        $sheetRealisasi->setCellValue('C13', 'NIP. ' . ($project->treasurer_nip ?? '19750519 200701 1 009'));
        $sheetRealisasi->getStyle('C12')->getFont()->setBold(true)->setUnderline(true);

        $subtotalM = $project->items->sum(function($item) {
            return $item->unit_price * $item->quantity;
        });

        $subtotalL = $totalSemuaHonor;

        $rowReal = 4; 
        $styleReal = $sheetRealisasi->getStyle("A{$rowReal}:C{$rowReal}");
        $sheetRealisasi->duplicateStyle($styleReal, "A".($rowReal+1).":C".($rowReal+2));

        $sheetRealisasi->setCellValue('A' . $rowReal, 1);
        $sheetRealisasi->setCellValue('B' . $rowReal, 'Pengadaan Bahan / Material ' . ($project->contractor->company_name ?? 'TB. Sukses'));
        $sheetRealisasi->setCellValue('D' . $rowReal, $subtotalM);
        
        $rowReal++;
        $sheetRealisasi->setCellValue('A' . $rowReal, 2);
        $sheetRealisasi->setCellValue('B' . $rowReal, 'Membayar Upah Tenaga/Tukang Pekerjaan ' . $project->project_name);
        $sheetRealisasi->setCellValue('D' . $rowReal, $subtotalL);
        
        $sheetRealisasi->getStyle("B{$rowReal}:D{$rowReal}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $rowReal++;
        $sheetRealisasi->setCellValue('B' . $rowReal, 'JUMLAH');
        $sheetRealisasi->setCellValue('D' . $rowReal, $subtotalM + $subtotalL);
        $sheetRealisasi->getStyle("B{$rowReal}:D{$rowReal}")->getFont()->setBold(true);

        $sheetAlamat = clone $templateAlamat;
        $sheetAlamat->setTitle('Alamat Pekerja');
        
        $spreadsheet->addSheet($sheetAlamat);
        $namaKontraktor = strtoupper($project->contractor->company_name ?? 'TB. SUKSES');
        $sheetAlamat->setCellValue('A1', 'NAMA PEKERJA ' . $namaKontraktor);

        $sheetAlamat->mergeCells('A1:E1');
        $sheetAlamat->getStyle('A1:E1')->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $rowAlamat = 4; 
        
        $sheetAlamat->removeRow($rowAlamat + 1, 3); 

        if ($workerCount > 1) {
            $sheetAlamat->insertNewRowBefore($rowAlamat + 1, $workerCount - 1);
            $styleAlamat = $sheetAlamat->getStyle("A{$rowAlamat}:E{$rowAlamat}");
            $sheetAlamat->duplicateStyle($styleAlamat, "A" . ($rowAlamat + 1) . ":E" . ($rowAlamat + $workerCount - 1));
        }

        $dataAlamat = [];
        foreach ($workerCache as $idx => $w) {
            $dataAlamat[] = [$idx + 1, $w['name'], $w['role'], $w['address'], $w['phone']];
        }

        $sheetAlamat->fromArray($dataAlamat, null, 'A' . $rowAlamat);
        $sheetAlamat->getStyle("B{$rowAlamat}:E" . ($rowAlamat + $workerCount - 1))
                    ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $masterNames = ['Absensi', 'Honor', 'Realisasi Template', 'Alamat Pekerja Template'];
        foreach ($masterNames as $mName) {
            $mSheet = $spreadsheet->getSheetByName($mName);
            if ($mSheet) {
                $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($mSheet));
            }
        }

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $project->project_name);
        $fileName  = 'Absensi_' . $safeName . '.xlsx';

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFile = $tempDir . '/' . $fileName;
        $writer   = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(false); 
        $writer->save($tempFile);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();

        return response()->download($tempFile, $fileName, [
            'Content-Type'     => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Encoding' => 'identity',
            'Cache-Control'    => 'no-store, no-transform',
        ])->deleteFileAfterSend(true);
    }

    private function generateFooter($sheet, $startRow, $tglFull, $project, $isHonor = false)
    {
        for ($r = $startRow; $r <= $startRow + 20; $r++) {
            $sheet->setCellValue('B' . $r, null);
            $sheet->setCellValue('C' . $r, null);
            $sheet->setCellValue('D' . $r, null);
            $sheet->setCellValue('E' . $r, null);
            $sheet->setCellValue('F' . $r, null);
            $sheet->setCellValue('G' . $r, null);
        }

        $rowTgl = $startRow + 2;

        if ($isHonor) {
            $sheet->setCellValue('G' . $rowTgl, "Karanganyar, $tglFull"); 
            $rowJabatan = $rowTgl + 1;
            
            $sheet->setCellValue('B' . $rowJabatan, "Bendahara"); 
            $rowNamaBendahara = $rowJabatan + 4;
            $sheet->setCellValue('B' . $rowNamaBendahara, $project->treasurer_name ?? 'Gunarni Sugeng Saputra');
            $sheet->setCellValue('B' . ($rowNamaBendahara+1), "NIP. " . ($project->treasurer_nip ?? '19750519 200701 1 009'));
            $sheet->getStyle("B{$rowNamaBendahara}")->getFont()->setBold(true)->setUnderline(true);

            $sheet->setCellValue('G' . $rowJabatan, "Waka Sarpras");
            $sheet->setCellValue('G' . $rowNamaBendahara, $project->facilities_head_name ?? 'Sukasno, S.Pd');
            $sheet->setCellValue('G' . ($rowNamaBendahara+1), "NIP. " . ($project->facilities_head_nip ?? '19810617 200801 1 001'));
            $sheet->getStyle("G{$rowNamaBendahara}")->getFont()->setBold(true)->setUnderline(true);

            $rowMengetahui = $rowNamaBendahara + 3; 
            
            $sheet->mergeCells("C{$rowMengetahui}:F{$rowMengetahui}");
            $sheet->setCellValue('C' . $rowMengetahui, "Mengetahui,");
            $sheet->getStyle("C{$rowMengetahui}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $sheet->mergeCells("C".($rowMengetahui+1).":F".($rowMengetahui+1));
            $sheet->setCellValue('C' . ($rowMengetahui+1), "Kepala SMK Negeri 2 Karanganyar");
            $sheet->getStyle("C".($rowMengetahui+1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $rowKepsek = $rowMengetahui + 5;
            $sheet->mergeCells("C{$rowKepsek}:F{$rowKepsek}");
            $sheet->setCellValue('C' . $rowKepsek, $project->principal_name ?? 'Sukidi, S.Pd., M.Pd.');
            
            $sheet->mergeCells("C".($rowKepsek+1).":F".($rowKepsek+1));
            $sheet->setCellValue('C' . ($rowKepsek+1), "NIP. " . ($project->principal_nip ?? '197003101997031004'));
            
            $sheet->getStyle("C{$rowKepsek}")->getFont()->setBold(true)->setUnderline(true);
            $sheet->getStyle("C{$rowKepsek}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C".($rowKepsek+1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        } else {
            $sheet->setCellValue('F' . $rowTgl, "Karanganyar, $tglFull");
            $rowJabatan = $rowTgl + 1;
            
            $sheet->setCellValue('B' . $rowJabatan, "Kepala SMK Negeri 2 Karanganyar");
            $sheet->setCellValue('F' . $rowJabatan, "Waka Sarpras");

            $rowNama = $rowJabatan + 4;
            $sheet->setCellValue('B' . $rowNama, $project->principal_name ?? 'Sukidi, S.Pd., M.Pd.');
            $sheet->setCellValue('B' . ($rowNama+1), "NIP. " . ($project->principal_nip ?? '197003101997031004'));
            
            $sheet->setCellValue('F' . $rowNama, $project->facilities_head_name ?? 'Sukasno, S.Pd');
            $sheet->setCellValue('F' . ($rowNama+1), "NIP. " . ($project->facilities_head_nip ?? '19810617 200801 1 001'));
            
            $sheet->getStyle("B{$rowNama}")->getFont()->setBold(true)->setUnderline(true);
            $sheet->getStyle("F{$rowNama}")->getFont()->setBold(true)->setUnderline(true);
        }
    }
    

}
