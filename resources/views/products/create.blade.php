@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Thêm sản phẩm" />

    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
            x-data="imageUploader()">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Thêm sản phẩm mới</h3>
            </div>

            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-forms.input label="Tên sản phẩm" name="name" :value="old('name')" required />
                    </div>
                    <div>
                        <x-forms.input label="Slug" name="slug" :value="old('slug')" placeholder="Tự động tạo" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Danh mục <span class="text-red-500">*</span></label>
                        <select name="category_id" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">— Chọn danh mục —</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-forms.input label="Giá (VNĐ)" name="price" type="number" min="0" step="1000" :value="old('price')" required />
                    </div>
                    <div>
                        <x-forms.input label="Tồn kho" name="stock" type="number" min="0" :value="old('stock', 0)" required />
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Trạng thái</label>
                    <select name="status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="active" @selected(old('status', 'active') === 'active')>Đang bán</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Ẩn</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Mô tả</label>
                    <textarea name="description" rows="5"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('description') }}</textarea>
                </div>

                {{-- Image Upload --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Hình ảnh sản phẩm</label>
                    <div class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center dark:border-gray-700"
                        @dragover.prevent @drop.prevent="handleDrop($event)">
                        <input type="file" name="images[]" multiple accept="image/*"
                            class="hidden" x-ref="fileInput"
                            @change="handleFiles($event.target.files)" />
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.5"
                            class="mx-auto mb-3 text-gray-400">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <polyline points="21 15 16 10 5 21" />
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Kéo thả ảnh vào đây hoặc
                            <button type="button" @click="$refs.fileInput.click()"
                                class="text-brand-500 hover:underline">chọn file</button>
                        </p>
                        <p class="mt-1 text-xs text-gray-400">PNG, JPG, GIF tối đa 2MB mỗi ảnh</p>
                    </div>

                    {{-- Preview --}}
                    <div class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-6" x-show="previews.length > 0">
                        <template x-for="(preview, i) in previews" :key="i">
                            <div class="relative">
                                <img :src="preview" class="h-20 w-full rounded-lg object-cover" />
                                <button type="button" @click="removePreview(i)"
                                    class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white text-xs hover:bg-red-600">
                                    ×
                                </button>
                                <span x-show="i === 0"
                                    class="absolute bottom-0 left-0 rounded-bl-lg rounded-tr-lg bg-brand-500 px-1 py-0.5 text-xs text-white">
                                    Chính
                                </span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Lưu sản phẩm
                    </button>
                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function imageUploader() {
            return {
                previews: [],
                files: [],
                handleFiles(fileList) {
                    Array.from(fileList).forEach(file => {
                        if (!file.type.startsWith('image/')) return;
                        const reader = new FileReader();
                        reader.onload = e => this.previews.push(e.target.result);
                        reader.readAsDataURL(file);
                        this.files.push(file);
                    });
                },
                handleDrop(e) {
                    this.handleFiles(e.dataTransfer.files);
                },
                removePreview(index) {
                    this.previews.splice(index, 1);
                    this.files.splice(index, 1);
                }
            }
        }
    </script>
@endsection
