<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Editar sucursal
            </h2>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                {{ $branch->name }}
            </p>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="max-w-xl">

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 dark:border-red-800 dark:bg-red-950">
                    <p class="mb-1.5 text-sm font-semibold text-red-800 dark:text-red-400">
                        Por favor corrige los siguientes errores:
                    </p>
                    <ul class="space-y-0.5 text-sm text-red-700 dark:text-red-400">
                        @foreach ($errors->all() as $error)
                            <li class="flex items-start gap-1.5">
                                <span class="mt-0.5 shrink-0">•</span>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form card --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <form method="POST" action="{{ route('branches.update', $branch) }}" class="divide-y divide-gray-100 dark:divide-gray-700">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5 px-6 py-6">

                        {{-- Nombre --}}
                        <div>
                            <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name', $branch->name) }}"
                                placeholder="Ej. Sucursal Centro"
                                autocomplete="off"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-400 @error('name') border-red-400 dark:border-red-600 @enderror"
                            >
                            @error('name')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ML Client ID --}}
                        <div>
                            <label for="ml_client_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                ML Client ID
                            </label>
                            <input
                                type="text"
                                id="ml_client_id"
                                name="ml_client_id"
                                value="{{ old('ml_client_id', $branch->ml_client_id) }}"
                                placeholder="Identificador de cliente"
                                autocomplete="off"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-400 @error('ml_client_id') border-red-400 dark:border-red-600 @enderror"
                            >
                            @error('ml_client_id')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ML Client Secret --}}
                        <div>
                            <label for="ml_client_secret" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                ML Client Secret
                            </label>
                            <div class="relative">
                                <input
                                    type="password"
                                    id="ml_client_secret"
                                    name="ml_client_secret"
                                    value="{{ old('ml_client_secret', $branch->ml_client_secret) }}"
                                    placeholder="••••••••••••"
                                    autocomplete="off"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 pr-10 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-400 @error('ml_client_secret') border-red-400 dark:border-red-600 @enderror"
                                >
                                <button
                                    type="button"
                                    onclick="toggleSecret()"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                    tabindex="-1"
                                    aria-label="Mostrar/ocultar contraseña"
                                >
                                    <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg id="icon-eye-off" xmlns="http://www.w3.org/2000/svg" class="hidden h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                            @error('ml_client_secret')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Activa toggle --}}
                        <div>
                            <input type="hidden" name="active" value="0">
                            <label class="inline-flex cursor-pointer items-center gap-3">
                                <div class="relative">
                                    <input
                                        type="checkbox"
                                        name="active"
                                        value="1"
                                        {{ old('active', $branch->active) ? 'checked' : '' }}
                                        class="peer sr-only"
                                    >
                                    <div class="h-5 w-9 rounded-full bg-gray-200 transition peer-checked:bg-blue-600 dark:bg-gray-600 peer-checked:dark:bg-blue-500"></div>
                                    <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-4"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Sucursal activa
                                </span>
                            </label>
                        </div>

                    </div>

                    {{-- Footer actions --}}
                    <div class="flex items-center justify-between px-6 py-4">

                        {{-- Danger zone: delete --}}
                        <form method="POST" action="{{ route('branches.destroy', $branch) }}"
                              onsubmit="return confirm('¿Seguro que deseas eliminar esta sucursal? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-medium text-red-600 ring-1 ring-red-300 transition hover:bg-red-50 dark:text-red-400 dark:ring-red-700 dark:hover:bg-red-950">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Eliminar sucursal
                            </button>
                        </form>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('branches.index') }}"
                               class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:text-gray-400 dark:ring-gray-600 dark:hover:bg-gray-700 dark:hover:text-white">
                                Cancelar
                            </a>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                Actualizar
                            </button>
                        </div>

                    </div>

                </form>
            </div>

        </div>
    </div>

    <script>
        function toggleSecret() {
            const input = document.getElementById('ml_client_secret');
            const eyeOn = document.getElementById('icon-eye');
            const eyeOff = document.getElementById('icon-eye-off');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            eyeOn.classList.toggle('hidden', isPassword);
            eyeOff.classList.toggle('hidden', !isPassword);
        }
    </script>

</x-app-layout>
