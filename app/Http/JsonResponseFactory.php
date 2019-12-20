<?php

namespace App\Http;

use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Exception;

/**
 * This class acts as a factory for creating JsonResponse objects that adhere to
 * the JSON API specification. It's intended to be injected into controllers and
 * used instead of Laravel's helper functions or native response classes.
 */
class JsonResponseFactory
{
    public function success($data, int $code, object $meta = null): JsonResponse
    {
        if (!is_array($data) && !is_object($data)) {
            throw new InvalidArgumentException();
        }
        $body = (object) [
            "data" => $data,
            "meta" => (object) []
        ];
        if ($meta) {
            $body->meta = $meta;
        } else if (is_array($body->data) || is_collection($body->data)) {
            $body->meta = (object) [
                "count" => count($body->data)
            ];
        }
        return new JsonResponse($body, $code);
    }

    public function error(array $errors, int $code): JsonResponse
    {
        if ($code < 400 || $code >= 600) {
            throw new InvalidArgumentException();
        }
        $body = (object) [
            "errors" => [],
            "meta" => (object) []
        ];
        foreach ($errors as $source => $messages) {
            foreach ($messages as $message) {
                /**
                 * This section converts our messages, which are in fact strings
                 * of JSON, into their various parts. For an explanation as to
                 * why we do this see the App\Validators\Validator class.
                 */
                $parts = json_decode($message);
                if (json_last_error() > 0 || !is_array($parts) || count($parts) != 4) {
                    throw new Exception();
                }
                $body->errors[] = (object) [
                    "source" => $source,
                    "code" => $parts[0],
                    "template" => $parts[1],
                    "variables" => $parts[2],
                    "detail" => $parts[3]
                ];
            }
        }
        $body->meta->count = count($body->errors);
        return new JsonResponse($body, $code);
    }
}