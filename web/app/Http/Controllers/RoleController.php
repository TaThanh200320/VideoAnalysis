<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Middleware\PermissionMiddleware;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('view role'), only: ['index']),
            new Middleware(PermissionMiddleware::using('create role'), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using('update role'), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using('delete role'), only: ['destroy']),
        ];
    }

    public function index()
    {

        $roles = Cache::remember('roles', Carbon::now()->addMinutes(10), fn() => Role::all());
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Cache::remember('permissions', Carbon::now()->addMinutes(10), fn() => Permission::all());
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permission' => 'array'
        ]);

        $role = Role::create([
            'name' => $request->name
        ]);

        if ($request->has('permission')) {
            $role->syncPermissions($request->permission);
        }

        return redirect(url('/roles'))->with('status', 'Role Created Successfully with Permissions');
    }

    public function edit(Role $role)
    {
        $permissions = Cache::remember('permissions', Carbon::now()->addMinutes(10), fn() => Permission::all());
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $request->name]);
        $role->permissions()->sync($request->permissions);

        return redirect(url('/roles'))->with('success', 'Role updated successfully.');
    }

    public function destroy($roleId)
    {
        $role = Role::findOrFail($roleId);
        $role->delete();

        return redirect('/roles')->with('status', 'Role Delete Successfully');
    }
}
