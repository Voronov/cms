@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 text-center">Two Factor Authentication</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
        @if(! auth()->user()->two_factor_secret)
            <div class="space-y-6">
                <p class="text-gray-600 dark:text-gray-400">
                    Two-factor authentication adds an extra layer of security to your account. To enable it, you'll need to use an authenticator app like Google Authenticator or Microsoft Authenticator.
                </p>

                <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Enable Two-Factor Authentication
                    </button>
                </form>
            </div>
        @else
            <div class="space-y-6">
                @if(session('status') == 'two-factor-authentication-enabled')
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>Two-factor authentication is enabled. Please scan the QR code and provide the code to confirm.</p>
                    </div>
                @endif

                @if(! auth()->user()->two_factor_confirmed_at)
                    <div class="space-y-4">
                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                            Finish enabling two-factor authentication.
                        </p>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                            To finish enabling two-factor authentication, scan the following QR code using your phone's authenticator application and provide the generated OTP code.
                        </p>

                        <div class="p-4 bg-white inline-block rounded-lg shadow-sm">
                            {!! auth()->user()->twoFactorQrCodeSvg() !!}
                        </div>

                        <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                                <input type="text" name="code" id="code" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required autofocus autocomplete="one-time-code">
                                @error('code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition duration-150">
                                Confirm
                            </button>
                        </form>
                    </div>
                @else
                    <div class="space-y-4">
                        <p class="font-semibold text-gray-900 dark:text-gray-100 text-green-600">
                            Two-factor authentication is enabled and confirmed.
                        </p>
                        
                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                            Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost.
                        </p>

                        <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 dark:bg-gray-900 dark:text-gray-300 rounded-lg">
                            @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                                <div>{{ $code }}</div>
                            @endforeach
                        </div>

                        <div class="flex space-x-4">
                            <form method="POST" action="{{ url('/user/two-factor-recovery-codes') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    Regenerate Recovery Codes
                                </button>
                            </form>

                            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition duration-150">
                                    Disable Two-Factor Authentication
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
