<?php

namespace App\Http\Controllers;

use App\Models\KopSurat;
use Illuminate\Http\Request;

class KopSuratController extends Controller
{
    public function index()
    {
        $kopSekolah = KopSurat::sekolah()->latest()->get();
        $kopKontraktor = KopSurat::kontraktor()->latest()->get();
        return view('kop-surat.index', compact('kopSekolah', 'kopKontraktor'));
    }

    public function create()
    {
        return view('kop-surat.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:sekolah,kontraktor',
            'image'      => 'required|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        $path = $request->file('image')->store('kop_surats', 'public');

        KopSurat::create([
            'name'       => $request->name,
            'type'       => $request->type,
            'image_path' => $path,
        ]);

        return redirect()->route('kop-surat.index')->with('success', 'Kop surat berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $kopSurat = KopSurat::findOrFail($id);
        return view('kop-surat.edit', compact('kopSurat'));
    }

    public function update(Request $request, $id)
    {
        $kopSurat = KopSurat::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'type'  => 'required|in:sekolah,kontraktor',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        $data = [
            'name' => $request->name,
            'type' => $request->type,
        ];

        if ($request->hasFile('image')) {
            if ($kopSurat->image_path && \Storage::disk('public')->exists($kopSurat->image_path)) {
                \Storage::disk('public')->delete($kopSurat->image_path);
            }
            $data['image_path'] = $request->file('image')->store('kop_surats', 'public');
        }

        $kopSurat->update($data);
        return redirect()->route('kop-surat.index')->with('success', 'Kop surat berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $kopSurat = KopSurat::findOrFail($id);
        if ($kopSurat->image_path && \Storage::disk('public')->exists($kopSurat->image_path)) {
            \Storage::disk('public')->delete($kopSurat->image_path);
        }
        $kopSurat->delete();
        return redirect()->route('kop-surat.index')->with('success', 'Kop surat berhasil dihapus!');
    }
}
