<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Acesso restrito a administradores.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = User::with(['subscriptions' => function ($q) {
            $q->where('status', 'active')->latest();
        }, 'subscriptions.plan']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('store_name', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $plans = Plan::where('is_active', true)->orderBy('price')->get();

        return view('admin.users', compact('users', 'plans'));
    }

    public function updatePlan(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        // Cancelar assinaturas ativas anteriores
        $user->subscriptions()->where('status', 'active')->update([
            'status' => 'canceled',
            'ends_at' => now(),
        ]);

        // Criar nova assinatura
        $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        return back()->with('success', "Plano de {$user->name} alterado para {$plan->name} com sucesso!");
    }

    public function updateFlexiCuts(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $request->validate([
            'flexi_cuts_count' => 'required|integer|min:0',
        ]);

        $user->update([
            'flexi_cuts_count' => $request->flexi_cuts_count,
        ]);

        return back()->with('success', "Usos do Gerador Flexi de {$user->name} atualizados com sucesso!");
    }

    public function suspend(Request $request, User $user)
    {
        $this->authorizeAdmin();

        if ($user->is_admin) {
            return back()->with('error', 'Não é possível suspender um administrador.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user->update([
            'suspended_at' => now(),
            'suspension_reason' => $request->reason,
            'remember_token' => null, // Invalidar "lembrar de mim"
        ]);

        // O middleware CheckSuspended forçará o logout na próxima requisição do usuário
        return back()->with('success', "Usuário {$user->name} foi suspenso.");
    }

    public function unsuspend(User $user)
    {
        $this->authorizeAdmin();

        $user->update([
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        return back()->with('success', "Suspensão de {$user->name} foi removida.");
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!\Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', 'Senha incorreta. A exclusão foi cancelada.');
        }

        if ($user->is_admin) {
            return back()->with('error', 'Não é possível deletar um administrador.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Você não pode deletar a si mesmo.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Usuário {$name} foi deletado permanentemente.");
    }
}
