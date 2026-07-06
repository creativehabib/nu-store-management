<?php

namespace App\Livewire\Workflow;

use App\Models\Department;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\User;
use App\Support\ApprovalWorkflow;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class FinalPrint extends Component
{
    public $requisition;

    public $officerDetails = [];

    /** @var list<string> */
    public array $signatureRoles = [];

    public string $verificationUrl = '';

    public string $verificationQrUrl = '';

    public function mount(int $id): void
    {
        $this->requisition = Requisition::with(['user.department', 'user.designation', 'items.product'])
            ->forUserDepartment()
            ->findOrFail($id);

        $this->verificationUrl = URL::signedRoute('requisition.verify', ['requisition' => $this->requisition]);
        $this->verificationQrUrl = 'https://quickchart.io/qr?size=180&margin=1&text='.rawurlencode($this->verificationUrl);

        $user = auth()->user();
        $isGlobalAdmin = in_array($user->role, ['admin', 'super_admin', 'initiator']);
        if (! $isGlobalAdmin) {
            abort(403, 'Unauthorized access.');
        }
        // আবেদনকারীর ডিপার্টমেন্ট আইডি
        $applicantDeptId = $this->requisition->user->department_id;

        // সেন্ট্রাল স্টোর বা নিজ দপ্তরের আইডি ডাইনামিকভাবে বের করা
        $approvingDeptId = Department::getApprovingDepartmentId($applicantDeptId);

        $this->signatureRoles = ['initiator', ...ApprovalWorkflow::roles()];

        foreach ($this->signatureRoles as $role) {
            $user = User::with('designation')
                ->where('role', $role)
                ->where('department_id', $approvingDeptId)
                ->first();

            $this->officerDetails[$role] = [
                'name' => $user->name ?? 'N/A',
                'designation' => $user->designation->title ?? ucfirst(str_replace('_', ' ', $role)),
            ];
        }
    }

    public function getSignature(string $role): ?string
    {
        $history = $this->requisition->approval_history ?? [];
        foreach ($history as $h) {
            if ($h['role'] === $role && isset($h['signature'])) {
                return asset('storage/'.$h['signature']);
            }
        }

        return null;
    }

    public function distributeStock(): void
    {
        if ($this->requisition->status !== 'director_approved') {
            Flux::toast('রিকুইজিশনটি এখনো চূড়ান্ত অনুমোদন পায়নি বা ইতিমধ্যে বিতরণ হয়েছে!', 'error');

            return;
        }

        DB::transaction(function () {
            foreach ($this->requisition->items as $item) {
                $product = Product::find($item->product_id);
                if ($product && $product->stock >= $item->supplied_qty) {
                    $product->decrement('stock', $item->supplied_qty);
                }
            }
            $this->requisition->update(['status' => 'distributed']);
        });

        Flux::toast('সফলভাবে স্টক মাইনাস করা হয়েছে এবং পণ্য বিতরণ সম্পন্ন হয়েছে!');
        $this->dispatch('workflow-queue-updated');
        $this->requisition->refresh();
    }

    public function render(): mixed
    {
        return view('livewire.workflow.final-print')->layout('layouts.app', ['title' => 'Final Print']);
    }
}
