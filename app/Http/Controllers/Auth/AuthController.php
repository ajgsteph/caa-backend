<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Authentication\LoginAction;
use App\Actions\Authentication\LogoutAction;
use App\Actions\Authentication\RegisterArtistAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterArtistRequest;
use App\Http\Resources\ArtistResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Authentication
 *
 * Endpoints d'inscription, connexion et déconnexion. Les tokens sont émis par Sanctum.
 */
class AuthController extends Controller
{
    /**
     * Inscription d'un artiste
     *
     * Crée un compte artiste, l'attache au rôle `artist` et retourne un token Sanctum.
     *
     * @unauthenticated
     *
     * @response 201 scenario="success" {
     *   "data": {
     *     "id": 5,
     *     "last_name": "Diop",
     *     "first_name": "Awa",
     *     "artist_name": "AWA-D",
     *     "email": "awa@test.sn",
     *     "phone": "+221770000000",
     *     "status": "ACTIVE",
     *     "registered_at": "2026-04-25T22:14:23+00:00",
     *     "roles": ["artist"]
     *   },
     *   "token": "5|h6uDj3r2qZr87yKofZNmjXbaz3S3JyWcxXe4j5Eyf9f4fce1"
     * }
     */
    public function register(RegisterArtistRequest $request, RegisterArtistAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return ArtistResource::make($result['user'])
            ->additional(['token' => $result['token']])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Connexion
     *
     * Authentifie un utilisateur et retourne un token Sanctum.
     *
     * @unauthenticated
     *
     * @response 200 scenario="success" {
     *   "data": {"id": 3, "email": "awa@caa.sn", "roles": ["artist"]},
     *   "token": "2|6URd4kiuvxY5pHKFzSnpYbP0LM3XkLattT2rTfCze475f5bb"
     * }
     * @response 422 scenario="invalid credentials" {
     *   "message": "Identifiants invalides.",
     *   "errors": {"email": ["Identifiants invalides."]}
     * }
     */
    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->string('email'),
            $request->string('password'),
            $request->string('device_name') ?: null,
        );

        return ArtistResource::make($result['user'])
            ->additional(['token' => $result['token']])
            ->response();
    }

    /**
     * Déconnexion
     *
     * Révoque le token courant. Passez `all_devices=true` pour révoquer tous les tokens.
     *
     * @authenticated
     *
     * @bodyParam all_devices boolean Optional. Révoquer tous les tokens. Example: false
     *
     * @response 200 {"message": "Déconnexion réussie."}
     */
    public function logout(Request $request, LogoutAction $action): JsonResponse
    {
        $action->execute($request->user(), (bool) $request->boolean('all_devices'));

        return response()->json(['message' => 'Déconnexion réussie.']);
    }
}
