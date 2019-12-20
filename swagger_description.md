### About

This API serves the web and mobile apps for WRI's Restoration Marketplace.

### Authentication & Authorisation

JWTs are used for authentication. Upon successful log in a JWT will be provided for you. These expire after 12 hours.

A padlock icon next to an endpoint indicates that it requires an authenticated user. For example:

![](/images/padlock.png)

### Requests & Responses

The response bodies documented here will be wrapped in an object adhering to the JSON:API specification.

### Error Codes

Any errors returned in the body of a response will have a unique code to help identify the type of error. They are:

```
ACCEPTED, ACTIVE_URL, AFTER, AFTER_OR_EQUAL, ALPHA, ALPHA_DASH, ALPHA_NUM, ARRAY, BEFORE, BEFORE_OR_EQUAL, BETWEEN, BOOLEAN, CARBON_CERTIFICATION_TYPE, CONFIRMED, CONTAIN_LOWER, CONTAIN_NUMBER, CONTAIN_UPPER, CONTINENT, COUNTRY_CODE, DATE, DATE_EQUALS, DATE_FORMAT, DIFFERENT, DIGITS, DIGITS_BETWEEN, DIMENSIONS, DISTINCT, DOCUMENT_TYPE, EMAIL, ENDS_WITH, EXISTS, FILE, FILLED, FUNDING_SOURCE, GT, GTE, IMAGE, IN, IN_ARRAY, INTEGER, IP, IPV4, IPV6, JSON, LAND_OWNERSHIP, LAND_SIZE, LAND_TYPE, LT, LTE, MAX, MIMES, MIMETYPES, MIN, NOT_IN, NOT_PRESENT, NOT_REGEX, NUMERIC, ORGANISATION_CATEGORY, ORGANISATION_TYPE, PRESENT, REGEX, REPORTING_FREQUENCY, REPORTING_LEVEL, REQUIRED, REQUIRED_IF, REQUIRED_UNLESS, REQUIRED_WITH, REQUIRED_WITH_ALL, REQUIRED_WITHOUT, REQUIRED_WITHOUT_ALL, RESTORATION_GOAL, RESTORATION_METHOD, REVENUE_DRIVER, SAME, SIZE, SOFT_URL, STARTS_WITH, STARTS_WITH_FACEBOOK, STARTS_WITH_TWITTER, STARTS_WITH_INSTAGRAM, STARTS_WITH_LINKEDIN, STRING, SUSTAINABLE_DEVELOPMENT_GOAL, TIMEZONE, TREE_SPECIES_OWNER, UNIQUE, UPLOADED, URL, UUID
```

### Uploads

Uploads should first be uploaded to the `/uploads` endpoint. Upon success an ID will be returned, this ID is valid for 1 hour. Use this ID in your request body to bind the upload to a property.

### Filtering

Filtering can be applied to the `/offers` and `/pitches` endpoints by sending a JSON payload. See their description for more information.

### Sorting

Sorting can be applied to the `/offers` and `/pitches` endpoints by appending query string parameters of `&sortAttribute=foo` and `&sortDirection=asc`.

The following attributes can be supplied to `sortAttribute`:

```
created_at, funding_amount, compatibility_score
```

The following directions can be supplied to `sortDirection`:

```
asc, desc
```

### Pagination

Pagination can be applied to the `/offers` and `/pitches` endpoints by appending a query string parameter of `&page=1`.

### Entity Relationship Diagram

![](/images/erd.png)

### Units

* All prices are measured in USD
* All land is measured in hectares
* All time is measured in months
