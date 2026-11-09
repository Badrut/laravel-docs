<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use Illuminate\Http\Request;

class ContractorController extends Controller
{
    public function index()
    {
        $contractors = Contractor::latest()->get();
        return view('contractors.index', compact('contractors'));
    }

    public function create()
    {
        return view('contractors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name'     => 'required|string|max:255',
            'person_in_charge' => 'nullable|string|max:255',
            'address'          => 'nullable|string',
            'phone'            => 'nullable|string|max:20',
        ]);

        Contractor::create($request->all());
        return redirect()->route('contractors.index')->with('success', 'Kontraktor berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $contractor = Contractor::findOrFail($id);
        return view('contractors.edit', compact('contractor'));
    }

    public function update(Request $request, $id)
    {
        $contractor = Contractor::findOrFail($id);
        $request->validate([
            'company_name'     => 'required|string|max:255',
            'person_in_charge' => 'nullable|string|max:255',
            'address'          => 'nullable|string',
            'phone'            => 'nullable|string|max:20',
        ]);

        $contractor->update($request->all());
        return redirect()->route('contractors.index')->with('success', 'Data kontraktor berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $contractor = Contractor::findOrFail($id);
        $contractor->delete();
        return redirect()->route('contractors.index')->with('success', 'Kontraktor berhasil dihapus!');
    }
}
