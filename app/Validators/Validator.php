<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator as BaseValidatorFactory;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

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
        \App\Validators\Extensions\CountryCode::class,
        \App\Validators\Extensions\OrganisationType::class,
        \App\Validators\Extensions\OrganisationCategory::class,
        \App\Validators\Extensions\ContainUpper::class,
        \App\Validators\Extensions\ContainLower::class,
        \App\Validators\Extensions\ContainNumber::class,
        \App\Validators\Extensions\DocumentType::class,
        \App\Validators\Extensions\FundingSource::class,
        \App\Validators\Extensions\LandOwnership::class,
        \App\Validators\Extensions\LandType::class,
        \App\Validators\Extensions\RestorationGoal::class,
        \App\Validators\Extensions\RestorationMethod::class,
        \App\Validators\Extensions\SustainableDevelopmentGoal::class,
        \App\Validators\Extensions\Continent::class,
        \App\Validators\Extensions\ReportingLevel::class,
        \App\Validators\Extensions\ReportingFrequency::class,
        \App\Validators\Extensions\SoftUrl::class,
        \App\Validators\Extensions\StartsWithFacebook::class,
        \App\Validators\Extensions\StartsWithTwitter::class,
        \App\Validators\Extensions\StartsWithLinkedin::class,
        \App\Validators\Extensions\StartsWithInstagram::class,
        \App\Validators\Extensions\RevenueDriver::class,
        \App\Validators\Extensions\CarbonCertificationType::class,
        \App\Validators\Extensions\LandSize::class,
        \App\Validators\Extensions\RejectedReason::class,
        \App\Validators\Extensions\OtherValuePresent::class,
        \App\Validators\Extensions\OtherValueNull::class,
        \App\Validators\Extensions\OtherValueString::class,
        \App\Validators\Extensions\FundingBracket::class,
        \App\Validators\Extensions\StrictFloat::class,
        \App\Validators\Extensions\Visibility::class,
        \App\Validators\Extensions\ArrayArray::class,
        \App\Validators\Extensions\ArrayObject::class,
        \App\Validators\Extensions\GeoJson::class,
        \App\Validators\Extensions\FileExtensionIsCsv::class,
        \App\Validators\Extensions\FileIsCsvOrUploadable::class,
        \App\Validators\Extensions\TerrafundRestorationMethod::class,
        \App\Validators\Extensions\TerrafundLandTenure::class,
        \App\Validators\Extensions\TerrafundDisturbance::class,
        \App\Validators\Extensions\CompletePercentage::class,
        \App\Validators\Extensions\OrganisationFileType::class,
        \App\validators\Extensions\Polygons\FeatureBounds::class,
        \App\validators\Extensions\Polygons\HasPolygonSite::class,
        \App\validators\Extensions\Polygons\PolygonSize::class,
        \App\validators\Extensions\Polygons\PolygonType::class,
        \App\validators\Extensions\Polygons\SelfIntersection::class,
        \App\validators\Extensions\Polygons\Spikes::class,
        \App\validators\Extensions\Polygons\WithinCountry::class,
    ];

    private function __construct()
    {
    }

    /**
     * @throws ValidationException
     */
    public static function validate(string $name, array $data, bool $checkExtraFields = true): void
    {
        $validator = self::createValidator($name, $data, $checkExtraFields);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public static function isValid(string $name, array $data, bool $checkExtraFields = true): bool
    {
        $validator = self::createValidator($name, $data, $checkExtraFields);

        return ! $validator->fails();
    }

    private static function createValidator(string $name, array $data, bool $checkExtraFields = true): \Illuminate\Validation\Validator
    {
        $target = get_called_class() . '::' . $name;
        if (! defined($target) || ! is_array(constant($target))) {
            throw new InvalidArgumentException();
        }
        $rules = constant($target);

        $customMessages = [];
        foreach (Validator::EXTENSIONS as $extension) {
            $customMessages[$extension::$name] = json_encode($extension::$message);
        }

        $validator = BaseValidatorFactory::make($data, $rules, $customMessages);

        foreach (Validator::EXTENSIONS as $extension) {
            $validator->addExtension($extension::$name, $extension . '::passes');
        }

        if ($checkExtraFields) {
            $validator->after(function ($validator) {
                $data = array_keys($validator->getData());
                $rules = array_keys($validator->getRules());
                $extraFields = array_diff($data, $rules);
                foreach ($extraFields as $field) {
                    $attribute = str_replace('_', ' ', $field);
                    $message = json_encode([
                        'NOT_PRESENT',
                        'The {{attribute}} field must not be present.',
                        ['attribute' => $attribute],
                        'The ' . $attribute . ' field must not be present.',
                    ]);
                    $validator->errors()->add($field, $message);
                }
            });
        }

        return $validator;
    }
}
