API General info:

### About

This API serves the web and mobile apps for WRI&#96;s Restoration Marketplace (AKA TerraMatch).

### Authorisation & Authentication

JWTs are used for authentication. Upon successful log in a JWT will be provided for you. These expire after 12 hours.

### Requests & Responses

The response bodies documented here will be wrapped in an object adhering to the JSON:API specification.

### Error Codes

Any errors returned in the body of a response will have a unique code to help identify the type of error. They are:

```
ACCEPTED, ACTIVE_URL, AFTER, AFTER_OR_EQUAL, ALPHA, ALPHA_DASH, ALPHA_NUM, ARRAY, ARRAY_ARRAY, ARRAY_OBJECT, BEFORE, BEFORE_OR_EQUAL, BETWEEN, BOOLEAN, CARBON_CERTIFICATION_TYPE, COMPLETE_PERCENTAGE, CONFIRMED, CONTAIN_LOWER, CONTAIN_NUMBER, CONTAIN_UPPER, CONTINENT, COUNTRY_CODE, CUSTOM, DATE, DATE_EQUALS, DATE_FORMAT, DIFFERENT, DIGITS, DIGITS_BETWEEN, DIMENSIONS, DISTINCT, DOCUMENT_TYPE, EMAIL, ENDS_WITH, EXISTS, FILE, FILE_EXTENSION_IS_CSV, FILE_IS_CSV_OR_UPLOADABLE, FILLED, FUNDING_BRACKET, FUNDING_SOURCE, GEO_JSON, GT, GTE, IMAGE, IN, IN_ARRAY, INTEGER, IP, IPV4, IPV6, JSON, LAND_OWNERSHIP, LAND_SIZE, LAND_TYPE, LT, LTE, MAX, MIMES, MIMETYPES, MIN, NOT_IN, NOT_PRESENT, NOT_REGEX, NUMERIC, OTHER_VALUE_PRESENT, OTHER_VALUE_NULL, OTHER_VALUE_STRING, ORGANISATION_CATEGORY, ORGANISATION_FILE_TYPE, ORGANISATION_TYPE, PRESENT, REGEX, REJECTED_REASON, REPORTING_FREQUENCY, REPORTING_LEVEL, REQUIRED, REQUIRED_IF, REQUIRED_UNLESS, REQUIRED_WITH, REQUIRED_WITH_ALL, REQUIRED_WITHOUT, REQUIRED_WITHOUT_ALL, RESTORATION_GOAL, RESTORATION_METHOD, REVENUE_DRIVER, SAME, SIZE, SOFT_URL, STARTS_WITH, STARTS_WITH_FACEBOOK, STARTS_WITH_TWITTER, STARTS_WITH_INSTAGRAM, STARTS_WITH_LINKEDIN, STRICT_FLOAT, STRING, SUSTAINABLE_DEVELOPMENT_GOAL, TERRAFUND_DISTURBANCE, TERRAFUND_LAND_TENURE, TERRAFUND_PROGRAMME_STATUS, TERRAFUND_NURSERY_TYPE, TERRAFUND_RESTORATION_METHOD, TIMEZONE, TREE_SPECIES_OWNER, UNIQUE, UPLOADED, URL, UUID, VISIBILITY
```

### Uploads

Uploads should first be uploaded to the `/uploads` endpoint. Upon success an ID will be returned, this ID is valid for 1 day. Use this ID in your request body to bind the upload to a property.

### Elevator Videos

Elevator videos can be created by using the `/elevator_videos` endpoint. After creating an elevator video you will be returned an elevator video ID. Use this to check its status. Elevator videos will start off as `processing` and change to `finished` when it has been build. Once the elevator video is built the `upload_id` property will be present, you can use this just like a regular upload and attach it to a pitch&#96;s `video` property. Be sure to use the elevator video&#96;s `upload_id` property and not its `id` property. An elevator video&#96;s status may end up as `errored` or `timed_out` in which case something has gone wrong.

### Units

* All prices are measured in USD
* All land is measured in hectares
* All time is measured in months
*
### Drafts

When creating a draft the `data` property will be automatically populated with a skeleton object representing either an offer or a pitch. You can then manipulate the `data` property using [JSON Patch](http://jsonpatch.com/) requests. Operations are relative to the `data` property, which means you don&#96;t need to preface paths with `/data`.
