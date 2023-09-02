<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Проверяем, что веб-хук вызван только для main ветки

        if ($request->header('X-GitHub-Event') === 'push' &&
            $request->input('ref') === 'refs/heads/main') {
            // Выполняем скрипт обновления сервера
            $process = new Process(['/var/www/html/webhooks/deploy.sh']);
            $process->run();

            if (!$process->isSuccessful()) {
                return response('Webhook received, but deployment failed: ' . $process->getErrorOutput(), 500);
            }
        
            return response('Webhook received and deployment initiated.', 200);
        }

        return response('Webhook ignored.', 200);
    }
}