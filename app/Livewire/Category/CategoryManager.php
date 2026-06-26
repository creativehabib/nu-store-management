<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryManager extends Component
{
    use WithPagination;

    public $categoryId;
    public $name;
    public $isEditMode = false;

    // নতুন প্রপার্টি
    public $categoryToDelete = null;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name,'.$this->categoryId,
        ];
    }

    public function save()
    {
        $this->validate();

        Category::updateOrCreate(
            ['id' => $this->categoryId],
            ['name' => $this->name]
        );

        Flux::toast($this->isEditMode ? 'ক্যাটাগরি সফলভাবে আপডেট হয়েছে!' : 'নতুন ক্যাটাগরি যুক্ত হয়েছে!');
        $this->resetFields();
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->isEditMode = true;
    }
    public function confirmDelete($id): void
    {
        $this->categoryToDelete = $id;
        Flux::modal('delete-category-modal')->show();
    }
    public function executeDelete(): void
    {
        if ($this->categoryToDelete) {
            Category::findOrFail($this->categoryToDelete)->delete();
            Flux::toast('ক্যাটাগরি মুছে ফেলা হয়েছে!');

            $this->categoryToDelete = null;
            Flux::modal('delete-category-modal')->close();
        }
    }

    public function resetFields()
    {
        $this->reset(['categoryId', 'name', 'isEditMode']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.category.category-manager', [
            'categories' => Category::latest()->paginate(10),
        ])->layout('layouts.app', ['title' => 'Category Manager']);
    }
}
