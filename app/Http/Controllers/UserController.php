<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua data user, urutkan dari yang terbaru
        $users = User::latest()->get(); 
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Tampilkan halaman form tambah user
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi inputan dari form
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // 2. Simpan ke database
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), // Wajib di-hash
        ]);

        // 3. Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Biasanya dipakai untuk nampilin detail 1 user (opsional, bisa dibiarkan kosong dulu)
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Cari user berdasarkan ID, kalau nggak ketemu bakal otomatis error 404
        $user = User::findOrFail($id);
        
        // Tampilkan halaman edit bawa data usernya
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        // 1. Validasi inputan (Email boleh sama dengan email dia yang sekarang)
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8', // Password opsional pas update
        ]);

        // 2. Siapkan data yang mau diupdate
        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        // 3. Cek kalau user ngisi password baru, berarti diupdate. Kalau kosong, biarin password lama.
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // 4. Lakukan update
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data User berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus!');
    }
}