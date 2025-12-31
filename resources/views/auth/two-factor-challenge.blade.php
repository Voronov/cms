@extends('layouts.admin')

@section('content')
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Please confirm access to your account by entering the authentication code provided by your authenticator application.
        </div>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/two-factor-challenge') }}">
            @csrf

            <div class="mt-4" x-data="{ recovery: false }">
                <div x-show="! recovery">
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                    <input type="text" name="code" id="code" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" inputmode="numeric" autofocus autocomplete="one-time-code">
                </div>

                <div x-show="recovery" style="display: none;">
                    <label for="recovery_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recovery Code</label>
                    <input type="text" name="recovery_code" id="recovery_code" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" autocomplete="one-time-code">
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="button" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 underline cursor-pointer"
                                    x-show="! recovery"
                                    x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })">
                        Use a recovery code
                    </button>

                    <button type="button" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 underline cursor-pointer"
                                    x-show="recovery"
                                    x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                        Use an authentication code
                    </button>

                    <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition duration-150">
                        Log in
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
