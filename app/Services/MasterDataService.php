<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterDataService
{
    public function __construct(private AuditLogService $auditLog) {}

    public function create(string $modelClass, array $data, string $module): Model
    {
        return DB::transaction(function () use ($modelClass, $data, $module) {
            $record = $modelClass::create($data);
            $this->auditLog->log("{$module} Created", $module, $record, null, $record->toArray());

            return $record;
        });
    }

    public function update(Model $record, array $data, string $module): Model
    {
        return DB::transaction(function () use ($record, $data, $module) {
            $old = $record->toArray();
            $record->update($data);
            $this->auditLog->log("{$module} Updated", $module, $record, $old, $record->toArray());

            return $record;
        });
    }
}
