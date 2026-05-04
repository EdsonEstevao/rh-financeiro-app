<?php
// app/Http/Controllers/Admin/AuditController.php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{Auth, Log};
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\PDF;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of audit records.
     */
    public function indexOld(Request $request): View
    {
        // Query base para atividades
        $query = Activity::with('causer')
            ->latest();

        // Aplicar filtros
        $this->applyFilters($query, $request);

        // Paginação
        $audits = $query->paginate(20)->withQueryString();

        // Estatísticas para os cards
        $stats = $this->getStatistics($request);

        // Lista de usuários para o filtro
        // Buscar usuários que têm atividades registradas
        $users = User::whereHas('actions')
            ->orderBy('name')
            ->get();

        // Lista de tipos de eventos para o filtro
        $events = Activity::distinct()
            ->pluck('event')
            ->filter()
            ->values();

        // Lista de logs disponíveis
        $logNames = Activity::distinct()
            ->pluck('log_name')
            ->filter()
            ->values();

        return view('admin.audit.index', compact(
            'audits',
            'stats',
            'users',
            'events',
            'logNames'
        ));
    }
        /**
     * Display a listing of audit records.
     */
    public function index(Request $request): View
    {
        // Query base para atividades
        $query = Activity::with('causer')->latest();

        // Aplicar filtros
        $this->applyFilters($query, $request);

        // Paginação
        $audits = $query->paginate(20)->withQueryString();

        // Estatísticas
        $todayCount = Activity::whereDate('created_at', today())->count();
        $uniqueUsers = Activity::whereNotNull('causer_id')
            ->distinct('causer_id')
            ->count('causer_id');
        $lastActivity = Activity::latest()->first();

        // Lista de usuários para o filtro
        $users = User::whereHas('actions')
            ->orderBy('name')
            ->get();

        // Lista de tipos de eventos
        $events = Activity::distinct()
            ->pluck('event')
            ->filter()
            ->values();

        // Lista de logs disponíveis
        $logNames = Activity::distinct()
            ->pluck('log_name')
            ->filter()
            ->values();

        return view('admin.audit.index', compact(
            'audits',
            'todayCount',
            'uniqueUsers',
            'lastActivity',
            'users',
            'events',
            'logNames'
        ));
    }

    /**
     * Display the specified audit record.
     */
    public function show(Activity $activity): View
    {
        $activity->load(['causer', 'subject']);

        // Atividades relacionadas (mesmo usuário ou mesmo subject)
        $relatedActivities = Activity::with('causer')
            ->where(function ($query) use ($activity) {
                $query->where('causer_type', $activity->causer_type)
                      ->where('causer_id', $activity->causer_id)
                      ->orWhere(function ($q) use ($activity) {
                          $q->where('subject_type', $activity->subject_type)
                            ->where('subject_id', $activity->subject_id);
                      });
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

            $filename = 'auditoria-' . now()->format('d-m-Y-His') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erro ao exportar PDF de auditoria', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Erro ao gerar PDF: ' . $e->getMessage());
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
            ];

            $callback = function () use ($activities) {
                $file = fopen('php://output', 'w');

                // BOM para UTF-8
                fputs($file, "\xEF\xBB\xBF");

                // Cabeçalho
                fputcsv($file, [
                    'ID',
                    'Data/Hora',
                    'Usuário',
                    'Email',
                    'Evento',
                    'Descrição',
                    'Modelo',
                    'ID Registro',
                    'Log',
                    'IP',
                    'User Agent'
                ], ';');

                // Dados
                foreach ($activities as $activity) {
                    fputcsv($file, [
                        $activity->id,
                        $activity->created_at->format('d/m/Y H:i:s'),
                        $activity->causer?->name ?? 'Sistema',
                        $activity->causer?->email ?? '',
                        $activity->event ?? '',
                        $activity->description,
                        class_basename($activity->subject_type ?? ''),
                        $activity->subject_id ?? '',
                        $activity->log_name ?? '',
                        $activity->properties['ip_address'] ?? '',
                        $activity->properties['user_agent'] ?? '',
                    ], ';');
                }

                fclose($file);
            };

            Log::info('Auditoria exportada em Excel', [
                'user_id' => Auth::id(),
                'records' => $activities->count()
            ]);

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Erro ao exportar Excel de auditoria', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Erro ao gerar Excel: ' . $e->getMessage());
        }
    }

    /**
     * Clean old audit records.
     */
    public function cleanOldRecords(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:7', 'max:730'],
        ]);

        try {
            $days = $validated['days'];
            $cutoffDate = Carbon::now()->subDays($days);

            $count = Activity::where('created_at', '<', $cutoffDate)->count();

            if ($count === 0) {
                return back()->with('info', 'Nenhum registro antigo para limpar.');
            }

            Activity::where('created_at', '<', $cutoffDate)->delete();

            Log::warning('Registros de auditoria antigos foram limpos', [
                'deleted_count' => $count,
                'older_than_days' => $days,
                'executed_by' => Auth::id()
            ]);

            // Registrar esta ação como uma nova atividade
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'deleted_count' => $count,
                    'older_than_days' => $days,
                ])
                ->log("Limpos {$count} registros de auditoria mais antigos que {$days} dias");

            return back()->with('success', "{$count} registros de atividade removidos com sucesso!");

        } catch (\Exception $e) {
            Log::error('Erro ao limpar registros de auditoria', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Erro ao limpar registros: ' . $e->getMessage());
        }
    }

    /**
     * Get filtered activities query.
     */
    private function getFilteredActivities(Request $request)
    {
        $query = Activity::with('causer');

        return $this->applyFilters($query, $request);
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, Request $request)
    {
        // Filtro por usuário
        $query->when($request->filled('user_id'), function ($q) use ($request) {
            $q->where('causer_type', User::class)
              ->where('causer_id', $request->user_id);
        });

        // Filtro por evento
        $query->when($request->filled('event'), function ($q) use ($request) {
            $q->where('event', $request->event);
        });

        // Filtro por log_name
        $query->when($request->filled('log_name'), function ($q) use ($request) {
            $q->where('log_name', $request->log_name);
        });

        // Filtro por período - data início
        $query->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->date_from);
        });

        // Filtro por período - data fim
        $query->when($request->filled('date_to'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->date_to);
        });

        // Filtro por busca textual
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('description', 'like', "%{$search}%")
                   ->orWhereHas('causer', function ($cq) use ($search) {
                       $cq->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                   });
            });
        });

        return $query;
    }

    /**
     * Get statistics for the audit dashboard.
     */
    private function getStatistics(Request $request): array
    {
        $baseQuery = Activity::query();
        $filteredQuery = $this->getFilteredActivities($request);

        return [
            'total_records' => $baseQuery->count(),
            'today_count' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            'week_count' => (clone $baseQuery)->whereDate('created_at', '>=', now()->startOfWeek())->count(),
            'month_count' => (clone $baseQuery)->whereDate('created_at', '>=', now()->startOfMonth())->count(),
            'unique_users' => (clone $baseQuery)
                ->whereNotNull('causer_id')
                ->distinct('causer_id')
                ->count('causer_id'),
            'unique_events' => (clone $baseQuery)
                ->distinct('event')
                ->count('event'),
            'filtered_count' => $filteredQuery->count(),
            'last_activity' => (clone $baseQuery)->latest()->first(),
            'top_events' => (clone $baseQuery)
                ->selectRaw('event, COUNT(*) as total')
                ->whereNotNull('event')
                ->groupBy('event')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
            'daily_activity' => (clone $baseQuery)
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];
    }
}