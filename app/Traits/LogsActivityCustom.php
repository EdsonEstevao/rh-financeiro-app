<?php
// app/Traits/LogsActivityCustom.php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsActivityCustom
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logExcept(['password', 'password_confirmation', 'remember_token'])
            ->setDescriptionForEvent(function (string $eventName) {
                $modelName = class_basename($this);
                $identifier = $this->name ?? $this->title ?? $this->id;

                return match($eventName) {
                    'created' => "{$modelName} '{$identifier}' foi criado",
                    'updated' => "{$modelName} '{$identifier}' foi atualizado",
                    'deleted' => "{$modelName} '{$identifier}' foi deletado",
                    'restored' => "{$modelName} '{$identifier}' foi restaurado",
                    default => "{$modelName} '{$identifier}' foi {$eventName}",
                };
            });
    }
}
