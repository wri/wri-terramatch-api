# WRI Restoration Marketplace API

## About

This repository contains the API which serves the web and mobile apps for WRI's Restoration Marketplace. 

There's lots of supporting documentation in [Confluence](https://3sidedcube.atlassian.net/wiki/spaces/WRI/overview).

## Local Development

This repository uses Docker for local development.

### Set Up

After you've cloned this repository run:

```
make build
docker-compose up
```

Going forward just run:

```
docker-compose up
```

You should now be able to browse to [http://127.0.0.1:8080](http://127.0.0.1:8080) and see something.

In the future if any of the `.Dockerfile`s change you will need to run:

```
make build
```

Don't forget to migrate and seed all the services.

### Services

MariaDB is located at `127.0.0.1:3360`. The database name is `wri_restoration_marketplace_api`, the username is `wri` and the password is `wri`.

MailCatcher will catch any emails sent over SMTP. To view them browse to [http://127.0.0.1:1080](http://127.0.0.1:1080).

To view the document store browse to [http://127.0.0.1:9000](http://127.0.0.1:9000). The username and password are `AKIABUVWH1HUD7YQZQAR` and `PVMlDMep3/jLSz9GxPV3mTvH4JZynkf2BFeTu+i8`.

### Migrating & Seeding

To migrate and seed all the services run:

```
make migrate-seed
```

This will delete any exiting data in those services. You have been warned.

### CLI Programs

To interact with Artisan run:

```
docker-compose run php ./artisan XXX
```

There is no scheduler for local development. You will have to run any scheduled commands manually through Artisan.

To start a queue worker pass the command `queue:work --no-interaction XXX` to Artisan.

To interact with Composer run:

```
docker-compose run composer composer XXX
```

To interact with npm run:

```
docker-compose run npm npm XXX
```

## Tests

To execute the test suite run:

```
make test
```

PHPUnit will run automatically inside Bitbucket's Pipelines feature when creating a pull request.

## Documentation

We use Swagger (AKA OpenAPI Spec 2.0, not to be confused with OpenAPI Spec 3.0) for documenting the API. To view the documentation browse to [http://127.0.0.1:8000/documentation](http://127.0.0.1:8000/documentation).

## Provisioning & Deployment

There is a [separate repository](https://bitbucket.org/3sidedcube/wri-restoration-marketplace-api-provisioning) for provisioning and deployment.

## Troubleshooting

* Quite often you'll end up with some weird file permissions by using Docker to run Artisan's scaffolders. This won't allow you to edit them! Use `sudo make permissions` to fix that.
