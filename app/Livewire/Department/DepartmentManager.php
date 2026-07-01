<?php

namespace App\Livewire\Department;

use App\Models\Department;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentManager extends Component
{
    use WithPagination;

    public $departmentId;
    public $name;
    public $isEditMode = false;
    public $departmentToDelete = null;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:departments,name,' . $this->departmentId,
        ];
    }

    public function save()
    {
        $this->validate();

        Department::updateOrCreate(
            ['id' => $this->departmentId],
            ['name' => $this->name]
        );

        Flux::toast($this->isEditMode ? 'ডিপার্টমেন্ট আপডেট হয়েছে!' : 'নতুন ডিপার্টমেন্ট যুক্ত হয়েছে!');
        $this->resetFields();
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);
        $this->departmentId = $department->id;
        $this->name = $department->name;
        $this->isEditMode = true;
    }

    public function confirmDelete($id): void
    {
        $this->departmentToDelete = $id;
        Flux::modal('delete-department-modal')->show();
    }

    public function executeDelete(): void
    {
        if ($this->departmentToDelete) {
            Department::findOrFail($this->departmentToDelete)->delete();
            Flux::toast('ডিপার্টমেন্ট মুছে ফেলা হয়েছে!');

            $this->departmentToDelete = null;
            Flux::modal('delete-department-modal')->close();
        }
    }

    public function resetFields()
    {
        $this->reset(['departmentId', 'name', 'isEditMode']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.department.department-manager', [
            'departments' => Department::latest()->paginate(10),
        ])->layout('layouts.app', ['title' => 'Department Manager']);
    }
}
