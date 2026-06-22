@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Users" />

    <div class="space-y-6">
        @session('success')
            <x-ui.alert variant="success">
                {{ $value }}
            </x-ui.alert>
        @endsession
        @session('error')
            <x-ui.alert variant="error">
                {{ $value }}
            </x-ui.alert>
        @endsession

        <div class="flex justify-end">
            <a href="{{ route('users.create') }}"
                class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                Add User
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[1102px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Name</p>
                            </th>
                            <th class="px-5 py-3 text-left sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Email</p>
                            </th>
                            <th class="px-5 py-3 text-left sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Role</p>
                            </th>
                            <th class="px-5 py-3 text-left sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Quyền</p>
                            </th>
                            <th class="px-5 py-3 text-left sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                            </th>
                            <th class="px-5 py-3 text-left sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Created At</p>
                            </th>
                            <th class="px-5 py-3 text-left sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Updated At</p>
                            </th>
                            <th class="px-5 py-3 text-left sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $user->name }}</p>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $user->email }}</p>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ ucfirst($user->role) }}</p>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    @if ($user->role === 'editor')
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($user->permissions as $permission)
                                                <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                                                    {{ $permission->label }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-gray-400">Chưa có quyền</span>
                                            @endforelse
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    @if ($user->status === 'active')
                                        <span class="inline-flex items-center rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-500/15 dark:text-red-400">
                                            Blocked
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $user->created_at }}</p>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $user->updated_at }}</p>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('users.edit', $user) }}"
                                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </a>
                                        @if (! $user->is(auth()->user()))
                                            <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-600 shadow-theme-xs transition hover:bg-red-50 dark:border-red-700 dark:bg-transparent dark:text-red-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6" />
                                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                        <path d="M10 11v6" />
                                                        <path d="M14 11v6" />
                                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center">
                                    <p class="text-gray-500 dark:text-gray-400">No users found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($users->hasPages())
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
