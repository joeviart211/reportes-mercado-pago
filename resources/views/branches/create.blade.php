<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Crear sucursal</h2>
    </x-slot>

    <div class="p-6">

        @if ($errors->any())
            <div class="mb-4 text-red-600">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('branches.store') }}">
            @csrf

            <input type="text" name="name"
                   value="{{ old('name') }}"
                   placeholder="Nombre"
                   class="border p-2 w-full mb-3">

            <input type="text" name="ml_client_id"
                   value="{{ old('ml_client_id') }}"
                   placeholder="ML Client ID"
                   class="border p-2 w-full mb-3">

            <input type="text" name="ml_client_secret"
                   value="{{ old('ml_client_secret') }}"
                   placeholder="ML Client Secret"
                   class="border p-2 w-full mb-3">

            <input type="hidden" name="active" value="0">
            <label>
                <input type="checkbox" name="active" value="1"
                    {{ old('active') ? 'checked' : '' }}>
                Activa
            </label>

            <button class="bg-green-600 text-white px-4 py-2 rounded mt-3">
                Guardar
            </button>
        </form>
    </div>
</x-app-layout>