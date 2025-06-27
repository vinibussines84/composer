<?php

namespace App\Http\Controllers;

use App\Models\PixTransaction;
use App\Models\WithdrawRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::id());

        if (! $user) {
            abort(403, 'Usuário não autenticado');
        }

        // saldo e bloqueado
        $saldo = (int) $user->saldo / 100;
        $bloqueado = (int) $user->bloqueado / 100;
        $disponivel = $saldo - $bloqueado;

        // cash in hoje
        $cashInHoje = PixTransaction::where('authkey', $user->authkey)
            ->where('gtkey', $user->gtkey)
            ->where('status', 'paid')
            ->whereDate('created_at', now())
            ->get();

        $cashIn = $cashInHoje->sum('amount') / 100;
        $cashInCount = $cashInHoje->count();

        // cash out hoje
        $cashOutHoje = WithdrawRequest::where('user_id', $user->id)
            ->where('status', 'autorizado')
            ->whereDate('created_at', now())
            ->get();

        $cashOut = $cashOutHoje->sum('amount') / 100;
        $cashOutCount = $cashOutHoje->count();

        // total de taxas
        $paidTransactions = PixTransaction::where('authkey', $user->authkey)
            ->where('gtkey', $user->gtkey)
            ->where('status', 'paid')
            ->get();

        $totalTaxas = $paidTransactions->sum(function ($tx) use ($user) {
            $taxa = $tx->balance_type == 1
                ? $user->taxa_cash_in
                : $user->taxa_cash_out;
            return ($tx->amount / 100) * ($taxa / 100);
        });

        return view('dashboard', [
            'disponivel' => $disponivel,
            'bloqueado' => $bloqueado,
            'cashIn' => $cashIn,
            'cashOut' => $cashOut,
            'cashInCount' => $cashInCount,
            'cashOutCount' => $cashOutCount,
            'totalTaxas' => $totalTaxas,
        ]);
    }
}
