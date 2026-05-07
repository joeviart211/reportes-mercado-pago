<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">Reportes — {{ $branch->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">Mercado Pago · Todas las transacciones</p>
            </div>
            <a href="{{ route('branches.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                ← Volver a sucursales
            </a>
        </div>
    </x-slot>

    <div class="p-6 space-y-6">

        {{-- Alertas --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
                {{ session('info') }}
            </div>
        @endif

        {{-- Estado de conexión --}}
        <div class="bg-white border rounded p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($branch->isConnectedToMl())
                    <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
                    <span class="text-sm text-gray-700">Conectado como <strong>{{ $branch->ml_user_id }}</strong></span>
                @else
                    <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>
                    <span class="text-sm text-gray-700">Sin conexión a Mercado Libre</span>
                @endif
            </div>
            @if(!$branch->isConnectedToMl())
                <a href="{{ route('branches.ml.connect', $branch) }}"
                   class="bg-yellow-400 hover:bg-yellow-500 text-black text-sm font-medium px-4 py-2 rounded">
                    Conectar Mercado Libre
                </a>
            @else
                @role('admin')
                    <form method="POST" action="{{ route('branches.ml.disconnect', $branch) }}">
                        @csrf @method('DELETE')
                        <button class="text-sm text-red-600 hover:underline">Desconectar</button>
                    </form>
                @endrole
            @endif
        </div>

        @if($branch->isConnectedToMl())

        {{-- Solicitar nuevo reporte --}}
        <div class="bg-white border rounded p-4">
            <h3 class="font-medium text-gray-800 mb-3">Solicitar nuevo reporte</h3>
            <form method="POST" action="{{ route('branches.reports.request', $branch) }}"
                  class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha inicio</label>
                    <input type="date" name="from" required
                           value="{{ old('from', now()->startOfMonth()->format('Y-m-d')) }}"
                           class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('from') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha fin</label>
                    <input type="date" name="to" required
                           value="{{ old('to', now()->format('Y-m-d')) }}"
                           class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('to') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded">
                    Solicitar reporte
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-2">
                MP genera el archivo en segundos. Recarga la página para ver el nuevo reporte en la lista.
            </p>
        </div>

        {{-- Lista de reportes disponibles --}}
        <div class="bg-white border rounded overflow-hidden">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h3 class="font-medium text-gray-800">Reportes disponibles</h3>
                <span class="text-xs text-gray-400">{{ count($reports) }} reportes</span>
            </div>

            @if(count($reports) === 0)
                <div class="px-4 py-10 text-center text-gray-400 text-sm">
                    No hay reportes disponibles. Solicita uno arriba.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3">Archivo</th>
                            <th class="px-4 py-3">Período</th>
                            <th class="px-4 py-3">Creado</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($reports as $report)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs text-gray-600 max-w-xs truncate">
                                    {{ $report['file_name'] }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ \Carbon\Carbon::parse($report['begin_date'])->format('d/m/Y') }}
                                    →
                                    {{ \Carbon\Carbon::parse($report['end_date'])->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ \Carbon\Carbon::parse($report['date_created'])->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($report['status'] === 'processed')
                                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">
                                            Listo
                                        </span>
                                    @elseif($report['status'] === 'pending')
                                        <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full">
                                            Procesando
                                        </span>
                                    @else
                                        <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">
                                            {{ $report['status'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($report['status'] === 'processed')
                                        @if(in_array($report['file_name'], $importedFiles))
                                            {{-- Ya importado --}}
                                            <!-- <span class="text-green-400 text-xs font-semibold">✓ Ya importado</span> -->
                                            <a href="{{ route('exportXls', [$branch, $report['file_name']]) }}"
                                                class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded inline-block">
                                                    Exportar XLS
                                            </a>
                                        @else
                                            {{-- Aún no importado --}}
                                            <form method="POST"
                                                action="{{ route('branches.reports.import', [$branch, $report['file_name']]) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded">
                                                    Importar 
                                                </button>
                                            </form>
                                        @endif

                                        
                                    @else
                                        <span class="text-gray-300 text-xs">No disponible</span>
                                    @endif

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        @endif 

    </div>
</x-app-layout>
