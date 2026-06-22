@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit User" />

    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    Edit: {{ $user->name }}
                </h3>
            </div>

            @php($isSelf = $user->is(auth()->user()))

            <form action="{{ route('users.update', $user) }}" method="POST" class="p-6" x-data="{ role: '{{ old('role', $user->role) }}' }">
                @csrf
                @method('PUT')

                @if ($isSelf)
                    <div>
                        <x-forms.input label="Name" name="name" :value="$user->name" required />
                    </div>

                    <div class="mt-4">
                        <x-forms.input label="Email" name="email" :value="$user->email" required />
                    </div>

                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        Bạn không thể tự thay đổi vai trò, quyền hạn hoặc trạng thái của chính mình tại đây.
                    </p>
                @else
                    <div>
                        <p class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Name</p>
                        <p class="text-sm text-gray-800 dark:text-white/90">{{ $user->name }}</p>
                    </div>

                    <div class="mt-4">
                        <p class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email</p>
                        <p class="text-sm text-gray-800 dark:text-white/90">{{ $user->email }}</p>
                    </div>

                    <div class="mt-4">
                        <label for="role" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Role</label>
                        <select id="role" name="role" x-model="role"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @foreach (['admin' => 'Admin', 'editor' => 'Editor', 'customer' => 'Customer'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4" x-show="role === 'editor'" x-cloak>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Quyền thao tác phía quản trị
                        </label>
                        <div class="space-y-4 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            @foreach ($permissions as $group => $items)
                                <div>
                                    <p class="mb-2 text-xs font-semibold text-gray-400 uppercase">{{ ucfirst($group) }}</p>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                        @foreach ($items as $permission)
                                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                    @checked(in_array($permission->id, old('permissions', $user->permissions->pluck('id')->all())))>
                                                {{ $permission->label }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('permissions')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                        <select id="status" name="status"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @foreach (['active' => 'Active', 'blocked' => 'Blocked'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $user->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="mt-4 w-full px-2.5">
                    <div class="mt-1 flex items-center gap-3">
                        <button type="submit" class="bg-brand-500 hover:bg-brand-600 flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white">
                            Update User
                        </button>
    
                        <a href="{{ route('users.index') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
