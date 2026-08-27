<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        $logs = AuditLog::with('user')->latest('created_at')->paginate(50);
        return view('audit.index', compact('logs'));
    }
}
