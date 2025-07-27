<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Customer;
use App\Models\Task;
use App\Models\TaskType;
use Carbon\Carbon;

class DashboardController extends Controller
{
    //

    public function index()
    {
        if (Auth::user()) {
            // Get dashboard metrics
            $metrics = $this->getDashboardMetrics();
            return view('dashboard.index', compact('metrics'));
        } else {
            return redirect('login');
        }
    }

    private function getDashboardMetrics()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            // Key Metrics
            'total_customers' => Customer::count(),
            'active_customers' => Customer::where('status', 'active')->count(),
            'total_tasks' => Task::count(),
            'pending_tasks' => Task::where('status', 'pending')->count(),
            'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
            'cancelled_tasks' => Task::where('status', 'cancelled')->count(),
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),

            // Revenue Metrics
            'total_revenue' => Task::where('status', 'completed')->sum('estimated_cost'),
            'monthly_revenue' => Task::where('status', 'completed')
                ->whereMonth('completed_at', Carbon::now()->month)
                ->sum('estimated_cost'),
            'pending_revenue' => Task::whereIn('status', ['pending', 'in_progress'])->sum('estimated_cost'),

            // Performance Metrics
            'avg_task_duration' => Task::where('status', 'completed')
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, started_at, completed_at)')),
            'completion_rate' => $this->getCompletionRate(),

            // Recent Activity
            'recent_tasks' => Task::with(['customer', 'taskType', 'assignedUser'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
            'recent_customers' => Customer::orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
            'due_today' => Task::whereDate('task_date', $today)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'overdue_tasks' => Task::whereDate('task_date', '<', $today)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),

            // Task Status Distribution
            'task_status_distribution' => Task::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),

            // Monthly Trends
            'monthly_task_trends' => $this->getMonthlyTaskTrends(),
            'monthly_revenue_trends' => $this->getMonthlyRevenueTrends(),

            // Top Performers
            'top_performers' => User::withCount(['assignedTasks' => function ($query) {
                $query->where('status', 'completed');
            }])
                ->orderBy('assigned_tasks_count', 'desc')
                ->take(5)
                ->get(),

            // Task Types Performance
            'task_types_performance' => TaskType::withCount(['tasks' => function ($query) {
                $query->where('status', 'completed');
            }])
                ->with(['tasks' => function ($query) {
                    $query->where('status', 'completed');
                }])
                ->orderBy('tasks_count', 'desc')
                ->take(5)
                ->get(),
        ];
    }

    private function getCompletionRate()
    {
        $totalTasks = Task::count();
        $completedTasks = Task::where('status', 'completed')->count();

        return $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
    }

    private function getMonthlyTaskTrends()
    {
        return Task::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::createFromFormat('m', $item->month)->format('M') => $item->count];
            });
    }

    private function getMonthlyRevenueTrends()
    {
        return Task::selectRaw('MONTH(completed_at) as month, SUM(estimated_cost) as revenue')
            ->where('status', 'completed')
            ->whereYear('completed_at', Carbon::now()->year)
            ->groupByRaw('MONTH(completed_at)')
            ->orderByRaw('MONTH(completed_at)')
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::createFromFormat('m', $item->month)->format('M') => $item->revenue ?? 0];
            });
    }
}
