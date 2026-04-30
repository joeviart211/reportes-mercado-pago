<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Editar sucursal</h2>
    </x-slot>

    <div class="p-6">
        <form method="POST" action="{{ route('branches.update', $branch) }}">
            @csrf
            @method('PUT')

            <input type="text" name="name" value="{{ $branch->name }}" class="border p-2 w-full mb-3">

            <input type="text" name="ml_client_id" value="{{ $branch->ml_client_id }}" class="border p-2 w-full mb-3">

            <input type="text" name="ml_client_secret" value="{{ $branch->ml_client_secret }}" class="border p-2 w-full mb-3">

            <label>
                <input type="checkbox" name="active" value="1" {{ $branch->active ? 'checked' : '' }}>
                Activa
            </label>

            <button class="bg-blue-600 text-white px-4 py-2 rounded mt-3">
                Actualizar
            </button>
        </form>
    </div>
</x-app-layout>