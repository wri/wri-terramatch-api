<?php

namespace App\Services;

use App\Models\User as UserModel;
use App\Models\Admin as AdminModel;
use App\Models\Device as DeviceModel;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Config\Repository as Config;
use Illuminate\Log\LogManager as Log;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;

class PushService
{
    private $snsClient = null;
    private $config = null;
    private $deviceModel = null;
    private $log = null;

    public function __construct(Config $config, DeviceModel $deviceModel, Log $log)
    {
        $this->snsClient = App::make("CustomSnsClient");;
        $this->config = $config;
        $this->deviceModel = $deviceModel;
        $this->log = $log;
    }

    public function fetchEndpointArn(string $os, string $pushToken): string
    {
        if (!in_array($os, ["android", "ios"]) || empty($pushToken)) {
            throw new Exception();
        }
        $platformApplicationArn = $this->config->get("app.sns." . $os . "_arn");
        $result = $this->snsClient->createPlatformEndpoint([
            "PlatformApplicationArn" => $platformApplicationArn,
            "Token" => $pushToken
        ]);
        $endpointArn = $result->get("EndpointArn");
        if (!$endpointArn) {
            throw new Exception();
        }
        return $endpointArn;
    }

    public function sendInterestShownPush(UserModel $userModel): void
    {
        if (!$this->config->get("app.sns.enabled")) {
            return;
        }
        $devices = $this->deviceModel->where("user_id", "=", $userModel->id)->get();
        foreach ($devices as $device) {
            try {
                $this->snsClient->publish($this->buildBody($device, "Someone has shown interest in you"));
            } catch (Exception $exception) {
                $this->log->error($exception);
            }
        }
    }

    public function sendMatchPush(Model $model): void
    {
        if (!$this->config->get("app.sns.enabled")) {
            return;
        }
        if (!in_array(get_class($model), ["App\\Models\\User", "App\\Models\\Admin"])) {
            throw new InvalidArgumentException();
        }
        if (get_class($model) == "App\\Models\\User") {
            $message = "Someone has matched with you";
        } else if (get_class($model) == "App\\Models\\Admin") {
            $message = "Successful match";
        }
        $devices = $this->deviceModel->where("user_id", "=", $model->id)->get();
        foreach ($devices as $device) {
            try {
                $this->snsClient->publish($this->buildBody($device, $message));
            } catch (Exception $exception) {
                $this->log->error($exception);
            }
        }
    }

    public function sendVersionCreatedPush(AdminModel $adminModel): void
    {
        if (!$this->config->get("app.sns.enabled")) {
            return;
        }
        $devices = $this->deviceModel->where("user_id", "=", $adminModel->id)->get();
        foreach ($devices as $device) {
            try {
                $this->snsClient->publish($this->buildBody($device, "Changes requiring your approval"));
            } catch (Exception $exception) {
                $this->log->error($exception);
            }
        }
    }

    public function sendVersionApprovedPush(UserModel $userModel): void
    {
        if (!$this->config->get("app.sns.enabled")) {
            return;
        }
        $devices = $this->deviceModel->where("user_id", "=", $userModel->id)->get();
        foreach ($devices as $device) {
            try {
                $this->snsClient->publish($this->buildBody($device, "Your changes have been approved"));
            } catch (Exception $exception) {
                $this->log->error($exception);
            }
        }
    }

    public function sendVersionRejectedPush(UserModel $userModel): void
    {
        if (!$this->config->get("app.sns.enabled")) {
            return;
        }
        $devices = $this->deviceModel->where("user_id", "=", $userModel->id)->get();
        foreach ($devices as $device) {
            try {
                $this->snsClient->publish($this->buildBody($device, "Your changes have been rejected"));
            } catch (Exception $exception) {
                $this->log->error($exception);
            }
        }
    }

    private function buildBody(DeviceModel $deviceModel, string $title): array
    {
        if ($deviceModel->os == "android") {
            $message = [
                "default" => $title,
                "GCM" => json_encode([
                    "data" => [
                        "message" => $title
                    ],
                    'priority' => 'high'
                ])
            ];
        } else if ($deviceModel->os == "ios") {
            $message = [
                "default" => $title,
                "APNS" => json_encode([
                    "aps" => [
                        "alert" => $title
                    ]
                ])
            ];
        }
        return [
            "MessageStructure" => "json",
            "Message" => json_encode($message),
            "TargetArn" => $deviceModel->arn
        ];
    }
}