<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Services\PostService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\PostCollection;
use Illuminate\Support\Facades\Config;
use App\Http\Requests\PostAddEditRequest;
use App\Http\Resources\PostSingleResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PostController extends Controller
{

    /**
     * @OA\Get(
     * path="/api/v1/post",
     * summary="Post listing",
     * description="get all post",
     * operationId="Listing",
     * tags={"Post"},
     * security={
     *           {"sanctum": {}}
     *  },
     *  @OA\Parameter(
     *      name="page",
     *      in="query",
     *      example="1",
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *  @OA\Parameter(
     *      name="per_page",
     *      in="query",
     *      example="20",
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="Successful operation",
     *    ),
     * )
     */
    public function show(Request $request)
    {
       /*  $post= Post::find(9);
        print_r($post->media()->get());die(); */
        $per_page   = $request->per_page ?? Config::get('constants.pagination_per_page');
        $posts      =  Post::with(['tags', 'media'])->orderBY('id','desc')->paginate($per_page);

        if (count($posts)) {
            return (new PostCollection($posts))->additional(['message' => 'Post listing']);
        }

        return (new PostCollection($posts))->additional(['message' => 'No post data available']);
    }

    /**
     * @OA\Post(
     * path="/api/v1/post/add",
     * summary="Add",
     * description="Add new post",
     * operationId="Add",
     * tags={"Post"},
     * security={
     *           {"sanctum": {}}
     *  },
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  required={"title","content"},
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="content",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="tags[0]",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="tags[1]",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="media",
     *                     type="file"
     *                 ),
     *                  example={"title": "demo title", "content": "content title", "tags[0]": "tag1", "tags[1]": "tag2" }
     *             )
     *         )
     *     ),
     *  @OA\Response(
     *    response=422,
     *    description="validation error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The given data was invalid")
     *        )
     *     )
     * )
     */
    public function add(PostAddEditRequest $request)
    {
        $request->validated();

        try {
            DB::beginTransaction();
            $post = (new PostService())->savePost($request);

            if (!empty($post)) {
                DB::commit();
                return new PostSingleResource($post);
            }

            abort(302, 'Fail to save post');
        } catch (Exception $ex) {

            DB::rollback();
            abort(500, $ex->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/api/v1/post/edit/{id}",
     * summary="Update",
     * description="UPdate post",
     * operationId="Update",
     * tags={"Post"},
     * security={
     *           {"sanctum": {}}
     *  }, 
     *     @OA\Parameter(
     *         description="post id parameter",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  required={"title","content"},
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="content",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="tags[0]",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="tags[1]",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="media",
     *                     type="file"
     *                 ),
     *                  example={"title": "demo title", "content": "content title", "tags[0]": "tag1", "tags[1]": "tag2" }
     *             )
     *         )
     *     ),
     *  @OA\Response(
     *    response=422,
     *    description="validation error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The given data was invalid")
     *        )
     *     )
     * )
     */
    public function update(PostAddEditRequest $request, $post_id)
    {
        $request->validated();

        try {
            // start the transaction
            DB::beginTransaction();
            $post = (new PostService())->updatePost($request, $post_id);

            if (!empty($post)) {
                DB::commit();
                return new PostSingleResource($post);
            }

            abort(302, 'Fail to save post');
        } catch (ModelNotFoundException $ex) {

            // it will rollback the data when gets a exception or a error
            DB::rollback();
            abort(404, 'Entry for ' . str_replace('App\\', '', $ex->getModel()) . ' not found');
        } catch (Exception $ex) {

            // it will rollback the data when gets a exception or a error
            DB::rollback();
            abort(500, $ex->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/api/v1/post/delete/{id}",
     * summary="Delete",
     * description="Delete post",
     * operationId="Delete",
     * tags={"Post"},
     * security={
     *     {"sanctum": {}}
     *  }, 
     *  @OA\Parameter(
     *     description="post id parameter",
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(type="integer")
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="validation error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The given data was invalid")
     *        )
     *     )
     * )
     */
    public function delete(Request $request, $post_id)
    {
        try {
            // start the transaction
            DB::beginTransaction();
            $post = (new PostService())->deletePost($request, $post_id);

            if ($post) {
                DB::commit();

                return $this->sendResponse('Post deleted successfully', null, 200);
            }

            return $this->sendResponse('Fail to deleted post', null, 200);
        } catch (ModelNotFoundException $ex) {

            // it will rollback the data when gets a exception or a error
            DB::rollback();
            abort(404, 'Entry for ' . str_replace('App\\', '', $ex->getModel()) . ' not found');
        } catch (Exception $ex) {

            // it will rollback the data when gets a exception or a error
            DB::rollback();
            abort(500, $ex->getMessage());
        }
    }
}
