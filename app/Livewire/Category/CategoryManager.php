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

    // ভ্যালিডেশন রুলস
    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name,'.$this->categoryId,
        ];
    }

    // সেভ বা আপডেট করার মেথড
    public function save()
    {
        $this->validate();

        Category::updateOrCreate(
            ['id' => $this->categoryId],
            ['name' => $this->name]
        );

        // Flux টোস্ট মেসেজ
        Flux::toast($this->isEditMode ? 'ক্যাটাগরি সফলভাবে আপডেট হয়েছে!' : 'নতুন ক্যাটাগরি যুক্ত হয়েছে!');

        $this->resetFields();
    }

    // এডিট মোড অন করার মেথড
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->isEditMode = true;
    }

    // ডিলিট করার মেথড
    public function delete($id)
    {
        Category::findOrFail($id)->delete();
        Flux::toast('ক্যাটাগরি মুছে ফেলা হয়েছে!');
    }

    // ফর্ম রিসেট করার মেথড
    public function resetFields()
    {
        $this->reset(['categoryId', 'name', 'isEditMode']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.category.category-manager', [
            // লেটেস্ট ক্যাটাগরিগুলো পেজিনেশন সহ পাঠানো হচ্ছে
            'categories' => Category::latest()->paginate(10),
        ])->layout('layouts.app', ['title' => 'Category Manager']);
    }
}
