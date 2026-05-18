@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Create User" />

    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    Add internal user
                </h3>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="p-6">
                @csrf

                <div>
                    <x-forms.input label="Name" name="name" required autofocus />
                </div>

                <div class="mt-4">
                    <x-forms.input label="Email" name="email" type="email" required />
                </div>

                <div class="mt-4">
                    <label for="role" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Role</label>
                    <select id="role" name="role"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @foreach (['admin' => 'Admin', 'editor' => 'Editor', 'customer' => 'Customer'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', 'editor') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <x-forms.input label="Password" name="password" type="password" required />
                </div>

                <div class="mt-4">
                    <x-forms.input label="Confirm Password" name="password_confirmation" type="password" required />
                </div>

                <div class="mt-4 w-full px-2.5">
                    <div class="mt-1 flex items-center gap-3">
                        <button type="submit"
                            class="bg-brand-500 hover:bg-brand-600 flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white">
                            Create User
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
