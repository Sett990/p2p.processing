<?php

namespace App\Services\AntiFraud;

use App\Contracts\AntiFraudServiceContract;
use App\Enums\OrderStatus;
use App\Enums\TrafficType;
use App\Exceptions\AntiFraudException;
use App\Models\AntiFraudSetting;
use App\Models\Merchant;
use App\Models\MerchantClient;
use App\Models\Order;
use Illuminate\Support\Carbon;

class AntiFraudService implements AntiFraudServiceContract
{
    public function check(Merchant $merchant, ?string $clientId): ?MerchantClient
    {
        $settings = services()->antiFraudSetting()->getForMerchant($merchant->id);

        if (! $settings || ! $settings->enabled) {
            return null;
        }

        $clientId = trim((string) $clientId);
        if ($clientId === '') {
            throw AntiFraudException::clientIdRequired();
        }

        $client = MerchantClient::query()->firstOrCreate(
            ['merchant_id' => $merchant->id, 'client_id' => $clientId],
            ['traffic_type' => TrafficType::PRIMARY]
        );

        $this->syncTrafficType($client);

        if ($client->blocked_until && $client->blocked_until->isFuture()) {
            throw AntiFraudException::blockedUntil($client->blocked_until->toDateTimeString());
        }

        $trafficType = $client->traffic_type ?? TrafficType::PRIMARY;

        $this->checkMaxPending($client, $settings, $trafficType);
        $this->checkRateLimits($client, $settings, $trafficType);
        $this->checkFailedLimit($client, $settings, $trafficType);

        return $client;
    }

    private function syncTrafficType(MerchantClient $client): void
    {
        if ($client->traffic_type === TrafficType::SECONDARY) {
            return;
        }

        $hasSuccess = Order::query()
            ->where('merchant_client_id', $client->id)
            ->where('status', OrderStatus::SUCCESS)
            ->exists();

        if ($hasSuccess) {
            $client->traffic_type = TrafficType::SECONDARY;
            $client->save();
        }
    }

    private function checkMaxPending(
        MerchantClient $client,
        AntiFraudSetting $settings,
        TrafficType $trafficType
    ): void {
        $limit = $trafficType === TrafficType::PRIMARY
            ? $settings->primary_max_pending
            : $settings->secondary_max_pending;

        if (! $limit) {
            return;
        }

        $pendingCount = Order::query()
            ->where('merchant_client_id', $client->id)
            ->where('status', OrderStatus::PENDING)
            ->count();

        if ($pendingCount >= $limit) {
            throw AntiFraudException::maxPendingExceeded();
        }
    }

    private function checkRateLimits(
        MerchantClient $client,
        AntiFraudSetting $settings,
        TrafficType $trafficType
    ): void {
        $limits = $trafficType === TrafficType::PRIMARY
            ? ($settings->primary_rate_limits ?? [])
            : ($settings->secondary_rate_limits ?? []);

        if (! $limits) {
            return;
        }

        foreach ($limits as $limit) {
            $count = (int) ($limit['count'] ?? 0);
            $minutes = (int) ($limit['minutes'] ?? 0);

            if ($count <= 0 || $minutes <= 0) {
                continue;
            }

            $since = Carbon::now()->subMinutes($minutes);
            $createdCount = Order::query()
                ->where('merchant_client_id', $client->id)
                ->where('created_at', '>=', $since)
                ->count();

            if ($createdCount >= $count) {
                throw AntiFraudException::rateLimitExceeded($count, $minutes);
            }
        }
    }

    private function checkFailedLimit(
        MerchantClient $client,
        AntiFraudSetting $settings,
        TrafficType $trafficType
    ): void {
        $limit = $trafficType === TrafficType::PRIMARY
            ? $settings->primary_failed_limit
            : $settings->secondary_failed_limit;

        if (! $limit) {
            return;
        }

        $recentOrdersQuery = Order::query()
            ->where('merchant_client_id', $client->id)
            ->whereIn('status', [OrderStatus::SUCCESS, OrderStatus::FAIL])
            ->orderByDesc('created_at')
            ->limit($limit);

        if ($client->blocked_until) {
            $recentOrdersQuery->where('created_at', '>=', $client->blocked_until);
        }

        $recentOrders = $recentOrdersQuery->get();
        if ($recentOrders->count() < $limit) {
            return;
        }

        $allFailed = $recentOrders->every(fn (Order $order) => $order->status->equals(OrderStatus::FAIL));
        if (! $allFailed) {
            return;
        }

        $blockDays = $trafficType === TrafficType::PRIMARY
            ? $settings->primary_block_days
            : $settings->secondary_block_days;

        if ($blockDays && $blockDays > 0) {
            $client->blocked_until = now()->addDays($blockDays);
            $client->save();
        }

        throw AntiFraudException::failedLimitExceeded();
    }
}
