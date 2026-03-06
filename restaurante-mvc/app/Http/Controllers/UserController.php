<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Notifications\TemporaryPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('branch');

        // Filter by branch for managers
        if (auth()->user()->isManager()) {
            $query->where('branch_id', auth()->user()->branch_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $branches = Branch::active()->orderBy('name')->get();

        return view('users.index', compact('users', 'branches'));
    }

    public function create()
    {
        $branches = Branch::active()->orderBy('name')->get();
        return view('users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:admin,manager,employee',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        // Only admins can create other admins
        if ($validated['role'] === 'admin' && !auth()->user()->isAdmin()) {
            abort(403, 'No tienes permiso para crear administradores.');
        }

        // Managers and employees must have a branch assigned
        if (in_array($validated['role'], ['manager', 'employee']) && empty($validated['branch_id'])) {
            return back()->with('error', 'Los gerentes y empleados deben tener una sucursal asignada.');
        }

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function show(User $user)
    {
        $user->load('branch');
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $branches = Branch::active()->orderBy('name')->get();
        return view('users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'     => 'required|in:admin,manager,employee',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        // Prevent removing own admin role
        if ($user->id === auth()->id() && $user->isAdmin() && $validated['role'] !== 'admin') {
            return back()->with('error', 'No puedes cambiar tu propio rol de administrador.');
        }

        // Managers and employees must have a branch assigned
        if (in_array($validated['role'], ['manager', 'employee']) && empty($validated['branch_id'])) {
            return back()->with('error', 'Los gerentes y empleados deben tener una sucursal asignada.');
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }

    public function resetPassword(Request $request, User $user)
    {
        // Only admin can reset passwords
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'No tienes permiso para realizar esta acción.');
        }

        // Prevent resetting own password
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes resetear tu propia contraseña desde aquí.');
        }

        // Generate new random password (10 chars with letters and numbers)
        $newPassword = Str::random(10);

        // Update user password and force change
        $user->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => true,
        ]);

        // Notify user by email
        $user->notify(new TemporaryPasswordNotification($newPassword));

        return back()->with('success', "Contraseña reseteada. Se notificó a {$user->name} por correo.");
    }
}
