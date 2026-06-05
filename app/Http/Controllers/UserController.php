<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
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

    public function create()
    {
        $roles = ['pemilik' => 'Pemilik Toko', 'admin' => 'Admin Operasional', 'kasir' => 'Kasir'];

        return view('users.create', compact('roles'));
    }

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
        $data['visible_password'] = $plainPassword;
        $data['is_active'] = $request->boolean('is_active', true);

        $user = User::create($data);

        ActivityLog::record('CREATE_USER', "Menambahkan pengguna: {$user->name} (Role: {$user->role})");

        return redirect()->route('users.index')
            ->with('success', "Pengguna \"{$user->name}\" berhasil ditambahkan.");
    }

    public function edit(User $user)
    {
        $roles = ['pemilik' => 'Pemilik Toko', 'admin' => 'Admin Operasional', 'kasir' => 'Kasir'];

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
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
            $data['visible_password'] = $request->password;
        }

        $requestedActive = $request->boolean('is_active', true);

        if (($user->role === 'pemilik' || $data['role'] === 'pemilik') && ! $requestedActive) {
            return back()->withInput()->with('error', 'Akun owner/pemilik tidak boleh dinonaktifkan.');
        }

        if ($user->id === auth()->id() && ! $requestedActive) {
            return back()->withInput()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $data['is_active'] = $requestedActive;
        $user->update($data);

        ActivityLog::record('UPDATE_USER', "Memperbarui pengguna: {$user->name} (ID: {$user->id}) — Status: " . ($user->is_active ? 'aktif' : 'nonaktif'));

        return redirect()->route('users.index')
            ->with('success', "Pengguna \"{$user->name}\" berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->role === 'pemilik') {
            return back()->with('error', 'Akun owner/pemilik tidak boleh dihapus.');
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
