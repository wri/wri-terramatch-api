<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => '["ACCEPTED","The {{attribute}} must be accepted.",{"attribute":":attribute"},"The :attribute must be accepted."]',
    'active_url' => '["ACTIVE_URL","The {{attribute}} is not a valid URL.",{"attribute":":attribute"},"The :attribute is not a valid URL."]',
    'after' => '["AFTER","The {{attribute}} must be a date after {{date}}.",{"attribute":":attribute","date":":date"},"The :attribute must be a date after :date."]',
    'after_or_equal' => '["AFTER_OR_EQUAL","The {{attribute}} must be a date after or equal to {{date}}.",{"attribute":":attribute","date":":date"},"The :attribute must be a date after or equal to :date."]',
    'alpha' => '["ALPHA","The {{attribute}} may only contain letters.",{"attribute":":attribute"},"The :attribute may only contain letters."]',
    'alpha_dash' => '["ALPHA_DASH","The {{attribute}} may only contain letters, numbers, dashes and underscores.",{"attribute":":attribute"},"The :attribute may only contain letters, numbers, dashes and underscores."]',
    'alpha_num' => '["ALPHA_NUM","The {{attribute}} may only contain letters and numbers.",{"attribute":":attribute"},"The :attribute may only contain letters and numbers."]',
    'array' => '["ARRAY","The {{attribute}} must be an array.",{"attribute":":attribute"},"The :attribute must be an array."]',
    'before' => '["BEFORE","The {{attribute}} must be a date before {{date}}.",{"attribute":":attribute","date":":date"},"The :attribute must be a date before :date."]',
    'before_or_equal' => '["BEFORE_OR_EQUAL","The {{attribute}} must be a date before or equal to {{date}}.",{"attribute":":attribute","date":":date"},"The :attribute must be a date before or equal to :date."]',
    'between' => [
        'numeric' => '["BETWEEN","The {{attribute}} must be between {{min}} and {{max}}.",{"attribute":":attribute","min":":min","max":":max"},"The :attribute must be between :min and :max."]',
        'file' => '["BETWEEN","The {{attribute}} must be between {{min}} and {{max}} kilobytes.",{"attribute":":attribute","min":":min","max":":max"},"The :attribute must be between :min and :max kilobytes."]',
        'string' => '["BETWEEN","The {{attribute}} must be between {{min}} and {{max}} characters.",{"attribute":":attribute","min":":min","max":":max"},"The :attribute must be between :min and :max characters."]',
        'array' => '["BETWEEN","The {{attribute}} must have between {{min}} and {{max}} items.",{"attribute":":attribute","min":":min","max":":max"},"The :attribute must have between :min and :max items."]',
    ],
    'boolean' => '["BOOLEAN","The {{attribute}} field must be true or false.",{"attribute":":attribute"},"The :attribute field must be true or false."]',
    'confirmed' => '["CONFIRMED","The {{attribute}} confirmation does not match.",{"attribute":":attribute"},"The :attribute confirmation does not match."]',
    'date' => '["DATE","The {{attribute}} is not a valid date.",{"attribute":":attribute"},"The :attribute is not a valid date."]',
    'date_equals' => '["DATE_EQUALS","The {{attribute}} must be a date equal to {{date}}.",{"attribute":":attribute","date":":date"},"The :attribute must be a date equal to :date."]',
    'date_format' => '["DATE_FORMAT","The {{attribute}} does not match the format {{format}}.",{"attribute":":attribute","format":":format"},"The :attribute does not match the format :format."]',
    'different' => '["DIFFERENT","The {{attribute}} and {{other}} must be different.",{"attribute":":attribute","other":":other"},"The :attribute and :other must be different."]',
    'digits' => '["DIGITS","The {{attribute}} must be {{digits}} digits.",{"attribute":":attribute","digits":":digits"},"The :attribute must be :digits digits."]',
    'digits_between' => '["DIGITS_BETWEEN","The {{attribute}} must be between {{min}} and {{max}} digits.",{"attribute":":attribute","min":":min","max":":max"},"The :attribute must be between :min and :max digits."]',
    'dimensions' => '["DIMENSIONS","The {{attribute}} has invalid image dimensions.",{"attribute":":attribute"},"The :attribute has invalid image dimensions."]',
    'distinct' => '["DISTINCT","The {{attribute}} field has a duplicate value.",{"attribute":":attribute"},"The :attribute field has a duplicate value."]',
    'email' => '["EMAIL","The {{attribute}} must be a valid email address.",{"attribute":":attribute"},"The :attribute must be a valid email address."]',
    'ends_with' => '["ENDS_WITH","The {{attribute}} must end with one of the following: {{values}}",{"attribute":":attribute","values":":values"},"The :attribute must end with one of the following: :values"]',
    'exists' => '["EXISTS","The selected {{attribute}} is invalid.",{"attribute":":attribute"},"The selected :attribute is invalid."]',
    'file' => '["FILE","The {{attribute}} must be a file.",{"attribute":":attribute"},"The :attribute must be a file."]',
    'filled' => '["FILLED","The {{attribute}} field must have a value.",{"attribute":":attribute"},"The :attribute field must have a value."]',
    'gt' => [
        'numeric' => '["GT","The {{attribute}} must be greater than {{value}}.",{"attribute":":attribute","value":":value"},"The :attribute must be greater than :value."]',
        'file' => '["GT","The {{attribute}} must be greater than {{value}} kilobytes.",{"attribute":":attribute","value":":value"},"The :attribute must be greater than :value kilobytes."]',
        'string' => '["GT","The {{attribute}} must be greater than {{value}} characters.",{"attribute":":attribute","value":":value"},"The :attribute must be greater than :value characters."]',
        'array' => '["GT","The {{attribute}} must have more than {{value}} items.",{"attribute":":attribute","value":":value"},"The :attribute must have more than :value items."]',
    ],
    'gte' => [
        'numeric' => '["GTE","The {{attribute}} must be greater than or equal {{value}}.",{"attribute":":attribute","value":":value"},"The :attribute must be greater than or equal :value."]',
        'file' => '["GTE","The {{attribute}} must be greater than or equal {{value}} kilobytes.",{"attribute":":attribute","value":":value"},"The :attribute must be greater than or equal :value kilobytes."]',
        'string' => '["GTE","The {{attribute}} must be greater than or equal {{value}} characters.",{"attribute":":attribute","value":":value"},"The :attribute must be greater than or equal :value characters."]',
        'array' => '["GTE","The {{attribute}} must have {{value}} items or more.",{"attribute":":attribute","value":":value"},"The :attribute must have :value items or more."]',
    ],
    'image' => '["IMAGE","The {{attribute}} must be an image.",{"attribute":":attribute"},"The :attribute must be an image."]',
    'in' => '["IN","The selected {{attribute}} is invalid.",{"attribute":":attribute"},"The selected :attribute is invalid."]',
    'in_array' => '["IN_ARRAY","The {{attribute}} field does not exist in {{other}}.",{"attribute":":attribute","other":":other"},"The :attribute field does not exist in :other."]',
    'integer' => '["INTEGER","The {{attribute}} must be an integer.",{"attribute":":attribute"},"The :attribute must be an integer."]',
    'ip' => '["IP","The {{attribute}} must be a valid IP address.",{"attribute":":attribute"},"The :attribute must be a valid IP address."]',
    'ipv4' => '["IPV4","The {{attribute}} must be a valid IPv4 address.",{"attribute":":attribute"},"The :attribute must be a valid IPv4 address."]',
    'ipv6' => '["IPV6","The {{attribute}} must be a valid IPv6 address.",{"attribute":":attribute"},"The :attribute must be a valid IPv6 address."]',
    'json' => '["JSON","The {{attribute}} must be a valid JSON string.",{"attribute":":attribute"},"The :attribute must be a valid JSON string."]',
    'lt' => [
        'numeric' => '["LT","The {{attribute}} must be less than {{value}}.",{"attribute":":attribute","value":":value"},"The :attribute must be less than :value."]',
        'file' => '["LT","The {{attribute}} must be less than {{value}} kilobytes.",{"attribute":":attribute","value":":value"},"The :attribute must be less than :value kilobytes."]',
        'string' => '["LT","The {{attribute}} must be less than {{value}} characters.",{"attribute":":attribute","value":":value"},"The :attribute must be less than :value characters."]',
        'array' => '["LT","The {{attribute}} must have less than {{value}} items.",{"attribute":":attribute","value":":value"},"The :attribute must have less than :value items."]',
    ],
    'lte' => [
        'numeric' => '["LTE","The {{attribute}} must be less than or equal {{value}}.",{"attribute":":attribute","value":":value"},"The :attribute must be less than or equal :value."]',
        'file' => '["LTE","The {{attribute}} must be less than or equal {{value}} kilobytes.",{"attribute":":attribute","value":":value"},"The :attribute must be less than or equal :value kilobytes."]',
        'string' => '["LTE","The {{attribute}} must be less than or equal {{value}} characters.",{"attribute":":attribute","value":":value"},"The :attribute must be less than or equal :value characters."]',
        'array' => '["LTE","The {{attribute}} must not have more than {{value}} items.",{"attribute":":attribute","value":":value"},"The :attribute must not have more than :value items."]',
    ],
    'max' => [
        'numeric' => '["MAX","The {{attribute}} may not be greater than {{max}}.",{"attribute":":attribute","max":":max"},"The :attribute may not be greater than :max."]',
        'file' => '["MAX","The {{attribute}} may not be greater than {{max}} kilobytes.",{"attribute":":attribute","max":":max"},"The :attribute may not be greater than :max kilobytes."]',
        'string' => '["MAX","The {{attribute}} may not be greater than {{max}} characters.",{"attribute":":attribute","max":":max"},"The :attribute may not be greater than :max characters."]',
        'array' => '["MAX","The {{attribute}} may not have more than {{max}} items.",{"attribute":":attribute","max":":max"},"The :attribute may not have more than :max items."]',
    ],
    'mimes' => '["MIMES","The {{attribute}} must be a file of type: {{values}}.",{"attribute":":attribute","values":":values"},"The :attribute must be a file of type: :values."]',
    'mimetypes' => '["MIMETYPES","The {{attribute}} must be a file of type: {{values}}.",{"attribute":":attribute","values":":values"},"The :attribute must be a file of type: :values."]',
    'min' => [
        'numeric' => '["MIN","The {{attribute}} must be at least {{min}}.",{"attribute":":attribute","min":":min"},"The :attribute must be at least :min."]',
        'file' => '["MIN","The {{attribute}} must be at least {{min}} kilobytes.",{"attribute":":attribute","min":":min"},"The :attribute must be at least :min kilobytes."]',
        'string' => '["MIN","The {{attribute}} must be at least {{min}} characters.",{"attribute":":attribute","min":":min"},"The :attribute must be at least :min characters."]',
        'array' => '["MIN","The {{attribute}} must have at least {{min}} items.",{"attribute":":attribute","min":":min"},"The :attribute must have at least :min items."]',
    ],
    'not_in' => '["NOT_IN","The selected {{attribute}} is invalid.",{"attribute":":attribute"},"The selected :attribute is invalid."]',
    'not_regex' => '["NOT_REGEX","The {{attribute}} format is invalid.",{"attribute":":attribute"},"The :attribute format is invalid."]',
    'numeric' => '["NUMERIC","The {{attribute}} must be a number.",{"attribute":":attribute"},"The :attribute must be a number."]',
    'present' => '["PRESENT","The {{attribute}} field must be present.",{"attribute":":attribute"},"The :attribute field must be present."]',
    'regex' => '["REGEX","The {{attribute}} format is invalid.",{"attribute":":attribute"},"The :attribute format is invalid."]',
    'required' => '["REQUIRED","The {{attribute}} field is required.",{"attribute":":attribute"},"The :attribute field is required."]',
    'required_if' => '["REQUIRED_IF","The {{attribute}} field is required when {{other}} is {{value}}.",{"attribute":":attribute","other":":other","value":":value"},"The :attribute field is required when :other is :value."]',
    'required_unless' => '["REQUIRED_UNLESS","The {{attribute}} field is required unless {{other}} is in {{values}}.",{"attribute":":attribute","other":":other","values":":values"},"The :attribute field is required unless :other is in :values."]',
    'required_with' => '["REQUIRED_WITH","The {{attribute}} field is required when {{values}} is present.",{"attribute":":attribute","values":":values"},"The :attribute field is required when :values is present."]',
    'required_with_all' => '["REQUIRED_WITH_ALL","The {{attribute}} field is required when {{values}} are present.",{"attribute":":attribute","values":":values"},"The :attribute field is required when :values are present."]',
    'required_without' => '["REQUIRED_WITHOUT","The {{attribute}} field is required when {{values}} is not present.",{"attribute":":attribute","values":":values"},"The :attribute field is required when :values is not present."]',
    'required_without_all' => '["REQUIRED_WITHOUT_ALL","The {{attribute}} field is required when none of {{values}} are present.",{"attribute":":attribute","values":":values"},"The :attribute field is required when none of :values are present."]',
    'same' => '["SAME","The {{attribute}} and {{other}} must match.",{"attribute":":attribute","other":":other"},"The :attribute and :other must match."]',
    'size' => [
        'numeric' => '["SIZE","The {{attribute}} must be {{size}}.",{"attribute":":attribute","size":":size"},"The :attribute must be :size."]',
        'file' => '["SIZE","The {{attribute}} must be {{size}} kilobytes.",{"attribute":":attribute","size":":size"},"The :attribute must be :size kilobytes."]',
        'string' => '["SIZE","The {{attribute}} must be {{size}} characters.",{"attribute":":attribute","size":":size"},"The :attribute must be :size characters."]',
        'array' => '["SIZE","The {{attribute}} must contain {{size}} items.",{"attribute":":attribute","size":":size"},"The :attribute must contain :size items."]',
    ],
    'starts_with' => '["STARTS_WITH","The {{attribute}} must start with one of the following: {{values}}",{"attribute":":attribute","values":":values"},"The :attribute must start with one of the following: :values"]',
    'string' => '["STRING","The {{attribute}} must be a string.",{"attribute":":attribute"},"The :attribute must be a string."]',
    'timezone' => '["TIMEZONE","The {{attribute}} must be a valid zone.",{"attribute":":attribute"},"The :attribute must be a valid zone."]',
    'unique' => '["UNIQUE","The {{attribute}} has already been taken.",{"attribute":":attribute"},"The :attribute has already been taken."]',
    'uploaded' => '["UPLOADED","The {{attribute}} failed to upload.",{"attribute":":attribute"},"The :attribute failed to upload."]',
    'url' => '["URL","The {{attribute}} format is invalid.",{"attribute":":attribute"},"The :attribute format is invalid."]',
    'uuid' => '["UUID","The {{attribute}} must be a valid UUID.",{"attribute":":attribute"},"The :attribute must be a valid UUID."]',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
