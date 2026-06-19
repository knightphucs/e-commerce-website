<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::with('permissions')->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('users.create', compact('permissions'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create($request->safe()->except('permissions'));
        $user->permissions()->sync($request->validated('permissions', []));

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $permissions = Permission::orderBy('group')->orderBy('key')->get()->groupBy('group');
        $user->load('permissions');

        return view('users.edit', compact('user', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->safe()->except('permissions'));
        $user->permissions()->sync($request->validated('permissions', []));

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }
}
