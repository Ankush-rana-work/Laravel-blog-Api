<?php

namespace App\Http\Controllers\API\V1;

use Exception;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Services\CommentService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Config;
use App\Http\Requests\CommentAddRequest;
use App\Http\Requests\CommentEditRequest;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentSingleResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CommentController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/v1/comment",
     * summary="Comment listing",
     * description="get all comment",
     * operationId="Comment Listing",
     * tags={"Comment"},
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
     *  @OA\Parameter(
     *      name="post_id",
     *      in="query",
     *      required=true,
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
    public function show( CommentRequest $request )
    {
        $request->validated();

        $per_page   = $request->per_page ?? Config::get('constants.pagination_per_page');
        $comment = Comment::with(['user','post', 'children', 'media'])
                ->where('post_id', $request->post_id)
                ->whereNull('parent_id')->paginate($per_page);

        if (count($comment)) {
            return (new CommentCollection($comment))->additional(['message' => 'Comment listing']);
        }

        return (new CommentCollection($comment))->additional(['message' => 'No comment data available']);
    }

        /**
     * @OA\Post(
     * path="/api/v1/comment/add",
     * summary="Add comment",
     * description="Add new comment",
     * operationId="Add comment",
     * tags={"Comment"},
     * security={
     *           {"sanctum": {}}
     *  },
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  required={"post_id","text"},
     *                 @OA\Property(
     *                     property="post_id",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="text",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="parent_id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="media",
     *                     type="file"
     *                 ),
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
    public function add(CommentAddRequest $request)
    {
        $request->validated();

        try {

            DB::beginTransaction();
            $post = (new CommentService())->saveComment($request);

            if (!empty($post)) {
                DB::commit();
                return new CommentSingleResource($post);
            }

            $this->sendResponse('Fail to create comment', null, 422);
        } catch (Exception $ex) {

            DB::rollback();
            abort(500, $ex->getMessage());
        }
    }

        /**
     * @OA\Post(
     * path="/api/v1/comment/edit/{id}",
     * summary="Upated comment",
     * description="Update new comment",
     * operationId="Update comment",
     * tags={"Comment"},
     * security={
     *           {"sanctum": {}}
     *  },
     *     @OA\Parameter(
     *         description="comment id parameter",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  required={"text"},
     *                 @OA\Property(
     *                     property="text",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="media",
     *                     type="file"
     *                 ),
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
    public function edit(CommentEditRequest $request, $comment_id)
    {
        try {

            DB::beginTransaction();

            $comment = (new CommentService())->updateComment($request, $comment_id);

            if (!empty($comment)) {
                DB::commit();
                return new CommentSingleResource($comment);
            }

            $this->sendResponse('Fail to update comment', null, 422);
        } catch (Exception $ex) {

            DB::rollback();
            abort(500, $ex->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/api/v1/comment/delete/{id}",
     * summary="Delete comment",
     * description="Delete comment",
     * operationId="Delete comment",
     * tags={"Comment"},
     * security={
     *     {"sanctum": {}}
     *  }, 
     *  @OA\Parameter(
     *     description="comment id parameter",
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
    public function delete( Request $request, $comment_id )
    {
        try {
            // start the transaction
            DB::beginTransaction();
            $post = (new CommentService())->deleteComment($request, $comment_id);

            if ($post) {
                DB::commit();

                return $this->sendResponse( 'Comment deleted successfully', null, 200 );
            }
            
            return $this->sendResponse( 'Fail to deleted comment', null, 200 );
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
