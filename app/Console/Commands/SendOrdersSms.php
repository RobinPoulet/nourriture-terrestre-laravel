<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Models\Order;
use App\Services\SmsSender;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Pusher\Pusher;
use Throwable;

/**
 * Envoie par SMS le récapitulatif des commandes du jour, puis prévient la page des commandes via Pusher
 * (remplace cron/send_sms_cron.php)
 */
#[Signature('sms:send-orders')]
#[Description('Envoie par SMS le récapitulatif des commandes du jour')]
class SendOrdersSms extends Command
{
    public function handle(SmsSender $sender): int
    {
        $totalQuantityByDish = Order::totalQuantityByDish();
        $lastMenu = Menu::query()->latest('id')->first();

        if (! $lastMenu?->is_open || empty($totalQuantityByDish)) {
            $this->info('Aucune commande à envoyer.');

            return self::SUCCESS;
        }

        $smsResponse = $sender->sendOrderSummary($totalQuantityByDish, $lastMenu->id);
        $summary = $smsResponse->summary();
        $this->info($summary['message'] ?? 'SMS traité');

        $this->notifyOrdersPage($summary);

        return $summary['status'] === 'success' ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Affiche le statut d'envoi en direct sur la page des commandes
     */
    private function notifyOrdersPage(?array $summary): void
    {
        $config = config('services.pusher');
        if (empty($config['key']) || $summary === null) {
            return;
        }

        try {
            (new Pusher($config['key'], $config['secret'], $config['app_id'], ['cluster' => $config['cluster'], 'useTLS' => true]))
                ->trigger('send-sms', 'send-sms', [
                    'message' => $summary['message'],
                    'status' => $summary['status'] === 'success' ? 'success' : 'danger',
                ]);
        } catch (Throwable $e) {
            $this->error('Notification Pusher impossible : '.$e->getMessage());
            report($e);
        }
    }
}
