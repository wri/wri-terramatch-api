<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelayedJob extends Model
{
    use HasFactory;

    // Define the table name explicitly, if necessary (optional)
    protected $table = 'delayed_jobs';

    // Use mass assignment protection for the fields that are fillable
    protected $fillable = [
        'uuid',
        'status',
        'statusCode',
        'payload',
    ];

    // Ensure the UUID is cast to string
    protected $casts = [
        'uuid' => 'string',
    ];

    /**
     * Get the current status of the job.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set the status of the job.
     *
     * @param string $status
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->save();
    }

    /**
     * Update the status code for the job.
     *
     * @param int|null $code
     * @return void
     */
    public function updateStatusCode(?int $code): void
    {
        $this->statusCode = $code;
        $this->save();
    }
}
