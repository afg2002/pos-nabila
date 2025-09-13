<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Domains\User\Models\User;


class AuditLog extends Model
{
    protected $fillable = [
        'actor_id',
        'action',
        'table_name',
        'record_id',
        'diff_json'
    ];

    protected $casts = [
        'diff_json' => 'array',
        'record_id' => 'integer'
    ];

    // Relasi ke user yang melakukan aksi
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    // Method untuk membuat audit log
    public static function createLog($action, $tableName, $recordId, $diff = null, $actorId = null)
    {
        return self::create([
            'actor_id' => $actorId ?? Auth::id(),
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'diff_json' => $diff
        ]);
    }

    // Method untuk log create action
    public static function logCreate($tableName, $recordId, $data = null, $actorId = null)
    {
        return self::createLog('create', $tableName, $recordId, ['new' => $data], $actorId);
    }

    // Method untuk log update action
    public static function logUpdate($tableName, $recordId, $oldData, $newData, $actorId = null)
    {
        $diff = [
            'old' => $oldData,
            'new' => $newData,
            'changes' => array_diff_assoc($newData, $oldData)
        ];
        
        return self::createLog('update', $tableName, $recordId, $diff, $actorId);
    }

    // Method untuk log delete action
    public static function logDelete($tableName, $recordId, $data = null, $actorId = null)
    {
        return self::createLog('delete', $tableName, $recordId, ['deleted' => $data], $actorId);
    }

    // Scope untuk filter berdasarkan action
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    // Scope untuk filter berdasarkan table
    public function scopeByTable($query, $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    // Scope untuk filter berdasarkan actor
    public function scopeByActor($query, $actorId)
    {
        return $query->where('actor_id', $actorId);
    }

    // Scope untuk filter berdasarkan record
    public function scopeByRecord($query, $tableName, $recordId)
    {
        return $query->where('table_name', $tableName)->where('record_id', $recordId);
    }

    // Method untuk mendapatkan perubahan yang mudah dibaca
    public function getReadableChanges()
    {
        if (!$this->diff_json || !isset($this->diff_json['changes'])) {
            return [];
        }

        $changes = [];
        foreach ($this->diff_json['changes'] as $field => $newValue) {
            $oldValue = $this->diff_json['old'][$field] ?? null;
            $changes[] = [
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue
            ];
        }

        return $changes;
    }

    // Method untuk format action menjadi human readable
    public function getFormattedAction()
    {
        $actions = [
            'create' => 'Membuat',
            'update' => 'Mengubah',
            'delete' => 'Menghapus'
        ];

        return $actions[$this->action] ?? $this->action;
    }
}
