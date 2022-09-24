<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Requests\TagSearchRequest;
use App\Http\Resources\TagsSearchCollection;

class TagController extends Controller
{

        /**
     * @OA\Get(
     * path="/api/v1/tag/search",
     * summary="Tag search",
     * description="Search tags",
     * operationId="Tags",
     * tags={"Tags"},
     * security={
     *     {"sanctum": {}}
     *  },
     *  @OA\Parameter(
     *      name="term",
     *      in="query",
     *      required=true,
     *      example="php",
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *  @OA\Response(
     *    response=422,
     *    description="validation error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The given data was invalid")
     *        )
     *     )
     * )
     */
    public function search( TagSearchRequest $request ){

        $request->validated();

        $tags = Tag::where('name', 'like', '%' . $request->term. '%'  )->get();

        if (count( $tags )) {
            return (new TagsSearchCollection($tags))->additional(['message' => 'Tags search result']);
        }

        return (new TagsSearchCollection($tags))->additional(['message' => 'No tags available']);
    }
}
