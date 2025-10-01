<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Obtener el usuario
$user = App\Models\User::where('email', 'hugomgg@gmail.com')->first();

if (!$user) {
    echo "Usuario no encontrado\n";
    exit(1);
}

echo "Usuario encontrado: {$user->nombre} {$user->apellido}\n";
echo "Email: {$user->email}\n\n";

// Generar token de prueba
$token = 'test-token-' . bin2hex(random_bytes(20));

echo "Enviando notificación de recuperación de contraseña...\n";
echo "Token: {$token}\n\n";

// Enviar notificación
$user->sendPasswordResetNotification($token);

echo "✅ Notificación enviada correctamente!\n\n";
echo "📧 Revisa el email en: storage/logs/laravel.log\n";
echo "🔍 Busca las líneas más recientes con el asunto: 'Recuperación de Contraseña'\n";
