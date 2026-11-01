<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $logs = $user->logs()->latest()->paginate(25);

        return view('logs.index', compact('user', 'logs'));
    }
}
