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
            'progress_message',
        ]);

        $this->casts = array_merge($this->casts, [
            'processed_content' => 'integer',
            'total_content' => 'integer',
            'progress_message' => 'string',
        ]);
    }

    public function processMessage(): string
    {
        $progress = 0;
        if ($this->total_content > 0) {
            $progress = (int)(($this->processed_content / $this->total_content) * 100);
        } else {
            $progress = 0;
        }

        return $this->progress_message = 'Running '. $this->processed_content .' out of '
            .$this->total_content. ' polygons ('.$progress.'%)' ;
    }
}
