@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Sửa sản phẩm" />

    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Sửa: {{ $product->name }}</h3>
            </div>

            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data"
                class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-forms.input label="Tên sản phẩm" name="name" :value="old('name', $product->name)" required />
                    </div>
                    <div>
                        <x-forms.input label="Slug" name="slug" :value="old('slug', $product->slug)" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Danh mục <span class="text-red-500">*</span></label>
                        <select name="category_id" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-forms.input label="Giá (VNĐ)" name="price" type="number" min="0" step="1000"
                            :value="old('price', $product->price)" required />
                    </div>
                    <div>
                        <x-forms.input label="Tồn kho" name="stock" type="number" min="0"
                            :value="old('stock', $product->stock)" required />
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Trạng thái</label>
                    <select name="status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="active" @selected(old('status', $product->status) === 'active')>Đang bán</option>
                        <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Ẩn</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Mô tả</label>
                    <textarea name="description" rows="5"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('description', $product->description) }}</textarea>
                </div>

                {{-- Existing Images --}}
                @if ($product->images->isNotEmpty())
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Hình ảnh hiện tại</label>
                        <div class="grid grid-cols-4 gap-3 sm:grid-cols-6">
                            @foreach ($product->images as $image)
                                <div class="relative" x-data>
                                    <img src="{{ $image->url() }}" alt="Product image"
                                        class="h-20 w-full rounded-lg object-cover" />
                                    @if ($image->is_primary)
                                        <span class="absolute bottom-0 left-0 rounded-bl-lg rounded-tr-lg bg-brand-500 px-1 py-0.5 text-xs text-white">
                                            Chính
                                        </span>
                                    @endif
                                    <button type="button"
                                        @click="if(confirm('Xóa ảnh này?')) {
                                            fetch('{{ route('products.images.destroy', $image) }}', {
                                                method: 'DELETE',
                                                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'}
                                            }).then(() => $el.closest('.relative').remove())
                                        }"
                                        class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white text-xs hover:bg-red-600">
                                        ×
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Add New Images --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Thêm hình ảnh mới</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:text-gray-400" />
                    @error('images.*')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Cập nhật
                    </button>
                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
