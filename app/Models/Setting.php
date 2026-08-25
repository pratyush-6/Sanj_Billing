<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'group',
        'key',
        'value',
    ];

    public static function get(string $key, mixed $default = null, ?int $companyId = null, string $group = 'general'): mixed
    {
        $value = static::query()
            ->where('company_id', $companyId)
            ->where('group', $group)
            ->where('key', $key)
            ->value('value');

        return $value ?? $default;
    }

    public static function set(string $key, mixed $value, ?int $companyId = null, string $group = 'general'): void
    {
        static::query()->updateOrCreate(
            ['company_id' => $companyId, 'group' => $group, 'key' => $key],
            ['value' => $value]
        );
    }
}
