<?php

namespace App\Livewire\Designation;

use App\Models\Designation;
use Livewire\Component;
use Flux\Flux;

class DesignationManager extends Component
{
    public $designationId;
    public $title;
    public $rank;
    public $isEditMode = false;
    public $designationToDelete = null;

    public function rules()
    {
        return [
            'title' => 'required|string|max:255|unique:designations,title,' . $this->designationId,
            'rank'  => 'required|integer|min:0',
        ];
    }

    public function save()
    {
        $this->validate();

        Designation::updateOrCreate(
            ['id' => $this->designationId],
            ['title' => $this->title, 'rank' => $this->rank]
        );

        Flux::toast($this->designationId ? 'পদবি সফলভাবে আপডেট হয়েছে!' : 'নতুন পদবি যুক্ত হয়েছে!');
        $this->resetFields();
    }

    public function edit($id)
    {
        $designation = Designation::findOrFail($id);
        $this->designationId = $designation->id;
        $this->title = $designation->title;
        $this->rank = $designation->rank;
        $this->isEditMode = true;
    }

    public function confirmDelete($id): void
    {
        $this->designationToDelete = $id;
        Flux::modal('delete-designation-modal')->show();
    }

    public function executeDelete(): void
    {
        if ($this->designationToDelete) {
            Designation::findOrFail($this->designationToDelete)->delete();
            Flux::toast('পদবি মুছে ফেলা হয়েছে!');
            $this->designationToDelete = null;
            Flux::modal('delete-designation-modal')->close();
        }
    }

    public function resetFields()
    {
        $this->reset(['designationId', 'title', 'rank', 'isEditMode']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.designation.designation-manager', [
            'designations' => Designation::orderBy('rank', 'asc')->get(),
        ])->layout('layouts.app', ['title' => 'Designation Manager']);
    }
}
