@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Thêm danh mục" />

    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Thêm danh mục mới</h3>
            </div>

            <form action="{{ route('categories.store') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <x-forms.input label="Tên danh mục" name="name" :value="old('name')" required />
                </div>

                <div>
                    <x-forms.input label="Slug" name="slug" :value="old('slug')"
                        placeholder="Tự động tạo nếu để trống" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Danh mục cha
                    </label>
                    <select name="parent_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">— Không có —</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Trạng thái</label>
                    <select name="status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="active" @selected(old('status', 'active') === 'active')>Hoạt động</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Ẩn</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Mô tả</label>
                    <textarea name="description" rows="4"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Lưu danh mục
                    </button>
                    <a href="{{ route('categories.index') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
