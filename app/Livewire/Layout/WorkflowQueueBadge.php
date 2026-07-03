<?php

namespace App\Livewire\Layout;

use App\Support\WorkflowQueueCounter;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class WorkflowQueueBadge extends Component
{
    public string $type = 'initiator';

    public int $count = 0;

    public function mount(string $type): void
    {
        $this->type = $type;
        $this->refreshCount();
    }

    #[On('workflow-queue-updated')]
    public function refreshCount(): void
    {
        $counts = app(WorkflowQueueCounter::class)->countsFor(Auth::user());

        $this->count = $counts[$this->type] ?? 0;
    }

    public function render()
    {
        return view('livewire.layout.workflow-queue-badge');
    }
}
