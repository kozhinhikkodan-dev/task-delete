<?php

namespace App\Http\Controllers;

use App\DataTables\RolesDataTable;
use App\Http\Requests\RoleRequest;
use App\Http\Traits\DataTable;
use App\Models\Role;
use DB;
use Illuminate\Http\Request;

class RoleController extends Controller
{

    use DataTable;

    public function __construct()
    {
        $this->authorizeResource(Role::class);
    }

    public function index(Request $request, RolesDataTable $dataTable)
    {
        return $this->renderDataTable($request, $dataTable, 'roles.index');
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('roles.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        DB::transaction(function () use ($request) {
            // Create the role
            $role = Role::create($request->getData());

            // Assign permissions
            if ($request->filled('permissions')) {
                $role->syncPermissions($request->permissions);
            }


        });

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        return view('roles.form', compact('role'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, Role $role)
    {
        DB::transaction(function () use ($request, $role) {
            // update the role
            $role->update($request->getData());

            // Assign permissions
            if ($request->filled('permissions')) {
                $permissions = $request->permissions;
                info($permissions);
                // 1. Sync to role
                $role->syncPermissions($permissions);

                // 2. Sync to all users who have this role
                foreach ($role->users as $user) {
                    info($user->name);
                    // First, clear all direct permissions
        $user->syncPermissions([]); // clears everything
                    $user->syncPermissions($permissions); // will overwrite direct permissions
                }
            }


        });

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role, Request $request)
    {
        $systemRoles = ['Administrator', 'Supplier', 'Tailor'];

        if (in_array($role->name, $systemRoles)) {
            return redirect()->route('roles.index')->with('error', 'Cannot delete system role.');
        }

        DB::transaction(function () use ($role) {
            $role->permissions()->detach();
            $role->delete();
        });

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

}
