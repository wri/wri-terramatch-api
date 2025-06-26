<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectPipeline extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'submitted_by',
        'description',
        'program',
        'cohort',
        'publish_for',
        'url',
    ];

    protected $casts = [
        'cohort' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Helper method to check if project pipeline has a specific cohort
     * Handles both old string format and new array format
     */
    public function hasCohort(string $cohortName): bool
    {
        if (empty($this->cohort)) {
            return false;
        }

        // If cohort is already an array, check if it contains the cohort
        if (is_array($this->cohort)) {
            return in_array($cohortName, $this->cohort);
        }

        // Legacy support: if cohort is still a string, compare directly
        return $this->cohort === $cohortName;
    }

    /**
     * Helper method to get cohorts as array
     * Handles both old string format and new array format
     */
    public function getCohortsArray(): array
    {
        if (empty($this->cohort)) {
            return [];
        }

        // If cohort is already an array, return it
        if (is_array($this->cohort)) {
            return $this->cohort;
        }

        // Legacy support: if cohort is still a string, return as single-element array
        return [$this->cohort];
    }

    /**
     * Helper method to add a cohort to the project pipeline
     */
    public function addCohort(string $cohortName): void
    {
        $cohorts = $this->getCohortsArray();

        if (! in_array($cohortName, $cohorts)) {
            $cohorts[] = $cohortName;
            $this->cohort = $cohorts;
        }
    }

    /**
     * Helper method to remove a cohort from the project pipeline
     */
    public function removeCohort(string $cohortName): void
    {
        $cohorts = $this->getCohortsArray();

        $cohorts = array_filter($cohorts, function ($cohort) use ($cohortName) {
            return $cohort !== $cohortName;
        });

        $this->cohort = array_values($cohorts); // Re-index array
    }
}
