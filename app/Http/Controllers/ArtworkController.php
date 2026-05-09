<?php

namespace App\Http\Controllers;

use App\Actions\Artwork\DeleteArtworkAction;
use App\Actions\Artwork\SaveArtworkAction;
use App\Actions\Artwork\UpdateArtworkAction;
use App\Http\Requests\Artwork\StoreArtworkRequest;
use App\Http\Requests\Artwork\UpdateArtworkRequest;
use App\Http\Resources\ArtworkResource;
use App\Models\Artwork;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

/**
 * @group Œuvres
 *
 * Gestion des œuvres d'art de l'artiste authentifié.
 *
 * @authenticated
 */
class ArtworkController extends Controller
{
    /**
     * Lister mes œuvres
     *
     * Retourne les œuvres de l'artiste authentifié, paginées par 15.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $artworks = $request->user()
            ->artworks()
            ->latest()
            ->paginate(15);

        return ArtworkResource::collection($artworks);
    }

    /**
     * Créer une œuvre
     *
     * Réservé au rôle `artist`.
     *
     * @bodyParam title string required Titre de l'œuvre. Example: Soleil de Dakar
     * @bodyParam type string required Type d'œuvre (PAINTING, SCULPTURE, PHOTOGRAPHY, DRAWING, ENGRAVING, DIGITAL_ART, TEXTILE, INSTALLATION, OTHER). Example: PAINTING
     * @bodyParam technique string Technique utilisée. Example: Acrylique sur toile
     * @bodyParam dimensions string Dimensions. Example: 80x60 cm
     * @bodyParam year integer Année de création. Example: 2025
     * @bodyParam description string Description longue. No-example
     * @bodyParam signature string Signature de l'artiste. No-example
     * @bodyParam image file Image de l'œuvre (jpg/jpeg/png/webp, max 5 Mo). No-example
     *
     * @response 201 scenario="created" {"data": {"id": 1, "title": "Soleil de Dakar", "type": "PAINTING"}}
     */
    public function store(StoreArtworkRequest $request, SaveArtworkAction $action): JsonResponse
    {
        $artwork = $action->execute(
            $request->user(),
            $request->validated(),
            $request->file('image'),
        );

        return ArtworkResource::make($artwork)->response()->setStatusCode(201);
    }

    /**
     * Voir une œuvre
     *
     * @urlParam artwork integer required ID de l'œuvre. Example: 1
     */
    public function show(Artwork $artwork): ArtworkResource
    {
        Gate::authorize('view', $artwork);

        return ArtworkResource::make($artwork);
    }

    /**
     * Modifier une œuvre
     *
     * Réservé au rôle `artist`. L'artiste ne peut modifier que ses propres œuvres.
     *
     * @urlParam artwork integer required ID de l'œuvre. Example: 1
     * @bodyParam title string Titre de l'œuvre. Example: Soleil de Dakar
     * @bodyParam type string Type d'œuvre. Example: PAINTING
     * @bodyParam technique string Technique utilisée. Example: Acrylique sur toile
     * @bodyParam dimensions string Dimensions. Example: 80x60 cm
     * @bodyParam year integer Année de création. Example: 2025
     * @bodyParam description string Description longue. No-example
     * @bodyParam signature string Signature de l'artiste. No-example
     * @bodyParam image file Nouvelle image (jpg/jpeg/png/webp, max 5 Mo). No-example
     */
    public function update(UpdateArtworkRequest $request, Artwork $artwork, UpdateArtworkAction $action): ArtworkResource
    {
        Gate::authorize('update', $artwork);

        $artwork = $action->execute($artwork, $request->validated(), $request->file('image'));

        return ArtworkResource::make($artwork);
    }

    /**
     * Supprimer une œuvre
     *
     * Réservé au rôle `artist`. Impossible si un certificat est déjà associé à cette œuvre.
     *
     * @urlParam artwork integer required ID de l'œuvre. Example: 1
     *
     * @response 204 scenario="supprimée"
     * @response 422 scenario="certificat existant" {"message": "Impossible de supprimer une œuvre liée à un certificat."}
     */
    public function destroy(Artwork $artwork, DeleteArtworkAction $action): JsonResponse
    {
        Gate::authorize('delete', $artwork);

        if ($artwork->certificate()->exists()) {
            return response()->json(['message' => 'Impossible de supprimer une œuvre liée à un certificat.'], 422);
        }

        $action->execute($artwork);

        return response()->json(null, 204);
    }
}
