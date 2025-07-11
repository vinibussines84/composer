@extends('layouts.authenticated')

@section('title', 'Dashboard')

@section('content')
<div class="p-6 text-white min-h-screen bg-black relative overflow-hidden">
    {{-- Brilho verde --}}
    <div class="absolute inset-0 w-full h-full pointer-events-none">
        <div class="absolute -left-1/3 top-1/4 w-2/3 h-2/3 bg-green-500 opacity-10 blur-3xl rotate-45"></div>
    </div>

    {{-- Título e descrição --}}
    <div class="mb-6 relative z-10">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <p class="text-sm text-gray-400">Veja as estatísticas mais recentes do seu negócio.</p>
    </div>

    {{-- Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6 relative z-10">
        @include('partials.stat-card', [
            'title' => 'Saldo disponível',
            'value' => 'R$ 21.202,65',
            'icon' => 'dollar-sign',
            'color' => 'green',
        ])

        @include('partials.stat-card', [
            'title' => 'Bloqueios cautelares',
            'value' => 'R$ 0,00',
            'icon' => 'shield-alert',
            'color' => 'red',
        ])

        @include('partials.stat-card', [
            'title' => 'Número de transações',
            'value' => '1742',
            'icon' => 'credit-card',
            'color' => 'blue',
        ])

        @include('partials.stat-card', [
            'title' => 'Reserva de Segurança',
            'value' => 'R$ 0,00',
            'icon' => 'shield-check',
            'color' => 'amber',
        ])

        @include('partials.stat-card', [
            'title' => 'Estornos',
            'value' => 'R$ 1.635,89',
            'sub' => 'Total: 12 estornos',
            'icon' => 'rotate-ccw',
            'color' => 'rose',
        ])
    </div>

    {{-- Gráficos --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 relative z-10">
        {{-- Gráfico de linha --}}
        <div class="rounded-xl p-6 border border-gray-700">
            <h3 class="text-lg font-semibold mb-1">Transações PIX</h3>
            <p class="text-sm text-gray-400 mb-4">Volume de transações vs devoluções/estornos</p>
            <canvas id="line-chart" height="250"></canvas>
            <p class="text-sm text-green-500 mt-4">Crescimento de 36600.0% em transações PIX ↗</p>
        </
