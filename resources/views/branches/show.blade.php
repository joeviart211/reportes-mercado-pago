<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('branches.index') }}"
                       class="text-sm text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200">
                        Sucursales
                    </a>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $branch->name }}</span>
                </div>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ $branch->name }}
                </h2>
            </div>

            <a href="{{ route('branches.edit', $branch) }}"
               class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-gray-600 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:text-gray-400 dark:ring-gray-600 dark:hover:bg-gray-700 dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H9v-2a2 2 0 01.586-1.414z"/>
                </svg>
                Editar
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-6">

        {{-- Flash message --}}
        @if(session('success'))
            <div class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Slug</p>
                <p class="mt-1 font-mono text-sm font-medium text-gray-800 dark:text-gray-200">{{ $branch->slug }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Usuarios</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $branch->users_count }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Transacciones</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($branch->transactions_count) }}</p>
            </div>
        </div>

        {{-- Integrations --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Integraciones</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Gestiona las conexiones con servicios externos</p>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-700">

                {{-- Mercado Libre --}}
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Mercado Libre</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Sincronización de catálogo y ventas</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('branches.ml.connect', $branch) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-yellow-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 015.656 0l4-4a4 4 0 01-5.656-5.656l-1.102 1.101"/>
                            </svg>
                            Conectar
                        </a>
                        <form method="POST" action="{{ route('branches.ml.disconnect', $branch) }}">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('¿Desconectar Mercado Libre de esta sucursal?')"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-medium text-red-600 ring-1 ring-red-300 transition hover:bg-red-50 dark:text-red-400 dark:ring-red-700 dark:hover:bg-red-950">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                Desconectar
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Mercado Pago --}}
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Mercado Pago</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Procesamiento de pagos y cobros</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('branches.mp.disconnect', $branch) }}">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('¿Desconectar Mercado Pago de esta sucursal?')"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-medium text-red-600 ring-1 ring-red-300 transition hover:bg-red-50 dark:text-red-400 dark:ring-red-700 dark:hover:bg-red-950">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            Desconectar
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
