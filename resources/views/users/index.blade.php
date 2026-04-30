<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Usuarios
                </h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    Gestión de acceso y permisos del sistema
                </p>
            </div>

            <a href="{{ route('users.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo usuario
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-6">

        {{-- Flash --}}
        @if(session('success'))
            <div class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Usuario
                        </th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Roles
                        </th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Permisos directos
                        </th>
                        <th scope="col" class="px-5 py-3">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($users as $user)
                        <tr class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/30">

                            {{-- Avatar + nombre --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Roles --}}
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Sin roles</span>
                                    @endforelse
                                </div>
                            </td>

                            {{-- Permisos directos --}}
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                @if($user->permissions_count > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                        {{ $user->permissions_count }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="rounded-md px-3 py-1.5 text-xs font-medium text-blue-600 ring-1 ring-blue-300 transition hover:bg-blue-50 hover:text-blue-700 dark:text-blue-400 dark:ring-blue-700 dark:hover:bg-blue-950 dark:hover:text-blue-300">
                                        Editar
                                    </a>

                                    @unless($user->id === auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                                              onsubmit="return confirm('¿Eliminar a {{ $user->name }}? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-md px-3 py-1.5 text-xs font-medium text-red-600 ring-1 ring-red-300 transition hover:bg-red-50 dark:text-red-400 dark:ring-red-700 dark:hover:bg-red-950">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                No hay usuarios registrados todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex justify-end">
            {{ $users->links() }}
        </div>

    </div>
</x-app-layout>
