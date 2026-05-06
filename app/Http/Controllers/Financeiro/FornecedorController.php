<?php
// app/Http/Controllers/Financeiro/FornecedorController.php

namespace App\Http\Controllers\Financeiro;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{Auth, DB, Log};

use App\Http\Controllers\Controller;
use App\Models\Fornecedor;

class FornecedorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,financeiro']);
        
        // Permissões granulares
        $this->middleware('permission:financeiro.fornecedores.view')->only(['index', 'show']);
        $this->middleware('permission:financeiro.fornecedores.create')->only(['create', 'store']);
        $this->middleware('permission:financeiro.fornecedores.edit')->only(['edit', 'update']);
        $this->middleware('permission:financeiro.fornecedores.delete')->only(['destroy']);
    }

    /**
     * Display a listing of fornecedores.
     */
    public function index(Request $request): View
    {
        $query = Fornecedor::query()
            ->withCount(['contasPagar'])
            ->withSum(['contasPagar as total_pagar' => function ($q) {
                $q->whereIn('status', ['pendente', 'aprovado', 'agendado']);
            }], 'valor_total');

        // Aplicar filtros
        $this->applyFilters($query, $request);

        $fornecedores = $query->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        // Estatísticas
        $ativos = Fornecedor::query()->where('is_active', true)->count();
        $pendentes = Fornecedor::whereHas('contasPagar', function ($q) {
            $q->whereIn('status', ['pendente', 'aprovado', 'agendado']);
        })->count();

        return view('fornecedores.index', compact('fornecedores', 'ativos', 'pendentes'));
    }

    /**
     * Show the form for creating a new fornecedor.
     */
    public function create(): View
    {
        return view('fornecedores.create');
    }

    /**
     * Store a newly created fornecedor.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:200'],
            'nome_fantasia' => ['nullable', 'string', 'max:200'],
            'documento' => ['required', 'string', 'max:20', 'unique:fornecedores,documento'],
            'tipo_pessoa' => ['required', 'in:fisica,juridica'],
            'email' => ['nullable', 'email', 'max:100'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'endereco' => ['nullable', 'string', 'max:500'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:2'],
            'cep' => ['nullable', 'string', 'max:10'],
            'contato_nome' => ['nullable', 'string', 'max:100'],
            'contato_cargo' => ['nullable', 'string', 'max:100'],
            'banco_codigo' => ['nullable', 'string', 'max:10'],
            'banco_nome' => ['nullable', 'string', 'max:50'],
            'agencia' => ['nullable', 'string', 'max:20'],
            'conta' => ['nullable', 'string', 'max:20'],
            'pix_chave' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'string', 'max:50'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::beginTransaction();

            // Limpar documento (remover máscara)
            $validated['documento'] = $this->cleanDocument($validated['documento']);
            
            // Validar CPF/CNPJ
            if (!$this->validateDocument($validated['documento'], $validated['tipo_pessoa'])) {
                return back()
                    ->withInput()
                    ->with('error', 'Documento inválido. Verifique o CPF/CNPJ informado.');
            }

            $fornecedor = Fornecedor::create([
                ...$validated,
                'is_active' => true,
            ]);

            DB::commit();

            // Log da atividade
            activity()
                ->performedOn($fornecedor)
                ->causedBy(Auth::user())
                ->withProperties([
                    'nome' => $fornecedor->nome,
                    'documento' => $this->maskDocument($fornecedor->documento),
                    'categoria' => $fornecedor->categoria,
                ])
                ->log('Fornecedor criado');

            Log::info('Fornecedor criado com sucesso', [
                'fornecedor_id' => $fornecedor->id,
                'user_id' => Auth::id(),
                'nome' => $fornecedor->nome,
            ]);

            return redirect()
                ->route('financeiro.fornecedores.show', $fornecedor)
                ->with('success', "Fornecedor '{$fornecedor->nome}' cadastrado com sucesso!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao criar fornecedor', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $request->except(['_token']),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar fornecedor: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified fornecedor.
     */
    public function show(Fornecedor $fornecedor): View
    {
        $fornecedor->load([
            'contasPagar' => function ($query) {
                $query->latest('data_vencimento')->take(10);
            }
        ]);

        // Estatísticas do fornecedor
        $stats = [
            'total_contas' => $fornecedor->contasPagar()->count(),
            'contas_pendentes' => $fornecedor->contasPagar()
                ->whereIn('status', ['pendente', 'aprovado', 'agendado'])->count(),
            'contas_pagas' => $fornecedor->contasPagar()
                ->where('status', 'pago')->count(),
            'total_pago' => $fornecedor->contasPagar()
                ->where('status', 'pago')->sum('valor_pago'),
            'total_pendente' => $fornecedor->contasPagar()
                ->whereIn('status', ['pendente', 'aprovado', 'agendado'])->sum('valor_total'),
            'ultima_conta' => $fornecedor->contasPagar()->latest()->first(),
        ];

        return view('fornecedores.show', compact('fornecedor', 'stats'));
    }

    /**
     * Show the form for editing the specified fornecedor.
     */
    public function edit(Fornecedor $fornecedor): View
    {
        return view('fornecedores.edit', compact('fornecedor'));
    }

    /**
     * Update the specified fornecedor.
     */
    public function update(Request $request, Fornecedor $fornecedor): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:200'],
            'nome_fantasia' => ['nullable', 'string', 'max:200'],
            'documento' => ['required', 'string', 'max:20', 'unique:fornecedores,documento,' . $fornecedor->id],
            'tipo_pessoa' => ['required', 'in:fisica,juridica'],
            'email' => ['nullable', 'email', 'max:100'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'endereco' => ['nullable', 'string', 'max:500'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:2'],
            'cep' => ['nullable', 'string', 'max:10'],
            'contato_nome' => ['nullable', 'string', 'max:100'],
            'contato_cargo' => ['nullable', 'string', 'max:100'],
            'banco_codigo' => ['nullable', 'string', 'max:10'],
            'banco_nome' => ['nullable', 'string', 'max:50'],
            'agencia' => ['nullable', 'string', 'max:20'],
            'conta' => ['nullable', 'string', 'max:20'],
            'pix_chave' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'string', 'max:50'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ]);

        try {
            DB::beginTransaction();

            // Limpar documento
            $validated['documento'] = $this->cleanDocument($validated['documento']);
            
            // Validar CPF/CNPJ se foi alterado
            if ($validated['documento'] !== $fornecedor->documento) {
                if (!$this->validateDocument($validated['documento'], $validated['tipo_pessoa'])) {
                    return back()
                        ->withInput()
                        ->with('error', 'Documento inválido. Verifique o CPF/CNPJ informado.');
                }
            }

            // Registrar mudanças para auditoria
            $changes = array_diff_assoc($validated, $fornecedor->only(array_keys($validated)));

            $fornecedor->update($validated);

            DB::commit();

            // Log da atividade
            if (!empty($changes)) {
                activity()
                    ->performedOn($fornecedor)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'changes' => $this->sanitizeChanges($changes),
                    ])
                    ->log('Fornecedor atualizado');
            }

            Log::info('Fornecedor atualizado com sucesso', [
                'fornecedor_id' => $fornecedor->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()
                ->route('financeiro.fornecedores.show', $fornecedor)
                ->with('success', "Fornecedor '{$fornecedor->nome}' atualizado com sucesso!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao atualizar fornecedor', [
                'error' => $e->getMessage(),
                'fornecedor_id' => $fornecedor->id,
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar fornecedor: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified fornecedor (soft delete).
     */
    public function destroy(Fornecedor $fornecedor): RedirectResponse
    {
        try {
            // Verificar se há contas pendentes
            $contasPendentes = $fornecedor->contasPagar()
                ->whereIn('status', ['pendente', 'aprovado', 'agendado'])
                ->count();

            if ($contasPendentes > 0) {
                return back()->with('error', 
                    "Não é possível excluir este fornecedor. Existem {$contasPendentes} contas pendentes vinculadas.");
            }

            DB::beginTransaction();

            $nome = $fornecedor->nome;
            // $fornecedor->delete();
            $fornecedor->destroy($fornecedor->id); // Soft delete

            DB::commit();

            activity()
                ->performedOn($fornecedor)
                ->causedBy(Auth::user())
                ->withProperties(['nome' => $nome])
                ->log('Fornecedor excluído');

            Log::warning('Fornecedor excluído', [
                'fornecedor_id' => $fornecedor->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()
                ->route('financeiro.fornecedores.index')
                ->with('success', "Fornecedor '{$nome}' excluído com sucesso!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao excluir fornecedor', [
                'error' => $e->getMessage(),
                'fornecedor_id' => $fornecedor->id,
            ]);

            return back()->with('error', 'Erro ao excluir fornecedor: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Fornecedor $fornecedor): RedirectResponse
    {
        try {
            $fornecedor->update([
                'is_active' => !$fornecedor->is_active
            ]);

            $status = $fornecedor->is_active ? 'ativado' : 'desativado';

            activity()
                ->performedOn($fornecedor)
                ->causedBy(Auth::user())
                ->withProperties(['is_active' => $fornecedor->is_active])
                ->log("Fornecedor {$status}");

            return back()->with('success', "Fornecedor {$status} com sucesso!");

        } catch (\Exception $e) {
            Log::error('Erro ao alternar status do fornecedor', [
                'error' => $e->getMessage(),
                'fornecedor_id' => $fornecedor->id,
            ]);

            return back()->with('error', 'Erro ao alterar status.');
        }
    }

    /**
     * API: Search fornecedores for autocomplete.
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = $request->input('q', '');
        
        $fornecedores = Fornecedor::query()->where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->where('nome', 'like', "%{$search}%")
                      ->orWhere('nome_fantasia', 'like', "%{$search}%")
                      ->orWhere('documento', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'nome', 'nome_fantasia', 'documento', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $fornecedores,
        ]);
    }

    /**
     * API: Get fornecedor details for modal.
     */
    public function getDetails(Fornecedor $fornecedor): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $fornecedor->id,
                'nome' => $fornecedor->nome,
                'documento' => $fornecedor->documento_formatted,
                'email' => $fornecedor->email,
                'telefone' => $fornecedor->telefone,
                'banco_nome' => $fornecedor->banco_nome,
                'agencia' => $fornecedor->agencia,
                'conta' => $fornecedor->conta,
                'pix_chave' => $fornecedor->pix_chave,
            ],
        ]);
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('nome_fantasia', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('categoria', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('tipo_pessoa')) {
            $query->where('tipo_pessoa', $request->tipo_pessoa);
        }
    }

    /**
     * Validate Brazilian document (CPF or CNPJ).
     */
    private function validateDocument(string $document, string $tipo): bool
    {
        $document = $this->cleanDocument($document);

        if ($tipo === 'fisica' && strlen($document) === 11) {
            return $this->validateCPF($document);
        }

        if ($tipo === 'juridica' && strlen($document) === 14) {
            return $this->validateCNPJ($document);
        }

        return false;
    }

    /**
     * Validate CPF.
     */
    private function validateCPF(string $cpf): bool
    {
        // Eliminar CPFs inválidos conhecidos
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Validar dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += $cpf[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ($cpf[$t] != $digit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate CNPJ.
     */
    private function validateCNPJ(string $cnpj): bool
    {
        // Validar primeiro dígito verificador
        $sum = 0;
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weights[$i];
        }
        
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        if ($cnpj[12] != $digit1) {
            return false;
        }

        // Validar segundo dígito verificador
        $sum = 0;
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weights[$i];
        }
        
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return $cnpj[13] == $digit2;
    }

    /**
     * Clean document number (remove non-digits).
     */
    private function cleanDocument(string $document): string
    {
        return preg_replace('/[^0-9]/', '', $document);
    }

    /**
     * Mask document for display.
     */
    private function maskDocument(string $document): string
    {
        $document = $this->cleanDocument($document);
        
        if (strlen($document) === 11) {
            // CPF: 000.000.000-00
            return substr($document, 0, 3) . '.***.***-' . substr($document, -2);
        }
        
        if (strlen($document) === 14) {
            // CNPJ: 00.000.000/0000-00
            return substr($document, 0, 2) . '.***.***/' . substr($document, -6, 4) . '-**';
        }
        
        return $document;
    }

    /**
     * Sanitize changes for logging (remove sensitive data).
     */
    private function sanitizeChanges(array $changes): array
    {
        if (isset($changes['documento'])) {
            $changes['documento'] = $this->maskDocument($changes['documento']);
        }

        return $changes;
    }
}