<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dashboard;

class HomeController extends Controller
{

    public function index()
    {
        $user = auth()->user();

        $dashboards = Dashboard::active()
            ->when(!$user->isAdmin() && !$user->isSuperAdmin(), function ($query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->get();

        return view('home', compact('dashboards'));
    }
}
