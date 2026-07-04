<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use Illuminate\Contracts\View\View;

class RequisitionVerificationController extends Controller
{
    public function __invoke(Requisition $requisition): View
    {
        $requisition->load(['user.department', 'items.product']);

        return view('workflow.requisition-verification', [
            'requisition' => $requisition,
        ]);
    }
}
