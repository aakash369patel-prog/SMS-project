<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public static function log(
        ?User $user,
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null,
    ): AuditLog {
        return AuditLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => $model ? $model::class : 'system',
            'auditable_id' => $model?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
        ]);
    }
}
