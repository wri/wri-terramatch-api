<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalyticsEventService
{
    public function sendEvent(string $modelUuid, string $eventName, array $params)
    {
        if (! $this->enabled()) {
            return;
        }

        $response = Http::post($this->getAnalyticsUrl(), [
            'client_id' => $modelUuid,
            'events' => [
                [
                    'name' => $eventName,
                    'params' => $params,
                ],
            ],
        ]);
        if (! $response->successful()) {
            Log::error('Failed to send GA event: ' . $response->body());
        }
    }

    protected function enabled()
    {
        return ! empty($this->getAnalyticsUrl());
    }

    protected $_analyticsUrl;

    protected function getAnalyticsUrl()
    {
        if (! empty($this->_analyticsUrl)) {
            return $this->_analyticsUrl;
        }

        $apiSecret = env('GA_API_SECRET');
        $measurementId = env('GA_MEASUREMENT_ID');
        if (empty($apiSecret) || empty($measurementId)) {
            return null;
        }

        return $this->_analyticsUrl = sprintf(
            'https://www.google-analytics.com/mp/collect?measurement_id=%s&api_secret=%s',
            $measurementId,
            $apiSecret
        );
    }
}
