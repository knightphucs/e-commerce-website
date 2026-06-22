@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Thư viện ảnh" />

    <div class="space-y-6" x-data="{ showUpload: {{ $errors->any() ? 'true' : 'false' }} }">
        @session('success')
            <x-ui.alert variant="success">{{ $value }}</x-ui.alert>
        @endsession

        {{-- Toolbar --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Tìm theo tên sản phẩm..."
                    class="w-64 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Tìm kiếm
                </button>
                @if (request()->hasAny(['search']))
                    <a href="{{ route('media.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                        Xóa lọc
                    </a>
                @endif
            </form>
            @can('products.manage')
                <button type="button" @click="showUpload = !showUpload"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Thêm ảnh
                </button>
            @endcan
        </div>

        @can('products.manage')
            <div x-show="showUpload" x-cloak
                class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <form action="{{ route('media.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sản phẩm</label>
                            <select name="product_id"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">— Chưa gán (lưu vào thư viện) —</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ảnh <span class="text-red-500">*</span></label>
                            <input type="file" name="images[]" multiple accept="image/*" required
                                class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:text-gray-400" />
                            @error('images.*')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Tải lên
                    </button>
                </form>
            </div>
        @endcan

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            @if ($images->isEmpty())
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">Chưa có ảnh nào.</p>
            @else
                <div class="grid grid-cols-4 gap-3 sm:grid-cols-6 lg:grid-cols-10" x-data>
                    @foreach ($images as $image)
                        <div class="media-tile">
                            <div class="relative">
                                <img src="{{ $image->url() }}" alt="{{ $image->product->name ?? 'Ảnh chưa gán' }}"
                                    class="aspect-square w-full rounded-lg object-cover" />
                                @if ($image->is_primary)
                                    <span class="absolute bottom-1 left-1 rounded bg-brand-500 px-1 py-0.5 text-[10px] leading-none text-white">
                                        Chính
                                    </span>
                                @endif
                                @can('products.manage')
                                    <button type="button"
                                        @click="if(confirm('Xóa ảnh này?')) {
                                            fetch('{{ route('products.images.destroy', $image) }}', {
                                                method: 'DELETE',
                                                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'}
                                            }).then(() => $el.closest('.media-tile').remove())
                                        }"
                                        class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white text-xs hover:bg-red-600">
                                        ×
                                    </button>
                                @endcan
                            </div>
                            @if ($image->product)
                                <a href="{{ route('products.edit', $image->product) }}"
                                    class="mt-1 block truncate text-xs text-gray-600 hover:text-brand-500 hover:underline dark:text-gray-400">
                                    {{ $image->product->name }}
                                </a>
                            @else
                                <span class="mt-1 block truncate text-xs font-medium text-amber-600 dark:text-amber-400">
                                    Chưa gán
                                </span>
                                @can('products.manage')
                                    <form action="{{ route('media.assign', $image) }}" method="POST" class="mt-1">
                                        @csrf
                                        <select name="product_id" onchange="this.form.submit()"
                                            class="w-full rounded-md border border-gray-300 bg-white px-1.5 py-1 text-[11px] text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            <option value="">Gán cho...</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($images->hasPages())
                    <div class="mt-6">
                        {{ $images->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
