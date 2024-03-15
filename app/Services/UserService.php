<?php

namespace App\Services;

use App\Http\Traits\CatalogsTrait;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserService
{
    use CatalogsTrait;

    public function index(Request $request): array
    {
        $users = User::query();

        if (Auth::user()->hasRole('супервизер') && !Auth::user()->hasAnyRole(['администратор', 'диспетчер', 'кладовщик'])) {
            $departments = Auth::user()->getDepartmentAttribute();
            $users->role('техник')
                ->where('department_id', $departments->id);

            $locations = Auth::user()->locations()->first();
            if ($locations) {
                $users->whereHas('locations', function ($query) use ($locations) {
                    $query->where('id', $locations->id);
                });
            }
        } else {
            if ($request->filled('department_id') && $request->department_id !== 'all') {
                if ($request->filled('role') && $request->role !== 'all') {
                    $role = Role::find($request->role);
                    $users->role($role->name)
                        ->where('department_id', $request->department_id);
                } else {
                    $users->where('department_id', $request->department_id);
                }

                if ($request->filled('location_id')) {
                    $users->whereHas('locations', function ($query) use ($request) {
                        $query->where('id', $request->location_id);
                    });
                }
            } else {
                if ($request->filled('role') && $request->role !== 'all') {
                    $role = Role::find($request->role);
                    $users->role($role->name);
                }
            }
        }

        $users = $users->paginate(20);

        $departments = $this->getDepartments();
        $locations = $request->filled('department_id') ? $this->getLocations($request->department_id) : null;
        $roles = Role::all();

        foreach ($users as &$user) {
            $user->roles_html = $user->roles->pluck('name')->implode('<br>');
            $user->locations_text = $user->locations->pluck('v_name')->implode('<br>');
        }

        return compact('users', 'departments', 'locations', 'roles');
    }

    public function show($id): array
    {
        $user = User::find($id);
        $roles_arr = array();
        foreach ($user->roles as $role) {
            $roles_arr[] = $role->name;
        }
        $user->roles_html = mb_ucfirst(implode(', ', $roles_arr));
        $departments = $this->getDepartments();
        $locations = null;
        if ($user->department) {
            $locations = $this->getLocations($user->department->id);
        }

        return compact(['user', 'departments', 'locations', 'roles_arr']);
    }

    public function update(Request $request) {
        $user = User::find($request->id);
        $user->department_id = (int)$request->department;
        $user->locations()->sync($request->location);
        $user->departments()->sync($request->departments);
        $user->save();
    }
}

