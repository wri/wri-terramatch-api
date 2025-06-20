<?php

namespace App\Models\V2\Investments;

use App\Models\Traits\HasUuid;
use App\Models\V2\Projects\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investment extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'investment_date',
        'type',
    ];

    protected $casts = [
        'investment_date' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function investmentSplits(): HasMany
    {
        return $this->hasMany(InvestmentSplit::class);
    }

    // Helper methods
    public function getTotalAmountAttribute(): float
    {
        return $this->investmentSplits()->sum('amount');
    }
}
