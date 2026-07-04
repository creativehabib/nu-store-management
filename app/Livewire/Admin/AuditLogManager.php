<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $eventFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEventFilter(): void
    {
        $this->resetPage();
    }

    public function render(): mixed
    {
        $query = AuditLog::query()
            ->with('user')
            ->latest();

        if ($this->eventFilter !== '') {
            $query->where('event', $this->eventFilter);
        }

        if ($this->search !== '') {
            $query->where(function ($query): void {
                $query->where('description', 'like', '%'.$this->search.'%')
                    ->orWhere('event', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', function ($userQuery): void {
                        $userQuery->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%')
                            ->orWhere('pf_no', 'like', '%'.$this->search.'%');
                    });
            });
        }

        return view('livewire.admin.audit-log-manager', [
            'auditLogs' => $query->paginate(15),
            'events' => AuditLog::query()->distinct()->orderBy('event')->pluck('event'),
        ])->layout('layouts.app', ['title' => 'Audit Trail']);
    }
}
