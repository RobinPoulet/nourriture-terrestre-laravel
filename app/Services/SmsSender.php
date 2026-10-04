<?php

namespace App\Services;

use App\Models\Dish;
use App\Models\SmsResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envoi du récapitulatif de commande par SMS via la passerelle textbee.dev
 */
class SmsSender
{
    private const int HTTP_CODE_SUCCESS = 201;

    public function __construct(
        private readonly ?string $apiKey,
        private readonly ?string $deviceId,
        private readonly ?string $recipient,
    ) {}

    /**
     * Envoie les quantités commandées et enregistre la réponse de la passerelle
     *
     * @param  array<int, int>  $totalQuantityByDish  [dish_id => quantité]
     */
    public function sendOrderSummary(array $totalQuantityByDish, int $menuId): SmsResponse
    {
        $message = $this->formatMessage($totalQuantityByDish);
        $status = SmsResponse::STATUS_ERROR;
        $batchId = null;

        try {
            $response = Http::withHeaders(['x-api-key' => $this->apiKey])
                ->post("https://api.textbee.dev/api/v1/gateway/devices/{$this->deviceId}/send-sms", [
                    'recipients' => [$this->recipient],
                    'message' => $message,
                ]);

            if ($response->status() === self::HTTP_CODE_SUCCESS) {
                $status = SmsResponse::STATUS_SUCCESS;
                $batchId = $response->json('data.smsBatchId');
            } else {
                Log::error('Échec de l\'envoi du SMS', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (ConnectionException $e) {
            Log::error('Échec de l\'envoi du SMS : '.$e->getMessage());
        }

        return SmsResponse::query()->create([
            'message' => $message,
            'destination' => $this->recipient,
            // Colonne entière : l'identifiant de lot n'est conservé que s'il est numérique
            'sms_batch_id' => is_numeric($batchId) ? (int) $batchId : null,
            'status' => $status,
            'menu_id' => $menuId,
        ]);
    }

    /**
     * Une ligne par plat : premier mot du nom + quantité
     *
     * @param  array<int, int>  $totalQuantityByDish  [dish_id => quantité]
     */
    public function formatMessage(array $totalQuantityByDish): string
    {
        $dishes = Dish::query()->findMany(array_keys($totalQuantityByDish))->keyBy('id');

        $lines = [];
        foreach ($totalQuantityByDish as $dishId => $quantity) {
            $lines[] = ($dishes->get($dishId)?->shortName() ?? "Plat $dishId").': '.$quantity;
        }

        return "Commande MyDSO\n".implode("\n", $lines);
    }
}
