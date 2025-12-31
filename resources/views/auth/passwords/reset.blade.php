@extends('layouts.auth')

@section('title', 'Reset Password')
@section('heading', 'Reset Password')

@section('content')
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="space-y-4">
            <div>
                <label for="email" class="sr-only">Email</label>
                <input id="email"
                    class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                    placeholder="Email">
            </div>

            <div>
                <label for="password" class="sr-only">Password</label>
                <input id="password"
                    class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    type="password" name="password" required autocomplete="new-password" placeholder="Password">
            </div>

            <div>
                <label for="password_confirmation" class="sr-only">Confirm Password</label>
                <input id="password_confirmation"
                    class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    type="password" name="password_confirmation" required autocomplete="new-password"
                    placeholder="Confirm Password">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit"
                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Reset Password
            </button>
        </div>
    </form>
@endsection