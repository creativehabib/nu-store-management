<?php

namespace App\Observers;

use App\Models\Requisition;
use App\Support\AuditLogger;

class RequisitionObserver
{
    public function updated(Requisition $requisition): void
    {
        if (! $requisition->wasChanged('status')) {
            return;
        }

        AuditLogger::record(
            'requisition.status_changed',
            __('Requisition :number status changed from :old to :new.', [
                'number' => $requisition->requisition_no,
                'old' => $requisition->getOriginal('status'),
                'new' => $requisition->status,
            ]),
            $requisition,
            ['status' => $requisition->getOriginal('status')],
            ['status' => $requisition->status],
        );
    }
}
