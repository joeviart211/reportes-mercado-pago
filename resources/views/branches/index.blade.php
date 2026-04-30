<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Sucursales</h2>
    </x-slot>

    <div class="p-6">
        <a href="{{ route('branches.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">
            Nueva sucursal
        </a>

        @if(session('success'))
            <div class="mt-4 text-green-600">{{ session('success') }}</div>
        @endif

        <table class="mt-4 w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2">Nombre</th>
                    <th>Usuarios</th>
                    <th>Transacciones</th>
                    <th>Activa</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $branch)
                    <tr class="border-t">
                        <td class="p-2">{{ $branch->name }}</td>
                        <td>{{ $branch->users_count }}</td>
                        <td>{{ $branch->transactions_count }}</td>
                        <td>{{ $branch->active ? 'Sí' : 'No' }}</td>
                        <td>
                            <a href="{{ route('branches.show', $branch) }}">Ver</a> |
                            <a href="{{ route('branches.edit', $branch) }}">Editar</a>
                            <a href="{{ route('branches.reports.index', $branch) }}">
                                Ver reportes
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $branches->links() }}
        </div>
    </div>
</x-app-layout>