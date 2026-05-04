<?php
// app/Http/Controllers/Admin/AuditController.php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of activity logs.
     */
    public function index(Request $request): View
    {
        // Base query
        $query = Activity::with('causer')
            ->latest();

        // Apply filters
        $query->when($request->filled('user_id'), function ($q) use ($request) {
            $q->where('causer_type', User::class)
              ->where('causer_id', $request->user_id);
        })
        ->when($request->filled('event'), function ($q) use ($request) {
            $q->where('event', $request->event);
        })
        ->when($request->filled('log_name'), function ($q) use ($request) {
            $q->where('log_name', $request->log_name);
        })
        ->when($request->filled('subject_type'), function ($q) use ($request) {
            $q->where('subject_type', $request->subject_type);
        })
        ->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->date_from);
        })
        ->when($request->filled('date_to'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->date_to);
        })
        ->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('description', 'like', "%{$search}%")
                   ->orWhere('properties', 'like', "%{$search}%")
                   ->orWhereHas('causer', function ($cq) use ($search) {
                       $cq->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                   });
            });
        });

        // Paginate results
        $audits = $query->paginate(20)->withQueryString();

        // Statistics
        $stats = $this->getStatistics($request);

        // Get unique log names for filter
        $logNames = Activity::distinct()->pluck('log_name')->filter();

        // Get unique events for filter
        $events = Activity::distinct()->pluck('event')->filter();

        // Get users for filter
        $users = User::whereHas('actions')->orderBy('name')->get();

        return view('admin.audit.index', compact(
            'audits',
            'stats',
            'logNames',
            'events',
            'users'
        ));
    }

    /**
     * Display the specified activity.
     */
    public function show(Activity $activity): View
    {
        $activity->load('causer', 'subject');

        // Get related activities (same subject or causer)
        $relatedActivities = Activity::where(function ($q) use ($activity) {
            $q->where('subject_type', $activity->subject_type)
              ->where('subject_id', $activity->subject_id);
        })
        ->orWhere(function ($q) use ($activity) {
            $q->where('causer_type', $activity->causer_type)
              ->where('causer_id', $activity->causer_id);
        })
        ->where('id', '!=', $activity->id)
        ->latest()
        ->limit(10)
        ->get();

        return view('admin.audit.show', compact('activity', 'relatedActivities'));
    }

    /**
     * Export activities as PDF.
     */
    public function exportPDF(Request $request)
    {
        try {
            $activities = $this->getFilteredActivities($request)->get();

            $pdf = PDF::loadView('admin.audit.pdf', [
                'activities' => $activities,
                'filters' => [
                    'user' => $request->filled('user_id')
                        ? User::find($request->user_id)?->name
                        : 'Todos',
                    'event' => $request->event ?: 'Todos',
                    'log_name' => $request->log_name ?: 'Todos',
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                    'generated_at' => now()->format('d/m/Y H:i:s'),
                    'total_records' => $activities->count(),
                ],
                'stats' => [
                    'total' => $activities->count(),
                    'by_event' => $activities->groupBy('event')->map->count(),
                ]
            ]);

            $pdf->setPaper('a4', 'landscape');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

            $filename = 'auditoria-' . now()->format('d-m-Y-His') . '.pdf';

            Log::info('Audit PDF exported', [
                'user_id' => auth()->id(),
                'filename' => $filename,
                'records' => $activities->count()
            ]);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Failed to export audit PDF', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Erro ao exportar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export activities as CSV/Excel.
     */
    public function exportExcel(Request $request)
    {
        try {
            $activities = $this->getFilteredActivities($request)->get();

            $filename = 'auditoria-' . now()->format('d-m-Y-His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use ($activities) {
                $file = fopen('php://output', 'w');

                // BOM for UTF-8
                fputs($file, "\xEF\xBB\xBF");

                // Header
                fputcsv($file, [
                    'ID',
                    'Data/Hora',
                    'Usuário',
                    'Email',
                    'Evento',
                    'Modelo',
                    'ID Registro',
                    'Descrição',
                    'Log',
                    'IP',
                    'User Agent',
                    'Propriedades (JSON)'
                ], ';');

                // Data
                foreach ($activities as $activity) {
                    fputcsv($file, [
                        $activity->id,
                        $activity->created_at->format('d/m/Y H:i:s'),
                        $activity->causer?->name ?? 'Sistema',
                        $activity->causer?->email ?? '',
                        $activity->event,
                        class_basename($activity->subject_type),
                        $activity->subject_id ?? '',
                        $activity->description,
                        $activity->log_name,
                        $activity->properties['ip'] ?? '',
                        $activity->properties['user_agent'] ?? '',
                        json_encode($activity->properties->toArray(), JSON_UNESCAPED_UNICODE)
                    ], ';');
                }

                fclose($file);
            };

            Log::info('Audit Excel exported', [
                'user_id' => auth()->id(),
                'records' => $activities->count()
            ]);

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Failed to export audit Excel', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Erro ao exportar Excel: ' . $e->getMessage());
        }
    }

    /**
     * Clean old activity logs.
     */
    public function cleanOldRecords(Request $request): RedirectResponse
    {
        $request->validate([
            'days' => ['required', 'integer', 'min:7', 'max:730'],
        ]);

        try {
            $days = $request->days ?? 90;
            $date = Carbon::now()->subDays($days);

            $count = Activity::where('created_at', '<', $date)->count();

            if ($count === 0) {
                return back()->with('info', 'Nenhum registro antigo para limpar.');
            }

            Activity::where('created_at', '<', $date)->delete();

            Log::warning('Old activity logs cleaned', [
                'deleted_count' => $count,
                'older_than_days' => $days,
                'executed_by' => auth()->id(),
                'executed_at' => now()->toDateTimeString()
            ]);

            // Register this action as a new activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'deleted_count' => $count,
                    'older_than_days' => $days,
                ])
                ->log("Cleaned {$count} activity logs older than {$days} days");

            return back()->with('success', "{$count} registros de atividade removidos com sucesso!");

        } catch (\Exception $e) {
            Log::error('Failed to clean activity logs', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Erro ao limpar registros: ' . $e->getMessage());
        }
    }

    /**
     * Get activity statistics.
     */
    private function getStatistics(Request $request): array
    {
        $baseQuery = $this->getFilteredActivities($request);
        $todayQuery = Activity::whereDate('created_at', today());
        $weekQuery = Activity::whereDate('created_at', '>=', now()->startOfWeek());
        $monthQuery = Activity::whereDate('created_at', '>=', now()->startOfMonth());

        return [
            'total_records' => Activity::count(),
            'today_count' => $todayQuery->count(),
            'week_count' => $weekQuery->count(),
            'month_count' => $monthQuery->count(),
            'unique_users' => Activity::distinct()
                ->whereNotNull('causer_id')
                ->count('causer_id'),
            'unique_events' => Activity::distinct()->count('event'),
            'filtered_count' => $baseQuery->count(),
            'last_activity' => Activity::latest()->first(),
            'events_distribution' => Activity::selectRaw('event, COUNT(*) as total')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->groupBy('event')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
            'daily_activity_count' => Activity::whereDate('created_at', today())->count(),
            'top_users' => Activity::selectRaw('causer_id, causer_type, COUNT(*) as total')
                ->whereNotNull('causer_id')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->groupBy('causer_id', 'causer_type')
                ->orderByDesc('total')
                ->limit(5)
                ->with('causer')
                ->get(),
        ];
    }

    /**
     * Get filtered activities query.
     */
    private function getFilteredActivities(Request $request)
    {
        return Activity::with('causer')
            ->when($request->filled('user_id'), function ($q) use ($request) {
                $q->where('causer_type', User::class)
                  ->where('causer_id', $request->user_id);
            })
            ->when($request->filled('event'), function ($q) use ($request) {
                $q->where('event', $request->event);
            })
            ->when($request->filled('log_name'), function ($q) use ($request) {
                $q->where('log_name', $request->log_name);
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sq) use ($search) {
                    $sq->where('description', 'like', "%{$search}%")
                       ->orWhereHas('causer', function ($cq) use ($search) {
                           $cq->where('name', 'like', "%{$search}%");
                       });
                });
            });
    }

    /**
     * Display activity trends/analytics.
     */
    public function analytics(Request $request): View
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        // Daily activity counts
        $dailyActivity = Activity::whereDate('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Events by type
        $eventsByType = Activity::whereDate('created_at', '>=', $startDate)
            ->selectRaw('event, COUNT(*) as total')
            ->groupBy('event')
            ->orderByDesc('total')
            ->get();

        // Most active hours
        $hourlyActivity = Activity::whereDate('created_at', '>=', $startDate)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Models most affected
        $topModels = Activity::whereDate('created_at', '>=', $startDate)
            ->whereNotNull('subject_type')
            ->selectRaw('subject_type, COUNT(*) as total')
            ->groupBy('subject_type')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('admin.audit.analytics', compact(
            'dailyActivity',
            'eventsByType',
            'hourlyActivity',
            'topModels',
            'days'
        ));
    }

    /**
     * Display activity by specific user.
     */
    public function userActivity(User $user, Request $request): View
    {
        $activities = Activity::where('causer_type', User::class)
            ->where('causer_id', $user->id)
            ->with('subject')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_actions' => Activity::where('causer_id', $user->id)->count(),
            'today_actions' => Activity::where('causer_id', $user->id)
                ->whereDate('created_at', today())->count(),
            'last_action' => Activity::where('causer_id', $user->id)
                ->latest()->first(),
            'most_used_event' => Activity::where('causer_id', $user->id)
                ->selectRaw('event, COUNT(*) as total')
                ->groupBy('event')
                ->orderByDesc('total')
                ->first(),
        ];

        return view('admin.audit.user-activity', compact('user', 'activities', 'stats'));
    }
}