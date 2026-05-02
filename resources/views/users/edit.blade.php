<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('users.index') }}"
                       class="text-sm text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200">
                        Usuarios
                    </a>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $user->name }}</span>
                </div>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Editar usuario
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="max-w-2xl space-y-6">

            {{-- Flash --}}
            @if(session('success'))
                <div class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Errores de validación --}}
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 dark:border-red-800 dark:bg-red-950">
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

            {{-- ── FORM 1: Datos básicos ── --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <form method="POST" action="{{ route('users.update', $user) }}" class="divide-y divide-gray-100 dark:divide-gray-700">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Datos del usuario</h3>
                    </div>

                    <div class="space-y-5 px-6 py-6">

                        <div>
                            <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name', $user->name) }}" autocomplete="off"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-400 @error('name') border-red-400 dark:border-red-600 @enderror">
                            @error('name')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Correo electrónico <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email', $user->email) }}" autocomplete="off"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-400 @error('email') border-red-400 dark:border-red-600 @enderror">
                            @error('email')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nueva contraseña
                                    <span class="font-normal text-gray-400">(opcional)</span>
                                </label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" placeholder="••••••••"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 pr-10 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-400 @error('password') border-red-400 dark:border-red-600 @enderror">
                                    <button type="button" onclick="togglePassword('password', 'eye-pass', 'eye-off-pass')"
                                            tabindex="-1" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                        @include('users._eye-icons', ['eyeId' => 'eye-pass', 'eyeOffId' => 'eye-off-pass'])
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Confirmar contraseña
                                </label>
                                <div class="relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 pr-10 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-400">
                                    <button type="button" onclick="togglePassword('password_confirmation', 'eye-conf', 'eye-off-conf')"
                                            tabindex="-1" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                        @include('users._eye-icons', ['eyeId' => 'eye-conf', 'eyeOffId' => 'eye-off-conf'])
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4">
                        <a href="{{ route('users.index') }}"
                           class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:text-gray-400 dark:ring-gray-600 dark:hover:bg-gray-700 dark:hover:text-white">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Guardar cambios
                        </button>
                    </div>

                </form>
            </div>

            {{-- ── FORM 2: Roles ── --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <form method="POST" action="{{ route('users.roles.sync', $user) }}" class="divide-y divide-gray-100 dark:divide-gray-700">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Rol</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            El usuario heredará todos los permisos del rol seleccionado
                        </p>
                    </div>

                    <div class="px-6 py-5">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach($roles as $role)
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4 transition
                                    hover:border-indigo-300 hover:bg-indigo-50/40
                                    dark:border-gray-600 dark:hover:border-indigo-600 dark:hover:bg-indigo-900/20
                                    has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50
                                    dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-900/30">
                                    <input type="checkbox"
                                           name="roles[]"
                                           value="{{ $role->name }}"
                                           {{ in_array($role->name, $userRoles) ? 'checked' : '' }}
                                           class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                                    <div>
                                        <span class="block text-sm font-semibold capitalize text-gray-800 dark:text-gray-200">
                                            {{ $role->name }}
                                        </span>
                                        <span class="mt-0.5 block text-xs text-gray-400 dark:text-gray-500">
                                            {{ $role->permissions->pluck('name')->map(fn($p) => explode('.', $p)[1] ?? $p)->implode(', ') ?: 'Sin permisos' }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end px-6 py-4">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Actualizar rol
                        </button>
                    </div>

                </form>
            </div>

            {{-- ── FORM 3: Permisos directos ── --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <form method="POST" action="{{ route('users.permissions.sync', $user) }}" class="divide-y divide-gray-100 dark:divide-gray-700">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Permisos directos</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Permisos adicionales independientes del rol asignado
                        </p>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($permissions as $group => $groupPermissions)
                            <div class="px-6 py-4">
                                <div class="mb-3 flex items-center justify-between">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                        {{ $group }}
                                    </p>
                                    <button type="button"
                                            onclick="toggleGroup('perm-{{ $loop->index }}')"
                                            class="text-xs text-blue-600 hover:underline dark:text-blue-400">
                                        Todos
                                    </button>
                                </div>
                                <div id="perm-{{ $loop->index }}" class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                    @foreach($groupPermissions as $permission)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permission->name }}"
                                                   {{ in_array($permission->name, $userPermissions) ? 'checked' : '' }}
                                                   class="h-4 w-4 rounded border-gray-300 text-amber-500 focus:ring-amber-400 dark:border-gray-600 dark:bg-gray-700">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ explode('.', $permission->name)[1] ?? $permission->name }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end px-6 py-4">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Actualizar permisos
                        </button>
                    </div>

                </form>
            </div>

            {{-- ── Danger zone ── --}}
            @unless($user->id === auth()->id())
                <div class="rounded-xl border border-red-200 bg-white shadow-sm dark:border-red-900 dark:bg-gray-800">
                    <div class="border-b border-red-100 px-6 py-4 dark:border-red-900">
                        <h3 class="text-sm font-semibold text-red-600 dark:text-red-400">Zona de peligro</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Estas acciones son permanentes e irreversibles
                        </p>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Eliminar usuario</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Se eliminarán todos los datos asociados a {{ $user->name }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                              onsubmit="return confirm('¿Eliminar a {{ $user->name }}? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Eliminar usuario
                            </button>
                        </form>
                    </div>
                </div>
            @endunless

        </div>
    </div>

    <script>
        function togglePassword(inputId, eyeId, eyeOffId) {
            const input  = document.getElementById(inputId);
            const eyeOn  = document.getElementById(eyeId);
            const eyeOff = document.getElementById(eyeOffId);
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            eyeOn.classList.toggle('hidden', isPass);
            eyeOff.classList.toggle('hidden', !isPass);
        }

        function toggleGroup(id) {
            const boxes      = document.getElementById(id).querySelectorAll('input[type="checkbox"]');
            const allChecked = [...boxes].every(b => b.checked);
            boxes.forEach(b => b.checked = !allChecked);
        }
    </script>

</x-app-layout>
