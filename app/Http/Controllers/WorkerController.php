<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\WorkerRole;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function index()
    {
        $workers = Worker::with('role')->latest()->get();
        return view('workers.index', compact('workers'));
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
}