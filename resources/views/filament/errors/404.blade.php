<x-filament::page>
    <div class="flex flex-col items-center justify-center py-24">
        <h1 class="text-6xl font-bold text-danger-600">404</h1>

        <p class="mt-4 text-lg font-semibold">
            Halaman Admin Tidak Ditemukan
        </p>

        <p class="text-gray-500 mt-2">
            Menu atau URL yang Anda akses tidak tersedia.
        </p>

        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.pages.dashboard') }}"
            class="mt-6">
            Kembali ke Dashboard
        </x-filament::button>
    </div>
</x-filament::page>
