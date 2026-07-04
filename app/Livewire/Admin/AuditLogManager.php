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

    public string $bulkAction = '';

    public bool $selectPage = false;

    /** @var array<int, int> */
    public array $selectedAuditLogs = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatingEventFilter(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedSelectPage(bool $selected): void
    {
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
        AuditLog::query()->whereKey($auditLogId)->delete();

        Flux::toast(__('Audit log deleted successfully.'));
        $this->clearSelection();
    }

    public function deleteAllRecords(): void
    {
        $count = AuditLog::query()->count();

        if ($count === 0) {
            Flux::toast(__('No audit logs found to delete.'), variant: 'danger');

            return;
        }

        AuditLog::query()->delete();

        Flux::toast(__('All audit logs deleted successfully.'));
        $this->resetPage();
        $this->clearSelection();
    }

    public function reloadLogs(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function render(): mixed
    {
        $query = $this->auditLogQuery();

        return view('livewire.admin.audit-log-manager', [
            'auditLogs' => $query->paginate(15),
            'events' => AuditLog::query()->distinct()->orderBy('event')->pluck('event'),
            'totalAuditLogs' => AuditLog::query()->count(),
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

    protected function clearSelection(): void
    {
        $this->selectPage = false;
        $this->selectedAuditLogs = [];
    }
}
