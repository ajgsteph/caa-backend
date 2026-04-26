# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Obtenez votre token via `POST /api/v1/auth/login`. Envoyez-le ensuite dans l'en-tête `Authorization: Bearer <token>`.
