<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Menampilkan daftar pengguna (karyawan/admin/pemilik) dengan fitur pencarian, filter, dan pengurutan
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $allowedSorts = ['name', 'username', 'email', 'role', 'is_active', 'last_seen_at', 'created_at'];
        $sort = in_array($request->get('sort'), $allowedSorts, true) ? $request->get('sort') : 'name';
        $dir = $request->get('dir') === 'desc' ? 'desc' : 'asc';

        $perPage = in_array((int) $request->get('per_page'), [10,15,20,50,100], true) ? (int) $request->get('per_page') : 15;
        $users = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();
        $roles = ['pemilik' => 'Pemilik Toko', 'admin' => 'Admin Operasional', 'kasir' => 'Kasir'];

        return view('users.index', compact('users', 'roles', 'sort', 'dir'));
    }

    // Menampilkan form untuk menambahkan pengguna baru
    public function create()
    {
        $roles = ['pemilik' => 'Pemilik Toko', 'admin' => 'Admin Operasional', 'kasir' => 'Kasir'];

        return view('users.create', compact('roles'));
    }

    // Menyimpan data pengguna baru ke database
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:150',
            'username'  => 'required|string|max:50|unique:users,username',
            'email'     => 'nullable|email|max:150',
            'password'  => 'required|string|min:6|confirmed',
            'role'      => 'required|in:pemilik,admin,kasir',
            'is_active' => 'boolean',
        ]);

        $plainPassword = $data['password'];
        $data['password']  = Hash::make($plainPassword);
        $data['is_active'] = $request->has('is_active');
        
        $hasMainOwner = User::where('is_main_owner', true)->exists();
        if (!$hasMainOwner || auth()->user()->is_main_owner) {
            $data['is_main_owner'] = $data['role'] === 'pemilik' ? $request->boolean('is_main_owner', false) : false;
        } else {
            $data['is_main_owner'] = false;
        }

        $user = User::create($data);

        if ($user->is_main_owner) {
            User::where('id', '!=', $user->id)->update(['is_main_owner' => false]);
        }

        ActivityLog::record('CREATE_USER', "Menambahkan pengguna: {$user->name} (Role: {$user->role})");

        return redirect()->route('users.index')
            ->with('success', "Pengguna \"{$user->name}\" berhasil ditambahkan.");
    }

    // Menampilkan form edit pengguna beserta pembatasan hak akses
    public function edit(User $user)
    {
        if ($user->is_main_owner && !auth()->user()->is_main_owner && $user->id !== auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak memiliki hak akses untuk mengedit akun Pemilik Toko Utama.');
        }

        $roles = ['pemilik' => 'Pemilik Toko', 'admin' => 'Admin Operasional', 'kasir' => 'Kasir'];

        return view('users.edit', compact('user', 'roles'));
    }

    // Memperbarui data pengguna, termasuk validasi role dan penggantian kepemilikan utama (main_owner)
    public function update(Request $request, User $user)
    {
        if ($user->is_main_owner && !auth()->user()->is_main_owner && $user->id !== auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak memiliki hak akses untuk mengedit akun Pemilik Toko Utama.');
        }

        $data = $request->validate([
            'name'      => 'required|string|max:150',
            'username'  => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'     => 'nullable|email|max:150',
            'role'      => 'required|in:pemilik,admin,kasir',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $canDisable = $user->id !== auth()->id() && ($user->role !== 'pemilik' || (auth()->user()->is_main_owner && !$user->is_main_owner));
        
        if ($canDisable) {
            $data['is_active'] = $request->has('is_active');
        } else {
            $data['is_active'] = $user->is_active;
        }
        
        $hasMainOwner = User::where('is_main_owner', true)->exists();
        if (!$hasMainOwner || auth()->user()->is_main_owner) {
            $data['is_main_owner'] = $data['role'] === 'pemilik' ? $request->boolean('is_main_owner', false) : false;
        }
        // if they don't have permission, it simply doesn't overwrite $user->is_main_owner

        $user->update($data);

        if ($user->is_main_owner) {
            User::where('id', '!=', $user->id)->update(['is_main_owner' => false]);
        }

        ActivityLog::record('UPDATE_USER', "Memperbarui pengguna: {$user->name} (ID: {$user->id}) — Status: " . ($user->is_active ? 'aktif' : 'nonaktif'));

        return redirect()->route('users.index')
            ->with('success', "Pengguna \"{$user->name}\" berhasil diperbarui.");
    }

    // Menghapus akun pengguna (hanya jika memenuhi syarat dan belum pernah bertransaksi)
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->is_main_owner) {
            return back()->with('error', 'Akun Pemilik Toko Utama tidak boleh dihapus.');
        }

        if ($user->role === 'pemilik' && !auth()->user()->is_main_owner) {
            return back()->with('error', 'Hanya Pemilik Toko Utama yang bisa menghapus Pemilik Toko lainnya.');
        }

        if ($user->transactions()->exists()) {
            return back()->with('error', 'Pengguna tidak dapat dihapus karena memiliki riwayat transaksi. Nonaktifkan akun jika sudah tidak digunakan.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record('DELETE_USER', "Menghapus pengguna: {$name}");

        return redirect()->route('users.index')
            ->with('success', "Pengguna \"{$name}\" berhasil dihapus.");
    }
}
