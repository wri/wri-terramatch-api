<?php

namespace App\Models;

class DelayedJobProgress extends DelayedJob
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = array_merge($this->fillable, [
            'processed_content',
            'total_content',
        ]);

        $this->casts = array_merge($this->casts, [
            'processed_content' => 'integer',
            'total_content' => 'integer',
        ]);
    }
}
