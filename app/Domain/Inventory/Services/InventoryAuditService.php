<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Enums\AuditEvent;
use App\Domain\Inventory\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class InventoryAuditService
{
    /** @var list<string> */
    private array $hiddenFields = ['password', 'remember_token', 'token', 'api_token'];

    public function log(
        AuditEvent|string $event,
        string $module,
        ?Model $reference = null,
        ?User $actor = null,
        ?array $before = null,
        ?array $after = null,
        ?array $meta = null,
        ?Request $request = null,
        ?string $referenceNo = null,
    ): AuditLog {
        $request ??= request();
        $actor ??= auth()->user();

        return AuditLog::query()->create([
            'user_id' => $actor?->id,
            'event' => $event instanceof AuditEvent ? $event->value : $event,
            'module' => $module,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'reference_no' => $referenceNo ?? $this->guessReferenceNo($reference),
            'before_data' => $this->clean($before),
            'after_data' => $this->clean($after),
            'meta' => $this->clean($meta),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    public function logPosted(string $module, Model $reference, User $actor, ?array $before = null, ?array $after = null, ?array $meta = null): void
    {
        $this->log(
            event: AuditEvent::Post,
            module: $module,
            reference: $reference,
            actor: $actor,
            before: $before,
            after: $after,
            meta: $meta,
        );
    }

    private function clean(?array $data): ?array
    {
        return $data === null ? null : Arr::except($data, $this->hiddenFields);
    }

    private function guessReferenceNo(?Model $model): ?string
    {
        if (! $model) {
            return null;
        }

        foreach (['receipt_no', 'issue_no', 'return_no', 'mutation_no', 'document_no', 'code', 'sku'] as $field) {
            if ($model->{$field} !== null) {
                return (string) $model->{$field};
            }
        }

        return null;
    }
}
