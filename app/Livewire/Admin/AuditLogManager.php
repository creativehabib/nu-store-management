<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $eventFilter = '';

    public string $startDate = '';

    public string $endDate = '';

    public string $bulkAction = '';

    public bool $selectPage = false;

    /** @var array<int, int> */
    public array $selectedAuditLogs = [];

    public function mount(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['admin', 'super_admin'], true), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPageAndSelection();
    }

    public function updatingEventFilter(): void
    {
        $this->resetPageAndSelection();
    }

    public function updatingStartDate(): void
    {
        $this->resetPageAndSelection();
    }

    public function updatingEndDate(): void
    {
        $this->resetPageAndSelection();
    }

    public function updatedSelectPage(bool $selected): void
    {
        if (! $this->canDeleteAuditLogs()) {
            $this->clearSelection();

            return;
        }

        $this->selectedAuditLogs = $selected ? $this->currentPageIds() : [];
    }

    public function applyBulkAction(): void
    {
        if ($this->bulkAction !== 'delete_selected') {
            return;
        }

        $this->deleteSelected();
    }

    public function deleteSelected(): void
    {
        $this->authorizeAuditLogDeletion();

        if ($this->selectedAuditLogs === []) {
            Flux::toast(__('Please select at least one audit log.'), variant: 'danger');

            return;
        }

        AuditLog::query()
            ->whereKey($this->selectedAuditLogs)
            ->delete();

        Flux::toast(__('Selected audit logs deleted successfully.'));
        $this->bulkAction = '';
        $this->clearSelection();
    }

    public function deleteRecord(int $auditLogId): void
    {
        $this->authorizeAuditLogDeletion();

        AuditLog::query()->whereKey($auditLogId)->delete();

        Flux::toast(__('Audit log deleted successfully.'));
        $this->clearSelection();
    }

    public function deleteAllRecords(): void
    {
        $this->authorizeAuditLogDeletion();

        $count = AuditLog::query()->count();

        if ($count === 0) {
            Flux::toast(__('No audit logs found to delete.'), variant: 'danger');

            return;
        }

        AuditLog::query()->delete();

        Flux::toast(__('All audit logs deleted successfully.'));
        $this->resetPageAndSelection();
    }

    public function reloadLogs(): void
    {
        $this->resetPageAndSelection();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'eventFilter', 'startDate', 'endDate']);
        $this->resetPageAndSelection();
    }

    public function canDeleteAuditLogs(): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public function render(): mixed
    {
        $query = $this->auditLogQuery();

        return view('livewire.admin.audit-log-manager', [
            'auditLogs' => $query->paginate(15),
            'events' => AuditLog::query()->distinct()->orderBy('event')->pluck('event'),
            'totalAuditLogs' => AuditLog::query()->count(),
            'canDeleteAuditLogs' => $this->canDeleteAuditLogs(),
        ])->layout('layouts.app', ['title' => 'Audit Trail']);
    }

    protected function auditLogQuery(): Builder
    {
        $query = AuditLog::query()
            ->with('user')
            ->latest();

        if ($this->eventFilter !== '') {
            $query->where('event', $this->eventFilter);
        }

        if ($this->startDate !== '') {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate !== '') {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        if ($this->search !== '') {
            $query->where(function (Builder $query): void {
                $query->where('description', 'like', '%'.$this->search.'%')
                    ->orWhere('event', 'like', '%'.$this->search.'%')
                    ->orWhere('ip_address', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', function (Builder $userQuery): void {
                        $userQuery->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%')
                            ->orWhere('pf_no', 'like', '%'.$this->search.'%');
                    });
            });
        }

        return $query;
    }

    /**
     * @return array<int, int>
     */
    protected function currentPageIds(): array
    {
        return $this->auditLogQuery()
            ->paginate(15, ['id'])
            ->getCollection()
            ->pluck('id')
            ->map(fn (int $id): int => $id)
            ->all();
    }

    protected function authorizeAuditLogDeletion(): void
    {
        abort_unless($this->canDeleteAuditLogs(), 403);
    }

    protected function resetPageAndSelection(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    protected function clearSelection(): void
    {
        $this->selectPage = false;
        $this->selectedAuditLogs = [];
    }
}
