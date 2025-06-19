<?php

namespace App\Models;

use App\Http\Filters\v1\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array'
    ];

    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function relatedTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'related_transaction_id');
    }

    public function refunds()
    {
        return $this->hasMany(Transaction::class, 'related_transaction_id')
            ->where('transaction_type', 'refund');
    }

    public function scopeCompleted(Builder $query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRefunded(Builder $query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed(Builder $query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeCredits(Builder $query)
    {
        return $query->where('transaction_type', 'credit');
    }

    public function scopeDebits(Builder $query)
    {
        return $query->where('transaction_type', 'debit');
    }

    public function scopeRefunds(Builder $query)
    {
        return $query->where('transaction_type', 'refund');
    }

    public function scopeSource(Builder $query, string $source)
    {
        return $query->where('transaction_source', $source);
    }

    public function isRefundable(): bool
    {
        return $this->status === 'completed'
            && !$this->refunds()->exists()
            && in_array($this->transaction_source, [
                'job_upload',
                'job_application',
                'gig_creation',
                'gig_purchase'
            ]);
    }

    public function markAsRefunded(): bool
    {
        return $this->update(['status' => 'refunded']);
    }

    public static function createRefund(Transaction $originalTransaction, array $additionalData = []): self
    {
        return static::create(array_merge([
            'user_id' => $originalTransaction->user_id,
            'transaction_type' => 'refund',
            'amount' => $originalTransaction->amount,
            'currency' => $originalTransaction->currency,
            'status' => 'completed',
            'transaction_source' => 'refund',
            'reference' => 'REF-' . uniqid(),
            'related_transaction_id' => $originalTransaction->id,
            'description' => "Refund for transaction {$originalTransaction->reference}",
            'sourceable_type' => $originalTransaction->sourceable_type,
            'sourceable_id' => $originalTransaction->sourceable_id,
        ], $additionalData));
    }
}
