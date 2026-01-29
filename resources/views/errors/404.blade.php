<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center bg-white shadow-xl rounded-2xl p-10">
        
        <div class="flex justify-center mb-6">
            <svg class="w-24 h-24 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.054 3.378c.866-1.5 3.032-1.5 3.898 0l7.351 12.998z" />
            </svg>
        </div>

        <h1 class="text-7xl font-extrabold text-gray-800">404</h1>

        <p class="text-xl font-semibold text-gray-700 mt-4">
            Halaman Tidak Ditemukan
        </p>

        <p class="text-gray-500 mt-2">
            URL yang Anda akses tidak tersedia atau sudah dipindahkan.
        </p>

        <div class="mt-8 flex justify-center gap-4">
            <a href="{{ url('/') }}"
               class="px-6 py-3 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                Dashboard
            </a>

            <a href="{{ url()->previous() }}"
               class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                Kembali
            </a>
        </div>
    </div>
</body>
</html>