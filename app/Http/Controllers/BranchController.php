<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('users', 'transactions')
            ->latest()
            ->paginate(10);

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'ml_client_id'     => 'required|string|max:255',
            'ml_client_secret' => 'required|string',
            'active'           => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        // Si el slug ya existe le agrega un sufijo único
        $originalSlug = $data['slug'];
        $count = 1;
        while (Branch::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $count++;
        }

        $branch = Branch::create($data);

        return redirect()
            ->route('branches.show', $branch)
            ->with('success', "Sucursal {$branch->name} creada correctamente.");
    }

    public function show(Branch $branch)
    {
        $branch->loadCount('users', 'transactions');

        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name'             => 'sometimes|required|string|max:255',
            'ml_client_id'     => 'sometimes|required|string|max:255',
            'ml_client_secret' => 'sometimes|required|string',
            'active'           => 'boolean',
        ]);

        // Solo regenera el slug si cambió el nombre
        if (isset($data['name']) && $data['name'] !== $branch->name) {
            $newSlug = Str::slug($data['name']);
            $original = $newSlug;
            $count = 1;
            while (Branch::where('slug', $newSlug)->where('id', '!=', $branch->id)->exists()) {
                $newSlug = $original . '-' . $count++;
            }
            $data['slug'] = $newSlug;
        }

        $branch->update($data);

        return redirect()
            ->route('branches.show', $branch)
            ->with('success', "Sucursal {$branch->name} actualizada.");
    }

    public function destroy(Branch $branch)
    {
        // Evita borrar si tiene usuarios asignados
        if ($branch->users()->exists()) {
            return back()->with('error', "No se puede eliminar {$branch->name}: tiene usuarios asignados.");
        }

        $name = $branch->name;
        $branch->delete();

        return redirect()
            ->route('branches.index')
            ->with('success', "Sucursal {$name} eliminada.");
    }

    // ─── Desconectar ML/MP ────────────────────────────────────────

    public function disconnectMl(Branch $branch)
    {
        $branch->update([
            'ml_access_token'     => null,
            'ml_refresh_token'    => null,
            'ml_token_expires_at' => null,
            'ml_user_id'          => null,
        ]);

        return back()->with('success', "Mercado Libre desconectado de {$branch->name}.");
    }

    public function disconnectMp(Branch $branch)
    {
        $branch->update([
            'mp_access_token'     => null,
            'mp_refresh_token'    => null,
            'mp_token_expires_at' => null,
            'mp_user_id'          => null,
        ]);

        return back()->with('success', "Mercado Pago desconectado de {$branch->name}.");
    }
}