<?php

namespace Modules\Ai\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MicroweberPackages\User\Models\User;

class AgentChat extends Model
{
    protected $fillable = [
        'title',
        'description',
        'agent_type',
        'user_id',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(AgentChatMessage::class, 'chat_id');
    }

    public function searches(): HasMany
    {
        return $this->hasMany(AgentChatSearch::class, 'chat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLastMessage(): ?AgentChatMessage
    {
        return $this->messages()->latest()->first();
    }

    public function getMessageCount(): int
    {
        return $this->messages()->count();
    }

    public function getUserMessageCount(): int
    {
        return $this->messages()->where('role', 'user')->count();
    }

    public function getAssistantMessageCount(): int
    {
        return $this->messages()->where('role', 'assistant')->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByAgentType($query, string $agentType)
    {
        return $query->where('agent_type', $agentType);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
