@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Danh mục sản phẩm" />

    <div class="space-y-6">
        @session('success')
            <x-ui.alert variant="success">{{ $value }}</x-ui.alert>
        @endsession
        @session('error')
            <x-ui.alert variant="danger">{{ $value }}</x-ui.alert>
        @endsession

        {{-- Toolbar --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Tìm kiếm danh mục..."
                    class="w-64 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Tìm
                </button>
                @if (request('search'))
                    <a href="{{ route('categories.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                        Xóa
                    </a>
                @endif
            </form>
            @can('categories.manage')
                <a href="{{ route('categories.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Thêm danh mục
                </a>
            @endcan
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full min-w-[700px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Tên danh mục</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Danh mục cha</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Sản phẩm</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Trạng thái</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Thao tác</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4">
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $category->name }}</p>
                                    <p class="text-gray-400 text-theme-xs">{{ $category->slug }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                        {{ $category->parent?->name ?? '—' }}
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $category->products_count }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($category->status === 'active')
                                        <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                            Hoạt động
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            Ẩn
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        @can('categories.manage')
                                            <a href="{{ route('categories.edit', $category) }}"
                                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                                                Sửa
                                            </a>
                                        @endcan
                                        @can('categories.delete')
                                            <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-600 shadow-theme-xs transition hover:bg-red-50 dark:border-red-700 dark:bg-transparent dark:text-red-400">
                                                    Xóa
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center">
                                    <p class="text-gray-500 dark:text-gray-400">Chưa có danh mục nào.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($categories->hasPages())
            <div class="mt-4">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
@endsection
