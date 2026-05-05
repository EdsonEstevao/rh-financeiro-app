<?php
// app/Traits/LogsActivityCustom.php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

trait LogsActivityCustom
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges() // Mudou de dontSubmitEmptyLogs() no v5
            ->setDescriptionForEvent(function (string $eventName) {
                $identifier = $this->getAuditIdentifier();
                $model = class_basename($this);
                return "{$model} '{$identifier}' {$this->ptBrLabel($eventName)}";
            });
    }

    protected function getAuditIdentifier(): string
    {
        // if (isset($this->user) && $this->user) {
        //     return $this->user->name;
        // }
        if (Auth::check()) {
            return Auth::user()->name ?? (string) Auth::id();
        }

        foreach (['name', 'title', 'number', 'code', 'id', 'uuid'] as $attr) {
            if (!empty($this->$attr)) {
                return (string) $this->$attr;
            }
        }
        // return (string) ($this->id ?? 'desconhecido');
        return (string) ($this->name ?? $this->id ?? 'desconhecido');
    }

    protected function ptBrLabel(string $event): string
    {
        return match($event) {
            'created' => 'foi criado',
            'updated' => 'foi atualizado',
            'deleted' => 'foi deletado',
            'restored' => 'foi restaurado',
            'forceDeleted' => 'foi removido permanentemente',
            default => "teve evento '{$event}'"
        };
    }
}
