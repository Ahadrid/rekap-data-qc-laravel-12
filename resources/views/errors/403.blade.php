<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center bg-white shadow-xl rounded-2xl p-10">

        <div class="flex justify-center mb-6">
            <svg class="w-24 h-24 text-yellow-500" fill="none" stroke="currentColor" stroke-width="1.5"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 15v2m0-8v3m-7.5 8.25h15A2.25 2.25 0 0021.75 18V6A2.25 2.25 0 0019.5 3.75h-15A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>

        <h1 class="text-6xl font-extrabold text-gray-800">403</h1>

        <p class="text-xl font-semibold text-gray-700 mt-4">
            Akses Ditolak
        </p>

        <p class="text-gray-500 mt-2">
            Anda tidak memiliki izin untuk mengakses halaman ini.
        </p>

        <div class="mt-8 flex justify-center gap-4">
            <a href="{{ url()->previous() }}"
               class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                Kembali
            </a>

            <a href="{{ url('/') }}"
               class="px-6 py-3 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                Dashboard
            </a>
        </div>
    </div>
</body>
</html>
