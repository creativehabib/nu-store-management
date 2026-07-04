<?php

namespace App\Livewire\Purpose;

use App\Models\Purpose;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class PurposeManager extends Component
{
    use WithPagination;

    public ?int $purposeId = null;

    public string $name = '';

    public bool $is_active = true;

    public bool $isEditMode = false;

    public ?int $purposeToDelete = null;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('purposes', 'name')->ignore($this->purposeId)],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        Purpose::updateOrCreate(
            ['id' => $this->purposeId],
            ['name' => $this->name, 'is_active' => $this->is_active]
        );

        Flux::toast($this->isEditMode ? 'পারপাস সফলভাবে আপডেট হয়েছে!' : 'নতুন পারপাস যুক্ত হয়েছে!');
        $this->resetFields();
    }

    public function edit(int $id): void
    {
        $purpose = Purpose::findOrFail($id);

        $this->purposeId = $purpose->id;
        $this->name = $purpose->name;
        $this->is_active = $purpose->is_active;
        $this->isEditMode = true;
    }

    public function confirmDelete(int $id): void
    {
        $this->purposeToDelete = $id;
        Flux::modal('delete-purpose-modal')->show();
    }

    public function executeDelete(): void
    {
        if ($this->purposeToDelete) {
            Purpose::findOrFail($this->purposeToDelete)->delete();
            Flux::toast('পারপাস মুছে ফেলা হয়েছে!');

            $this->purposeToDelete = null;
            Flux::modal('delete-purpose-modal')->close();
        }
    }

    public function resetFields(): void
    {
        $this->reset(['purposeId', 'name', 'isEditMode', 'purposeToDelete']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.purpose.purpose-manager', [
            'purposes' => Purpose::latest()->paginate(10),
        ])->layout('layouts.app', ['title' => 'Purpose Manager']);
    }
}
