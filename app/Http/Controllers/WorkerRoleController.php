<?php

namespace App\Http\Controllers;

use App\Models\WorkerRole;
use Illuminate\Http\Request;

class WorkerRoleController extends Controller
{
    public function index()
    {
        $roles = WorkerRole::all();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:255|unique:worker_roles',
            'default_wage' => 'required|numeric|min:0',
        ]);

        WorkerRole::create($request->all());

        return redirect()->route('roles.index')->with('success', 'Role berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $role = WorkerRole::findOrFail($id);
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, $id)
    {
        $role = WorkerRole::findOrFail($id);
        $request->validate([
            'role_name' => 'required|string|max:255|unique:worker_roles,role_name,' . $role->id,
            'default_wage' => 'required|numeric|min:0',
        ]);

        $role->update($request->all());
        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $role = WorkerRole::findOrFail($id);
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus!');
    }
}