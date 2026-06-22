@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Profile">
        <x-slot:breadcrumbs>
            <li>
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">Dashboard</a>
            </li>
            <li>
                <span class="text-gray-700 dark:text-gray-400">Profile</span>
            </li>
        </x-slot:breadcrumbs>
    </x-common.page-breadcrumb>

    <x-layouts.settings title="Profile" description="Update your name and email address">
        @if (session('status'))
            <div class="mb-6">
                <x-ui.alert variant="success" :message="session('status')" />
            </div>
        @endif

        <form method="POST" action="{{ route('settings.profile.update') }}" class="space-y-6" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Avatar -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Avatar</label>
                <div class="flex items-center gap-4">
                    @if ($user->avatar)
                        <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
                            class="h-16 w-16 rounded-full object-cover">
                    @else
                        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-200 text-lg font-medium text-black dark:bg-gray-700 dark:text-white">
                            {{ $user->initials() }}
                        </span>
                    @endif
                    <input type="file" name="avatar" accept="image/*"
                        class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-500 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400">
                </div>
                @error('avatar')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Name Input -->
            <div>
                <x-forms.input
                    name="name"
                    label="Name"
                    type="text"
                    :value="$user->name"
                    required
                    autofocus
                />
            </div>

            <!-- Email Input -->
            <div>
                <x-forms.input
                    name="email"
                    label="Email"
                    type="email"
                    :value="$user->email"
                    required
                />
            </div>

            <!-- Save Button -->
            <div>
                <x-ui.button type="submit" variant="primary">
                    Save
                </x-ui.button>
            </div>
        </form>

        <!-- Delete Account Section -->
        <div class="mt-8 border-t border-gray-200 pt-8 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete account</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Delete your account and all of its resources</p>

            <form method="POST" action="{{ route('settings.profile.destroy') }}" class="mt-4"
                  onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <x-ui.button
                    type="submit"
                    variant="primary"
                    className="bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800"
                >
                    Delete account
                </x-ui.button>
            </form>
        </div>
    </x-layouts.settings>
@endsection
