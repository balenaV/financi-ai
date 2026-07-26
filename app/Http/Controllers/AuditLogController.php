<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        return view('profile.audit-log', [
            'logs' => $request->user()->auditLogs()->latest()->paginate(30),
        ]);
    }
}
