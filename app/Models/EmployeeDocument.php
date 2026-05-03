<?php
// app/Models/EmployeeDocument.php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'uploaded_by',
        'name',
        'description',
        'type',
        'category',
        'file_path',
        'file_name',
        'file_extension',
        'file_mime_type',
        'file_size',
        'storage_disk',
        'document_date',
        'expiration_date',
        'notification_date',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'version',
        'is_current',
        'previous_version_id',
        'tags',
        'metadata',
        'notes',
        'is_private',
        'requires_approval',
    ];

    protected $casts = [
        'document_date' => 'date',
        'expiration_date' => 'date',
        'notification_date' => 'date',
        'approved_at' => 'datetime',
        'file_size' => 'integer',
        'version' => 'integer',
        'is_current' => 'boolean',
        'is_private' => 'boolean',
        'requires_approval' => 'boolean',
        'tags' => 'json',
        'metadata' => 'json',
    ];

    /**
     * Funcionário dono do documento
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Usuário que fez o upload
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Usuário que aprovou o documento
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Versão anterior do documento
     */
    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, 'previous_version_id');
    }

    /**
     * Versões mais recentes do documento
     */
    public function newerVersions()
    {
        return $this->hasMany(EmployeeDocument::class, 'previous_version_id');
    }

    /**
     * URL do arquivo
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::disk($this->storage_disk)->url($this->file_path);
    }

    /**
     * Caminho completo do arquivo
     */
    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->storage_disk)->path($this->file_path);
    }

    /**
     * Tamanho do arquivo formatado
     */
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Verifica se o documento está vencido
     */
    public function isExpired(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }
        return $this->expiration_date->isPast();
    }

    /**
     * Dias até o vencimento
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->expiration_date) {
            return null;
        }
        return now()->diffInDays($this->expiration_date, false);
    }

    /**
     * Scope para documentos pendentes de aprovação
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para documentos aprovados
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope para documentos vencidos
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                    ->orWhere(function ($q) {
                        $q->where('expiration_date', '<', now())
                          ->whereNotNull('expiration_date');
                    });
    }

    /**
     * Scope para documentos que vencem em breve (30 dias)
     */
    public function scopeExpiringSoon($query)
    {
        return $query->where('expiration_date', '<=', now()->addDays(30))
                    ->where('expiration_date', '>=', now())
                    ->where('status', '!=', 'expired');
    }

    /**
     * Scope para documentos atuais (última versão)
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Aprovar documento
     */
    public function approve(int $approvedBy): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Rejeitar documento
     */
    public function reject(int $approvedBy, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}