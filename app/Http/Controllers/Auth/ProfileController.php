<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Authentication\UpdateEmailAction;
use App\Actions\Authentication\UpdatePasswordAction;
use App\Actions\Authentication\UpdatePhoneAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateEmailRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdatePhoneRequest;
use App\Http\Resources\ArtistResource;
use Illuminate\Http\Request;

/**
 * @group Profile
 *
 * Gestion du profil utilisateur authentifié.
 *
 * @authenticated
 */
class ProfileController extends Controller
{
    /**
     * Récupérer le profil courant
     */
    public function show(Request $request): ArtistResource
    {
        return ArtistResource::make($request->user()->load(['artistProfile', 'roles']));
    }

    /**
     * Modifier le mot de passe
     *
     * Nécessite le mot de passe actuel.
     */
    public function updatePassword(UpdatePasswordRequest $request, UpdatePasswordAction $action): ArtistResource
    {
        $user = $action->execute($request->user(), $request->string('password'));

        return ArtistResource::make($user->load(['artistProfile', 'roles']));
    }

    /**
     * Modifier l'email
     *
     * Le mot de passe actuel doit être fourni en confirmation.
     */
    public function updateEmail(UpdateEmailRequest $request, UpdateEmailAction $action): ArtistResource
    {
        $user = $action->execute($request->user(), $request->string('email'));

        return ArtistResource::make($user->load(['artistProfile', 'roles']));
    }

    /**
     * Modifier le téléphone
     */
    public function updatePhone(UpdatePhoneRequest $request, UpdatePhoneAction $action): ArtistResource
    {
        $user = $action->execute($request->user(), $request->string('phone'));

        return ArtistResource::make($user->load(['artistProfile', 'roles']));
    }
}
