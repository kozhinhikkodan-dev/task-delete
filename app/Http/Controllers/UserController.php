<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\Http\Requests\PasswordChangeRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Traits\DataTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use DataTable;

    public function __construct()
    {
        $this->authorizeResource(User::class);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, UsersDataTable $dataTable)
    {
        return $this->renderDataTable($request, $dataTable, 'users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        DB::transaction(function () use ($request) {
            // Create the user
            $user = User::create($request->getData());

            // Assign role
            $user->assignRole($request->role_name);
        });

        return redirect()->route('users.index')->with('success', 'User created successfully.');
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
    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        DB::transaction(function () use ($request, $user) {
            // Update the user
            $data = $request->getData();

            // Remove password if it's empty (not being updated)
            if (empty($data['password'])) {
                unset($data['password']);
            }

            $user->update($data);

            // Update role assignment
            $user->roles()->detach();
            $user->assignRole($request->role_name);
        });

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, Request $request)
    {
        DB::transaction(function () use ($user) {
            $user->delete();
        });

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function changePasswordPage()
    {
        return view('users.change-password');
    }

    public function changePassword(PasswordChangeRequest $request)
    {
         DB::transaction(function () use ($request) {
            $user = auth()->user();
            $user->update([
                'password' => bcrypt($request->password),
            ]);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        });

        return redirect(route('login'))->with('success', 'Password changed successfully.');
    }
}
