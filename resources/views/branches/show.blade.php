<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">{{ $branch->name }}</h2>
    </x-slot>

    <div class="p-6">

        @if(session('success'))
            <div class="text-green-600 mb-3">{{ session('success') }}</div>
        @endif

        <p><strong>Slug:</strong> {{ $branch->slug }}</p>
        <p><strong>Usuarios:</strong> {{ $branch->users_count }}</p>
        <p><strong>Transacciones:</strong> {{ $branch->transactions_count }}</p>

        <hr class="my-4">

        

        {{-- 🔗 Conectar Mercado Libre --}}
        <a href="{{ route('branches.ml.connect', $branch) }}"
           class="bg-yellow-500 text-white px-4 py-2 rounded">
            Conectar Mercado Libre
        </a>

        {{-- ❌ Desconectar ML --}}
        <form method="POST" action="{{ route('branches.ml.disconnect', $branch) }}" class="inline">
            @csrf
            <button class="bg-red-500 text-white px-3 py-2 rounded">
                Desconectar ML
            </button>
        </form>

    

        <hr class="my-4">

        {{-- 💳 Mercado Pago --}}
        <form method="POST" action="{{ route('branches.mp.disconnect', $branch) }}">
            @csrf
            <button class="bg-red-600 text-white px-3 py-2 rounded">
                Desconectar MP
            </button>
        </form>

        <hr class="my-4">

        <a href="{{ route('branches.index') }}" class="text-blue-600">← Volver</a>
    </div>
</x-app-layout>