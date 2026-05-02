<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Sucursales
                </h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    Gestión y seguimiento de todas las sucursales
                </p>
            </div>
            @role('admin')
                <a href="{{ route('branches.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva sucursal
                </a>
            @endrole

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

        {{-- Table card --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Nombre
                        </th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Usuarios
                        </th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Transacciones
                        </th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Estado
                        </th>
                        <th scope="col" class="px-5 py-3">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($branches as $branch)
                        <tr class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $branch->name }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $branch->users_count }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ number_format($branch->transactions_count) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                @if($branch->active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/40 dark:text-green-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Activa
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                        Inactiva
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                   
                                    <a href="{{ route('branches.show', $branch) }}"
                                       class="rounded-md px-3 py-1.5 text-xs font-medium text-gray-600 ring-1 ring-gray-300 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:ring-gray-600 dark:hover:bg-gray-700 dark:hover:text-white">
                                        Ver
                                    </a>
                                    @role('admin')
                                        <a href="{{ route('branches.edit', $branch) }}"
                                        class="rounded-md px-3 py-1.5 text-xs font-medium text-blue-600 ring-1 ring-blue-300 transition hover:bg-blue-50 hover:text-blue-700 dark:text-blue-400 dark:ring-blue-700 dark:hover:bg-blue-950 dark:hover:text-blue-300">
                                            Editar
                                        </a>
                                    @endrole
                                    @role('admin')
                                        <a href="{{ route('branches.reports.index', $branch) }}"
                                        class="rounded-md px-3 py-1.5 text-xs font-medium text-indigo-600 ring-1 ring-indigo-300 transition hover:bg-indigo-50 hover:text-indigo-700 dark:text-indigo-400 dark:ring-indigo-700 dark:hover:bg-indigo-950 dark:hover:text-indigo-300">
                                            Reportes
                                        </a>
                                    @endrole                                    
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                No hay sucursales registradas todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex justify-end">
            {{ $branches->links() }}
        </div>

    </div>
</x-app-layout>
