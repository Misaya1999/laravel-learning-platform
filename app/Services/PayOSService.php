<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayOSService
{
    public function configured(): bool
    {
        return filled(config('payment.payos.client_id'))
            && filled(config('payment.payos.api_key'))
            && filled(config('payment.payos.checksum_key'));
    }

    public function createPaymentLink(Order $order): Order
    {
        if (! $this->configured()) throw new RuntimeException('payOS chưa được cấu hình.');
        $order->loadMissing(['items', 'user']);
        $orderCode = $order->payos_order_code ?: $order->id;
        $amount = (int) round((float) $order->total);
        $description = 'KHP'.$orderCode;
        $cancelUrl = route('site.payos.cancel');
        $returnUrl = route('site.payos.return');
        $signatureData = "amount={$amount}&cancelUrl={$cancelUrl}&description={$description}&orderCode={$orderCode}&returnUrl={$returnUrl}";

        $payload = [
            'orderCode' => $orderCode,
            'amount' => $amount,
            'description' => $description,
            'buyerName' => $order->user?->name,
            'buyerEmail' => $order->user?->email,
            'buyerPhone' => $order->user?->phone,
            'items' => $order->items->map(fn ($item) => ['name' => mb_substr($item->course_name, 0, 100), 'quantity' => 1, 'price' => (int) round((float) $item->price)])->values()->all(),
            'cancelUrl' => $cancelUrl,
            'returnUrl' => $returnUrl,
            'expiredAt' => now()->addHours(config('payment.pending_expiration_hours', 48))->timestamp,
            'signature' => hash_hmac('sha256', $signatureData, config('payment.payos.checksum_key')),
        ];
        $payload = array_filter($payload, fn ($value) => $value !== null && $value !== '');

        $response = Http::acceptJson()
            ->withHeaders(['x-client-id' => config('payment.payos.client_id'), 'x-api-key' => config('payment.payos.api_key')])
            ->timeout(15)
            ->retry(2, 300)
            ->post(rtrim(config('payment.payos.api_url'), '/').'/v2/payment-requests', $payload);
        $response->throw();
        $body = $response->json();
        if (($body['code'] ?? null) !== '00' || empty($body['data']['checkoutUrl'])) {
            throw new RuntimeException($body['desc'] ?? 'Không thể tạo liên kết thanh toán payOS.');
        }

        $order->update([
            'payos_order_code' => $orderCode,
            'payment_link_id' => $body['data']['paymentLinkId'] ?? null,
            'checkout_url' => $body['data']['checkoutUrl'],
            'qr_code' => $body['data']['qrCode'] ?? null,
        ]);

        return $order->refresh();
    }

    public function verifyWebhook(array $data, string $signature): bool
    {
        if (! $this->configured()) return false;
        ksort($data);
        $parts = [];
        foreach ($data as $key => $value) {
            if ($value === null || in_array($value, ['undefined', 'null'], true)) $value = '';
            if (is_array($value)) {
                $value = array_map(function ($item) { if (is_array($item)) ksort($item); return $item; }, $value);
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $parts[] = $key.'='.$value;
        }
        $expected = hash_hmac('sha256', implode('&', $parts), config('payment.payos.checksum_key'));

        return hash_equals($expected, strtolower($signature));
    }
}
