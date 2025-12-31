@extends('layouts.auth')

@section('title', 'Forgot Password')
@section('heading', 'Reset Password')

@section('content')
    <div class="mb-4 text-sm text-gray-600">
        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link
        that will allow you to choose a new one.
    </div>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div>
            <label for="email" class="sr-only">Email</label>
            <input id="email"
                class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Email">
        </div>

        <div class="mt-4">
            <button type="submit"
                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Email Password Reset Link
            </button>
        </div>
    </form>
@endsection