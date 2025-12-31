<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pending Approval</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
        <h1 class="text-2xl font-bold mb-4 text-yellow-600">Pending Approval</h1>
        <p class="text-gray-600 mb-6">
            Your account is currently waiting for administrator approval.
            You will receive an email once your account has been approved.
        </p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-indigo-600 hover:text-indigo-800 font-medium">
                Logout
            </button>
        </form>
    </div>
</body>

</html>