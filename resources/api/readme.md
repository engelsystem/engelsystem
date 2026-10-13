# Engelsystem API documentation
Here you can find the OpenAPI files that describe the Engelsystem API.
Please be aware that the API is still in Beta and might change every second (but we try to keep it consistent during events ;))

## Access and privileges
Requests are authenticated with the user's API key, sent as `Authorization: Bearer <key>` or `X-Api-Key: <key>`.

What a user may access follows these rules:

* The `api` privilege (e.g. the "API" group) grants read access (`GET`) to every endpoint and keeps all data the API exposes to it.
* The `api` privilege is read-only. It never grants write access (`POST`, `PUT`, `PATCH`, `DELETE`).
* Without the `api` privilege, an endpoint is available if the user has the privilege that grants the same access on the website. Its data is limited to what the website shows that user.
* Write endpoints always require the privilege the website requires for the same action.
* Requests for the user's own data (`/users/self/...`) only require an authenticated user.
* Fields the website only shows with additional privileges are `null` without them.

The description of each operation in the [OpenAPI file](openapi.yml) lists the privileges it requires, the description of a field any additional privileges it requires.

## Links
* [Engelsystem OpenApi](openapi.yml)
* [OpenAPI format specification](https://swagger.io/specification/)
