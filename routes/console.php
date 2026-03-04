<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\ModelerRequest;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    ModelerRequest::where('created_at', '<', now()->subDays(7))->delete();
})->daily();

// Expirar assinaturas vencidas e rebaixar para o plano gratuito
Schedule::command('subscriptions:expire')->daily();
