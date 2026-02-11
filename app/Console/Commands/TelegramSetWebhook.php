<?php

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:webhook {action=set : set/info/delete}';
    protected $description = 'Manage Telegram bot webhook';

    public function handle(TelegramBotService $telegram)
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'set':
                $url = config('app.url') . '/api/webhook/telegram';
                $this->info("Setting webhook to: {$url}");
                
                $result = $telegram->setWebhook($url);
                
                if ($result['ok'] ?? false) {
                    $this->info('✅ Webhook set successfully!');
                } else {
                    $this->error('❌ Failed to set webhook');
                    $this->line(json_encode($result, JSON_PRETTY_PRINT));
                }
                break;

            case 'info':
                $result = $telegram->getWebhookInfo();
                $this->info('Webhook Info:');
                $this->line(json_encode($result, JSON_PRETTY_PRINT));
                break;

            case 'delete':
                $result = $telegram->deleteWebhook();
                
                if ($result['ok'] ?? false) {
                    $this->info('✅ Webhook deleted successfully!');
                } else {
                    $this->error('❌ Failed to delete webhook');
                }
                break;

            default:
                $this->error("Unknown action: {$action}");
        }

        return 0;
    }
}
