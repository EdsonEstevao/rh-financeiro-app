<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{Auth, DB, Hash, Log};
use Spatie\Permission\Models\Role;

use App\Http\Controllers\Controller;
use App\Models\User;
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);

        // $this->authorizeResource(User::class, 'user');
        // Autorização granular por ação
        $this->middleware('permission:admin.users.view')->only(['index', 'show']);
        $this->middleware('permission:admin.users.create')->only(['create', 'store']);
        $this->middleware('permission:admin.users.edit')->only(['edit', 'update']);

    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $users = User::with('roles')
            ->withCount(['employee', 'boletos'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('cpf', 'like', "%{$search}%");
                });
            })
            ->when($request->role, function ($query, $role) {
                $query->role($role);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            })
            ->when($request->sort, function ($query, $sort) {
                match ($sort) {
                    'oldest' => $query->oldest(),
                    'name' => $query->orderBy('name'),
                    'email' => $query->orderBy('email'),
                    default => $query->latest()
                };
            }, function ($query) {
                $query->latest();
            })
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'cpf' => ['nullable', 'string', 'max:14', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'cpf' => $validated['cpf'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'email_verified_at' => $request->boolean('email_verified') ? now() : null,
                'is_active' => true,
            ]);

            $user->assignRole($validated['role']);

            DB::commit();

            Log::info('User created', ['admin_id' => Auth::id(), 'user_id' => $user->id]);

            return redirect()
                ->route('admin.users.index')
                ->with('success', "Usuário {$user->name} criado com sucesso!");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao criar usuário. Por favor, tente novamente.');
        }
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();

        $user->load('roles', 'employee');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'cpf' => ['nullable', 'string', 'max:14', 'unique:users,cpf,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['required', 'boolean'],
        ]);

        try {
            DB::beginTransaction();

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'cpf' => $validated['cpf'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'is_active' => (bool) $validated['is_active'],
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($validated['password']);
                $userData['password_changed_at'] = now();
            }

            $user->update($userData);

            // Sync roles
            $user->syncRoles([$validated['role']]);

            DB::commit();

            Log::info('User updated', ['admin_id' => Auth::id(), 'user_id' => $user->id]);

            return redirect()
                ->route('admin.users.index')
                ->with('success', "Usuário {$user->name} atualizado com sucesso!");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update user', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
                'user_id' => $user->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar usuário. Por favor, tente novamente.');
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            // Prevent self-deletion
            if ($user->id === Auth::id()) {
                return back()->with('error', 'Você não pode excluir seu próprio usuário.');
            }

            $userName = $user->name;

            DB::beginTransaction();

            // Soft delete related records if needed
            if ($user->employee) {
                $user->employee->delete();
            }

            $user->delete();

            DB::commit();

            Log::warning('User deleted', ['admin_id' => Auth::id(), 'user_id' => $user->id]);

            return redirect()
                ->route('admin.users.index')
                ->with('success', "Usuário {$userName} excluído com sucesso!");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete user', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
                'user_id' => $user->id
            ]);

            return back()->with('error', 'Erro ao excluir usuário. Por favor, tente novamente.');
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        try {
            // Prevent self-deactivation
            if ($user->id === Auth::id()) {
                return back()->with('error', 'Você não pode alterar seu próprio status.');
            }

            $user->update([
                'is_active' => !$user->is_active
            ]);

            $status = $user->is_active ? 'ativado' : 'desativado';

            Log::info('User status toggled', [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'status' => $status
            ]);

            return back()->with('success', "Usuário {$user->name} {$status} com sucesso!");

        } catch (\Exception $e) {
            Log::error('Failed to toggle user status', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
                'user_id' => $user->id
            ]);

            return back()->with('error', 'Erro ao alterar status do usuário.');
        }
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore(int $userId): RedirectResponse
    {
        try {
            $user = User::withTrashed()->findOrFail($userId);

            DB::beginTransaction();

            $user->restore();

            if ($user->employee) {
                $user->employee()->withTrashed()->restore();
            }

            DB::commit();

            Log::info('User restored', ['admin_id' => Auth::id(), 'user_id' => $user->id]);

            return back()->with('success', "Usuário {$user->name} restaurado com sucesso!");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to restore user', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
                'user_id' => $userId
            ]);

            return back()->with('error', 'Erro ao restaurar usuário.');
        }
    }

    /**
     * Assign role to user via API.
     */
    public function assignRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        try {
            $user->syncRoles([$validated['role']]);

            return back()->with('success', "Perfil atualizado para {$validated['role']}.");

        } catch (\Exception $e) {
            Log::error('Failed to assign role', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
                'user_id' => $user->id
            ]);

            return back()->with('error', 'Erro ao atribuir perfil.');
        }
    }
}