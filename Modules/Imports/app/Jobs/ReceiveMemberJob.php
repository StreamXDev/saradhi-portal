<?php

namespace Modules\Imports\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Imports\Services\ReceiveMemberService;

class ReceiveMemberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $requestData;
    
    /**
     * Create a new job instance.
     */
    public function __construct(array $requestData) {
        $this->requestData = $requestData;
    }

    /**
     * Execute the job.
     */
    public function handle(ReceiveMemberService $receiveMemberService): void {
        $receiveMemberService->createMember($this->requestData);
    }
}
