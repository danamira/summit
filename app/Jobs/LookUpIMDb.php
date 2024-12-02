<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LookUpIMDb implements ShouldQueue
{
    use Queueable;

    protected string $title; # assumed to be sanitized


    /**
     * Create a new job instance.
     */
    public function __construct(string $sanitizedTitle)
    {
        $this->title = $sanitizedTitle;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
