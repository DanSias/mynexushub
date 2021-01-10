<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\UserRole;

class UserRoleController extends Controller
{
    public $email;

    public function index()
    {
        $user = auth()->user();
        $role = UserRole::where('user_id', $user->id)
            ->with('user')
            ->first();
        $role->location = json_decode($role->location);
        $role->bu = json_decode($role->bu);
        $role->channel = json_decode($role->channel);

        return $role;
    }

    public function store($email)
    {
        $this->email = $email;
        $role = $this->validateRequest();
        $store = UserRole::updateOrCreate(['user_id' => $role['user_id']], $role);

        Inertia::share('ok');
        // return 'Role Updated';
        // return $store;
    }

    public function validateRequest()
    {
        $role = request()->validate([
            'id' => '',
            'user_id' => '',
            'team' => '',
            'title' => '',
            'location' => '',
            'bu' => '',
            'channel' => '',
            'extension' => ''
        ]);
        $user = User::where('email', $this->email)->first();
        $role['user_id'] = $user->id;
        $role['location'] = json_encode($role['location']);
        return $role;
    }

}
