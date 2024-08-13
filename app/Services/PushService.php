<?php

namespace App\Services;

use App\Models\Device as DeviceModel;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PushService
{
    private $snsClient = null;

    public function __construct()
    {
        $this->snsClient = App::make('CustomSnsClient');
    }

    public function fetchEndpointArn(string $os, string $pushToken): ?string
    {
        if (! config('app.sns.enabled')) {
            return null;
        }

        if (! in_array($os, ['android', 'ios']) || empty($pushToken)) {
            throw new Exception();
        }
        $platformApplicationArn = config('app.sns.' . $os . '_arn');
        $result = $this->snsClient->createPlatformEndpoint([
            'PlatformApplicationArn' => $platformApplicationArn,
            'Token' => $pushToken,
        ]);
        $endpointArn = $result->get('EndpointArn');
        if (! $endpointArn) {
            throw new Exception();
        }

        return $endpointArn;
    }

    public function sendPush(
        Model $model,
        String $message,
        String $action,
        String $referencedModel = null,
        Int $referencedModelId = null
    ): void {
        if (! config('app.sns.enabled')) {
            return;
        }
        if (! get_class($model) == \App\Models\V2\User::class) {
            throw new InvalidArgumentException();
        }
        $devices = DeviceModel::where('user_id', '=', $model->id)->get();
        foreach ($devices as $device) {
            try {
                $this->snsClient->publish($this->buildBody($device, $message, $action, $referencedModel, $referencedModelId));
            } catch (Exception $exception) {
                Log::error($exception);
            }
        }
    }

    private function buildBody(
        DeviceModel $deviceModel,
        String $title,
        String $action,
        ?String $referencedModel,
        ?Int $referencedModelId
    ): array {
        if ($deviceModel->os == 'android') {
            $message = [
                'default' => $title,
                'GCM' => json_encode([
                    'data' => [
                        'message' => $title,
                        'action' => $action,
                        'referenced_model' => $referencedModel,
                        'referenced_model_id' => $referencedModelId,
                    ],
                    'priority' => 'high',
                ]),
            ];
        } elseif ($deviceModel->os == 'ios') {
            $message = [
                'default' => $title,
                'APNS' => json_encode([
                    'aps' => [
                        'alert' => $title,
                    ],
                    'action' => $action,
                    'referenced_model' => $referencedModel,
                    'referenced_model_id' => $referencedModelId,
                ]),
            ];
        }

        return [
            'MessageStructure' => 'json',
            'Message' => json_encode($message),
            'TargetArn' => $deviceModel->arn,
        ];
    }
}
