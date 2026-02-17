<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\AuditLogRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function __construct(
        protected AuditLogRepository $auditLogRepository
    ) {}

    /**
     * Display audit logs list
     */
    public function index(Request $request)
    {
        $filters = $request->only(['action', 'user_id', 'auditable_type', 'date_from', 'date_to', 'search']);

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $this->auditLogRepository->getFilteredPaginated($filters),
            'filters' => $filters,
            'actions' => $this->auditLogRepository->getDistinctActions(),
            'types' => $this->auditLogRepository->getDistinctTypes(),
            'users' => User::whereHas('auditLogs')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
