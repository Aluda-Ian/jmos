<?php

namespace App\Services\Ai;

use App\Models\Expense;
use App\Models\FundraisingOpportunity;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Task;
use App\Models\User;

class AiAuthorizationService
{
    /**
     * Determine if a user is authorized to request AI reports.
     */
    public function canViewReports(User $user): bool
    {
        if ($user->role === 'owner') {
            return true;
        }

        return $user->hasPermission('dashboard.view')
            || $user->hasPermission('projects.view')
            || $user->hasPermission('finance.view');
    }

    /**
     * Determine if user has financial visibility in reports.
     */
    public function canViewFinancialReports(User $user): bool
    {
        if ($user->role === 'owner') {
            return true;
        }

        return $user->hasPermission('finance.view')
            || $user->hasPermission('dashboard.financials')
            || $user->hasPermission('invoices.manage');
    }

    /**
     * Determine if user has project visibility in reports.
     */
    public function canViewProjectReports(User $user): bool
    {
        if ($user->role === 'owner') {
            return true;
        }

        return $user->hasPermission('projects.view') || $user->hasPermission('dashboard.view');
    }

    /**
     * Determine if user can use lead-generation / fundraising chat mode.
     */
    public function canUseLeadGenChat(User $user): bool
    {
        if ($user->role === 'owner') {
            return true;
        }

        return $user->hasPermission('leads.view')
            || $user->hasPermission('leads.manage')
            || $user->hasPermission('deals.manage')
            || in_array($user->role, ['owner', 'manager', 'sales', 'business_development'], true);
    }

    /**
     * General help mode is accessible to all authenticated team members.
     */
    public function canUseGeneralHelpChat(User $user): bool
    {
        return true;
    }

    /**
     * Strictly scopes application data according to the requesting user's permissions.
     * Sensitive fields (revenue, expense numbers, margin) are STRIPPED at the data layer
     * if the user lacks financial permissions.
     */
    public function scopeDataForUser(User $user, string $reportType = 'executive_digest'): array
    {
        $hasFinance = $this->canViewFinancialReports($user);
        $hasProjects = $this->canViewProjectReports($user);
        $hasLeads = $user->role === 'owner' || $user->hasPermission('leads.view');

        $scopedData = [
            'user' => [
                'name' => $user->name,
                'role' => $user->role,
                'department' => $user->department,
            ],
            'permissions_applied' => [
                'financial_data_included' => $hasFinance,
                'project_data_included' => $hasProjects,
                'crm_data_included' => $hasLeads,
            ],
            'generated_at' => now()->toIso8601String(),
        ];

        // 1. Projects Data Scoping
        if ($hasProjects) {
            $projectsQuery = Project::latest('updated_at')->take(20);
            $projects = $projectsQuery->get()->map(function (Project $p) use ($hasFinance) {
                $item = [
                    'id' => $p->id,
                    'title' => $p->project_name ?? $p->title ?? 'Untitled Project',
                    'client' => $p->client,
                    'category' => $p->category,
                    'status' => $p->status,
                    'progress' => ($p->progress_pct ?? $p->progress ?? 0).'%',
                    'due_date' => $p->deadline ?? $p->due_date,
                    'tasks_count' => Task::where('project_id', $p->id)->count(),
                    'completed_tasks' => Task::where('project_id', $p->id)->where('status', 'Completed')->count(),
                ];

                // Only include budget numbers if authorized
                if ($hasFinance && $p->budget) {
                    $item['budget'] = (float) $p->budget;
                }

                return $item;
            });

            $scopedData['projects'] = [
                'total_active' => Project::whereNotIn('status', ['Completed', 'Cancelled', 'Archived'])->count(),
                'completed' => Project::where('status', 'Completed')->count(),
                'recent_projects' => $projects,
            ];
        }

        // 2. Financial Data Scoping (Completely omitted if unauthorized)
        if ($hasFinance && in_array($reportType, ['financial_summary', 'executive_digest'], true)) {
            $totalInvoiced = (float) Invoice::sum('amount');
            $paidInvoices = (float) Invoice::where('status', 'Paid')->sum('amount');
            $outstandingInvoices = (float) Invoice::where('status', '!=', 'Paid')->where('status', '!=', 'Cancelled')->sum('amount');
            $overdueInvoices = (float) Invoice::where('status', 'Overdue')->sum('amount');
            $totalExpenses = (float) Expense::sum('amount');
            $netOperatingCashflow = $paidInvoices - $totalExpenses;

            $recentInvoices = Invoice::latest('created_at')->take(10)->get(['invoice_no', 'client', 'amount', 'status', 'due_date']);
            $recentExpenses = Expense::latest('date')->take(10)->get(['name', 'category', 'amount', 'date']);
            $quotesSummary = [
                'approved' => Quote::where('status', 'Approved')->count(),
                'pending' => Quote::whereIn('status', ['Draft', 'Sent'])->count(),
                'total_quoted_pipeline' => (float) Quote::whereIn('status', ['Draft', 'Sent', 'Approved'])->sum('total_amount'),
            ];

            $scopedData['finance'] = [
                'total_revenue_collected' => $paidInvoices,
                'total_outstanding' => $outstandingInvoices,
                'total_overdue' => $overdueInvoices,
                'total_expenses' => $totalExpenses,
                'net_operating_cashflow' => $netOperatingCashflow,
                'quotes_pipeline' => $quotesSummary,
                'recent_invoices' => $recentInvoices,
                'recent_expenses' => $recentExpenses,
            ];
        }

        // 3. CRM & Lead Gen Data Scoping
        if ($hasLeads) {
            $scopedData['leads'] = [
                'total_leads' => Lead::count(),
                'active_pipeline_leads' => Lead::whereNotIn('status', ['Lost', 'Converted'])->count(),
                'converted_leads' => Lead::where('status', 'Converted')->count(),
                'active_opportunities_count' => FundraisingOpportunity::where('status', 'Active')->count(),
            ];
        }

        return $scopedData;
    }

    /**
     * Compute a cache key that is tied to user role and permission scope fingerprint.
     */
    public function getReportCacheKey(User $user, string $reportType): string
    {
        $roleSlug = $user->role;
        $hasFinance = $this->canViewFinancialReports($user) ? 'fin1' : 'fin0';
        $hasProjects = $this->canViewProjectReports($user) ? 'prj1' : 'prj0';

        return "ai_report_{$reportType}_{$user->id}_{$roleSlug}_{$hasFinance}_{$hasProjects}";
    }
}
