<?php
// app/Http/Controllers/RH/DocumentController.php

namespace App\Http\Controllers\RH;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{Auth, DB, Log, Storage};
use Illuminate\Support\Str;

use App\Http\Controllers\Controller;
use App\Models\{Employee, EmployeeDocument};

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|rh|gerente']);
        $this->middleware('permission:rh.documents.create')->only(['create', 'store']);
        $this->middleware('permission:rh.documents.approve')->only(['approve', 'reject']);
        $this->middleware('permission:rh.documents.delete')->only(['destroy']);
    }

    /**
     * Display a listing of documents.
     */
    public function index(Request $request): View
    {
        $query = EmployeeDocument::with(['employee.user', 'uploadedBy', 'approvedBy']);

        // Gerente vê apenas documentos do seu departamento
        if (Auth::user()->hasRole('gerente')) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', Auth::user()->employee?->department_id);
            });
        }

        // Aplicar filtros
        $this->applyFilters($query, $request);

        // Estatísticas
        $stats = $this->getDocumentStats();

        $documents = $query->latest()->paginate(20)->withQueryString();

        // Lista de funcionários para filtro
        $employees = Employee::with('user')
            ->where('status', 'active')
            ->get();

        return view('rh.documents.index', compact('documents', 'stats', 'employees'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create(): View
    {
        $employees = Employee::with('user')
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        $documentTypes = [
            'rg' => 'RG',
            'cpf' => 'CPF',
            'ctps' => 'CTPS',
            'pis_pasep' => 'PIS/PASEP',
            'reservist' => 'Reservista',
            'voter_id' => 'Título de Eleitor',
            'birth_certificate' => 'Certidão de Nascimento',
            'marriage_certificate' => 'Certidão de Casamento',
            'school_record' => 'Histórico Escolar',
            'diploma' => 'Diploma',
            'certificate' => 'Certificado',
            'resume' => 'Currículo',
            'contract' => 'Contrato de Trabalho',
            'medical' => 'Atestado Médico',
            'address_proof' => 'Comprovante de Endereço',
            'bank_details' => 'Dados Bancários',
            'health_plan' => 'Plano de Saúde',
            'payroll' => 'Holerite',
            'tax_declaration' => 'Declaração de IR',
            'other' => 'Outro',
        ];

        return view('rh.documents.create', compact('employees', 'documentTypes'));
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'type' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:50'],
            'document_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date'],
            'notification_date' => ['nullable', 'date'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'is_private' => ['nullable', 'boolean'],
            'requires_approval' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::beginTransaction();

            // Verificar se o funcionário existe e está ativo
            $employee = Employee::findOrFail($validated['employee_id']);

            if (!$employee->isActive() && !Auth::user()->hasRole('admin')) {
                return back()
                    ->withInput()
                    ->with('error', 'Não é possível enviar documentos para funcionários inativos.');
            }

            $file = $request->file('file');

            // Gerar nome único para o arquivo
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $safeName = Str::slug($originalName) . '-' . time() . '.' . $extension;

            // Criar diretório organizado
            $directory = sprintf(
                'documents/%s/%s/%d',
                date('Y'),
                date('m'),
                $employee->id
            );

            // Salvar arquivo
            $path = $file->storeAs($directory, $safeName, 'public');

            // Verificar se o upload foi bem-sucedido
            if (!$path) {
                throw new \Exception('Falha ao salvar o arquivo.');
            }

            // Criar registro do documento
            $document = EmployeeDocument::create([
                'employee_id' => $validated['employee_id'],
                'uploaded_by' => Auth::id(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'category' => $validated['category'] ?? null,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_extension' => $extension,
                'file_mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'storage_disk' => 'public',
                'document_date' => $validated['document_date'] ?? null,
                'expiration_date' => $validated['expiration_date'] ?? null,
                'notification_date' => $validated['notification_date'] ?? null,
                'status' => 'pending',
                'is_private' => $validated['is_private'] ?? false,
                'requires_approval' => $validated['requires_approval'] ?? true,
                'version' => 1,
                'is_current' => true,
                'tags' => $this->parseTags($validated['tags'] ?? null),
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            // Log da atividade
            activity()
                ->performedOn($document)
                ->causedBy(Auth::user())
                ->withProperties([
                    'document_name' => $document->name,
                    'document_type' => $document->type,
                    'employee' => $employee->user->name,
                    'file_size' => $document->file_size_formatted,
                ])
                ->log('Documento enviado');

            Log::info('Document uploaded successfully', [
                'document_id' => $document->id,
                'employee_id' => $employee->id,
                'uploaded_by' => Auth::id(),
                'file_size' => $document->file_size,
            ]);

            return redirect()
                ->route('rh.documents.index')
                ->with('success', sprintf(
                    'Documento "%s" enviado com sucesso para %s!',
                    $document->name,
                    $employee->user->name
                ));

        } catch (\Exception $e) {
            DB::rollBack();

            // Remover arquivo se foi salvo antes do erro
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            Log::error('Failed to upload document', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id(),
                'employee_id' => $validated['employee_id'] ?? null,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao fazer upload do documento. Por favor, tente novamente.');
        }
    }

    /**
     * Display the specified document.
     */
    public function show(EmployeeDocument $document): View
    {
        $document->load(['employee.user', 'employee.department', 'uploadedBy', 'approvedBy']);

        // Verificar permissão para documentos privados
        if ($document->is_private && !Auth::user()->hasRole('admin|rh')) {
            if (Auth::user()->hasRole('gerente')) {
                $userDeptId = Auth::user()->employee?->department_id;
                $docDeptId = $document->employee?->department_id;

                if ($userDeptId !== $docDeptId) {
                    abort(403, 'Você não tem permissão para visualizar este documento.');
                }
            } else {
                abort(403, 'Documento privado.');
            }
        }

        return view('rh.documents.show', compact('document'));
    }

    /**
     * Approve a document.
     */
    public function approve(Request $request, EmployeeDocument $document): RedirectResponse
    {
        // Verificar se o documento está pendente
        if ($document->status !== 'pending') {
            return back()->with('warning', 'Este documento não está pendente de aprovação.');
        }

        // Verificar permissão do gerente para documentos do seu departamento
        if (Auth::user()->hasRole('gerente')) {
            $userDeptId = Auth::user()->employee?->department_id;
            $docDeptId = $document->employee?->department_id;

            if ($userDeptId !== $docDeptId) {
                return back()->with('error', 'Você não pode aprovar documentos de outro departamento.');
            }
        }

        try {
            DB::beginTransaction();

            $document->approve(Auth::id());

            // Se havia uma versão anterior, marcá-la como não atual
            if ($document->previous_version_id) {
                EmployeeDocument::where('id', $document->previous_version_id)
                    ->update(['is_current' => false]);
            }

            DB::commit();

            // Log da atividade
            activity()
                ->performedOn($document)
                ->causedBy(Auth::user())
                ->withProperties([
                    'document_name' => $document->name,
                    'employee' => $document->employee->user->name,
                ])
                ->log('Documento aprovado');

            Log::info('Document approved', [
                'document_id' => $document->id,
                'approved_by' => Auth::id(),
            ]);

            return back()->with('success', 'Documento aprovado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to approve document', [
                'error' => $e->getMessage(),
                'document_id' => $document->id,
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Erro ao aprovar documento. Por favor, tente novamente.');
        }
    }

    /**
     * Reject a document.
     */
    public function reject(Request $request, EmployeeDocument $document): RedirectResponse
    {
        // Verificar se o documento está pendente
        if ($document->status !== 'pending') {
            return back()->with('warning', 'Este documento não está pendente de aprovação.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            DB::beginTransaction();

            $document->reject(Auth::id(), $validated['rejection_reason']);

            DB::commit();

            // Log da atividade
            activity()
                ->performedOn($document)
                ->causedBy(Auth::user())
                ->withProperties([
                    'document_name' => $document->name,
                    'reason' => $validated['rejection_reason'],
                ])
                ->log('Documento rejeitado');

            Log::info('Document rejected', [
                'document_id' => $document->id,
                'rejected_by' => Auth::id(),
                'reason' => $validated['rejection_reason'],
            ]);

            return back()->with('success', 'Documento rejeitado.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to reject document', [
                'error' => $e->getMessage(),
                'document_id' => $document->id,
            ]);

            return back()->with('error', 'Erro ao rejeitar documento.');
        }
    }

    /**
     * Download document file.
     */
    public function download(EmployeeDocument $document)
    {
        try {
            // Verificar se o arquivo existe
            if (!Storage::disk($document->storage_disk)->exists($document->file_path)) {
                Log::warning('Document file not found', [
                    'document_id' => $document->id,
                    'file_path' => $document->file_path,
                ]);

                return back()->with('error', 'Arquivo não encontrado no servidor.');
            }

            // Verificar permissão para documentos privados
            if ($document->is_private && !Auth::user()->hasRole('admin|rh')) {
                if (Auth::user()->hasRole('gerente')) {
                    $userDeptId = Auth::user()->employee?->department_id;
                    $docDeptId = $document->employee?->department_id;

                    if ($userDeptId !== $docDeptId) {
                        abort(403);
                    }
                } elseif (Auth::user()->employee?->id !== $document->employee_id) {
                    abort(403);
                }
            }

            // Log do download
            activity()
                ->performedOn($document)
                ->causedBy(Auth::user())
                ->withProperties([
                    'document_name' => $document->name,
                    'file_name' => $document->file_name,
                ])
                ->log('Documento baixado');

            Log::info('Document downloaded', [
                'document_id' => $document->id,
                'downloaded_by' => Auth::id(),
            ]);

            // Retornar o download
            return Storage::disk($document->storage_disk)
                ->download($document->file_path, $document->file_name);

        } catch (\Exception $e) {
            Log::error('Failed to download document', [
                'error' => $e->getMessage(),
                'document_id' => $document->id,
            ]);

            return back()->with('error', 'Erro ao baixar o arquivo.');
        }
    }

    /**
     * Preview document (stream).
     */
    public function preview(EmployeeDocument $document)
    {
        try {
            // Verificar se o arquivo existe
            if (!Storage::disk($document->storage_disk)->exists($document->file_path)) {
                return back()->with('error', 'Arquivo não encontrado.');
            }

            // Verificar permissão
            if ($document->is_private && !Auth::user()->hasRole('admin|rh')) {
                if (Auth::user()->employee?->id !== $document->employee_id) {
                    abort(403);
                }
            }

            $file = Storage::disk($document->storage_disk)->get($document->file_path);
            $type = $document->file_mime_type;

            return response($file, 200)
                ->header('Content-Type', $type)
                ->header('Content-Disposition', 'inline; filename="' . $document->file_name . '"');

        } catch (\Exception $e) {
            Log::error('Failed to preview document', [
                'error' => $e->getMessage(),
                'document_id' => $document->id,
            ]);

            return back()->with('error', 'Erro ao visualizar o arquivo.');
        }
    }

    /**
     * Remove the specified document.
     */
    public function destroy(EmployeeDocument $document): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $fileName = $document->name;
            $filePath = $document->file_path;

            // Soft delete do registro
            $document->delete();

            // Opcional: remover o arquivo físico
            if (Storage::disk($document->storage_disk)->exists($filePath)) {
                Storage::disk($document->storage_disk)->delete($filePath);
            }

            DB::commit();

            activity()
                ->performedOn($document)
                ->causedBy(Auth::user())
                ->withProperties([
                    'document_name' => $fileName,
                ])
                ->log('Documento excluído');

            Log::info('Document deleted', [
                'document_id' => $document->id,
                'deleted_by' => Auth::id(),
            ]);

            return redirect()
                ->route('rh.documents.index')
                ->with('success', "Documento \"{$fileName}\" excluído com sucesso!");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete document', [
                'error' => $e->getMessage(),
                'document_id' => $document->id,
            ]);

            return back()->with('error', 'Erro ao excluir documento.');
        }
    }

    /**
     * Get documents expiring soon.
     */
    public function expiring(Request $request): View
    {
        $days = $request->get('days', 30);

        $query = EmployeeDocument::with(['employee.user', 'employee.department'])
            ->where('status', '!=', 'expired')
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '>=', now())
            ->where('expiration_date', '<=', now()->addDays($days));

        // Gerente vê apenas documentos do seu departamento
        if (Auth::user()->hasRole('gerente')) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', Auth::user()->employee?->department_id);
            });
        }

        $documents = $query->orderBy('expiration_date')->paginate(20);

        return view('rh.documents.expiring', compact('documents', 'days'));
    }

    /**
     * Apply filters to query.
     */
    private function applyFilters($query, Request $request): void
    {
        $query->when($request->filled('employee_id'), function ($q) use ($request) {
            $q->where('employee_id', $request->employee_id);
        });

        $query->when($request->filled('type'), function ($q) use ($request) {
            $q->where('type', $request->type);
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            switch ($request->status) {
                case 'expiring':
                    $q->where('expiration_date', '<=', now()->addDays(30))
                      ->where('expiration_date', '>=', now())
                      ->where('status', '!=', 'expired');
                    break;
                case 'expired':
                    $q->where(function ($sq) {
                        $sq->where('status', 'expired')
                           ->orWhere(function ($ssq) {
                               $ssq->where('expiration_date', '<', now())
                                  ->whereNotNull('expiration_date');
                           });
                    });
                    break;
                default:
                    $q->where('status', $request->status);
                    break;
            }
        });

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('name', 'like', "%{$search}%")
                   ->orWhere('description', 'like', "%{$search}%")
                   ->orWhere('type', 'like', "%{$search}%")
                   ->orWhereHas('employee.user', function ($uq) use ($search) {
                       $uq->where('name', 'like', "%{$search}%")
                          ->orWhere('cpf', 'like', "%{$search}%");
                   });
            });
        });

        $query->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->date_from);
        });

        $query->when($request->filled('date_to'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->date_to);
        });
    }

    /**
     * Get document statistics.
     */
    private function getDocumentStats(): array
    {
        $query = EmployeeDocument::query();

        if (Auth::user()->hasRole('gerente')) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', Auth::user()->employee?->department_id);
            });
        }

        $clone = clone $query;

        return [
            'total' => $clone->count(),
            'pending' => $clone->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'expired' => (clone $query)->where(function ($q) {
                $q->where('status', 'expired')
                  ->orWhere(function ($sq) {
                      $sq->where('expiration_date', '<', now())
                         ->whereNotNull('expiration_date');
                  });
            })->count(),
            'expiring_soon' => (clone $query)->where('expiration_date', '<=', now()->addDays(30))
                ->where('expiration_date', '>=', now())
                ->where('status', '!=', 'expired')
                ->count(),
            'total_size' => (clone $query)->sum('file_size'),
            'private_count' => (clone $query)->where('is_private', true)->count(),
        ];
    }

    /**
     * Parse tags from string to array.
     */
    private function parseTags(?string $tags): ?array
    {
        if (empty($tags)) {
            return null;
        }

        return array_filter(
            array_map('trim', explode(',', $tags)),
            fn($tag) => !empty($tag)
        );
    }

    /**
     * Get formatted file size.
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Bulk approve documents.
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'document_ids' => ['required', 'array', 'min:1'],
            'document_ids.*' => ['required', 'integer', 'exists:employee_documents,id'],
        ]);

        try {
            DB::beginTransaction();

            $count = 0;
            $documents = EmployeeDocument::whereIn('id', $validated['document_ids'])
                ->where('status', 'pending')
                ->get();

            foreach ($documents as $document) {
                // Verificar permissão do gerente
                if (Auth::user()->hasRole('gerente')) {
                    $userDeptId = Auth::user()->employee?->department_id;
                    $docDeptId = $document->employee?->department_id;

                    if ($userDeptId !== $docDeptId) {
                        continue; // Pula documentos de outros departamentos
                    }
                }

                $document->approve(Auth::id());
                $count++;
            }

            DB::commit();

            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'approved_count' => $count,
                ])
                ->log('Documentos aprovados em lote');

            return back()->with('success', "{$count} documentos aprovados com sucesso!");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to bulk approve documents', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Erro ao aprovar documentos em lote.');
        }
    }
}