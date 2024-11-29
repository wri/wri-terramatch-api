<?php

namespace App\Models;

class DelayedJobProgress extends DelayedJob
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = array_merge($this->fillable, [
            'progress',
            'processed_content',
            'total_content',
        ]);

        $this->casts = array_merge($this->casts, [
            'progress' => 'integer',
            'processed_content' => 'integer',
            'total_content' => 'integer',
        ]);
    }

    /**
     * Calculate the progress percentage as an integer.
     *
     * @return int
     */
    public function calculateProgress(): int
    {
        if ($this->total_content > 0) {
            $this->progress = (int)(($this->processed_content / $this->total_content) * 100);
        } else {
            $this->progress = 0;
        }
        return $this->progress;
    }
}
