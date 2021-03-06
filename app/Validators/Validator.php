<?php

namespace App\Validators;

use InvalidArgumentException;
use Illuminate\Support\Facades\Validator as BaseValidatorFactory;
use Illuminate\Validation\ValidationException;

/**
 * This class adds some features on top of Laravel's validators. This class
 * makes the following requirements possible:
 *
 * 1. Extra fields are not blindly accepted. We throw errors for any unnecessary
 *    fields. This is important considering our models will mass assign any
 *    attribute.
 * 2. We can add custom rules in one place. We don't have to generate validators
 *    inside controllers and copy the list of rules everywhere.
 * 3. Our validators can be used inside commands as well. If we were to use
 *    custom requests we couldn't make use of those in commands (or jobs).
 * 4. We can customise the error messages. Our front end needs to build the
 *    error in multiple languages. This is achieved by providing an object with
 *    properties (instead of a string) to the front end. The FEDs can then do
 *    their thing.
 */
class Validator
{
    public const EXTENSIONS = [
        "App\\Validators\\Extensions\\CountryCode",
        "App\\Validators\\Extensions\\OrganisationType",
        "App\\Validators\\Extensions\\OrganisationCategory",
        "App\\Validators\\Extensions\\ContainUpper",
        "App\\Validators\\Extensions\\ContainLower",
        "App\\Validators\\Extensions\\ContainNumber",
        "App\\Validators\\Extensions\\DocumentType",
        "App\\Validators\\Extensions\\FundingSource",
        "App\\Validators\\Extensions\\LandOwnership",
        "App\\Validators\\Extensions\\LandType",
        "App\\Validators\\Extensions\\RestorationGoal",
        "App\\Validators\\Extensions\\RestorationMethod",
        "App\\Validators\\Extensions\\SustainableDevelopmentGoal",
        "App\\Validators\\Extensions\\Continent",
        "App\\Validators\\Extensions\\ReportingLevel",
        "App\\Validators\\Extensions\\ReportingFrequency",
        "App\\Validators\\Extensions\\SoftUrl",
        "App\\Validators\\Extensions\\StartsWithFacebook",
        "App\\Validators\\Extensions\\StartsWithTwitter",
        "App\\Validators\\Extensions\\StartsWithLinkedin",
        "App\\Validators\\Extensions\\StartsWithInstagram",
        "App\\Validators\\Extensions\\RevenueDriver",
        "App\\Validators\\Extensions\\CarbonCertificationType",
        "App\\Validators\\Extensions\\LandSize",
        "App\\Validators\\Extensions\\RejectedReason",
        "App\\Validators\\Extensions\\OtherValuePresent",
        "App\\Validators\\Extensions\\OtherValueNull",
        "App\\Validators\\Extensions\\OtherValueString",
        "App\\Validators\\Extensions\\FundingBracket",
        "App\\Validators\\Extensions\\StrictFloat",
        "App\\Validators\\Extensions\\Visibility",
        "App\\Validators\\Extensions\\ArrayArray",
        "App\\Validators\\Extensions\\ArrayObject",
        "App\\Validators\\Extensions\\GeoJson"
    ];

    private function __construct()
    {
    }

    public static function validate(string $name, array $data, bool $checkExtraFields = true): void
    {
        $target = get_called_class() . "::" . $name;
        if (!defined($target) || !is_array(constant($target))) {
            throw new InvalidArgumentException();
        }
        $rules = constant($target);

        $customMessages = [];
        foreach (Validator::EXTENSIONS as $extension) {
            $customMessages[$extension::$name] = json_encode($extension::$message);
        }

        $validator = BaseValidatorFactory::make($data, $rules, $customMessages);

        foreach (Validator::EXTENSIONS as $extension) {
            $validator->addExtension($extension::$name, $extension . "::passes");
        }

        if ($checkExtraFields) {
            $validator->after(function ($validator) {
                $data = array_keys($validator->getData());
                $rules = array_keys($validator->getRules());
                $extraFields = array_diff($data, $rules);
                foreach ($extraFields as $field) {
                    $attribute = str_replace("_", " ", $field);
                    $message = json_encode([
                        "NOT_PRESENT",
                        "The {{attribute}} field must not be present.",
                        ["attribute" => $attribute],
                        "The " . $attribute . " field must not be present."
                    ]);
                    $validator->errors()->add($field, $message);
                }
            });
        }

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
