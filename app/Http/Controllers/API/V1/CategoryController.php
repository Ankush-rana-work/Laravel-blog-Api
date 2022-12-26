<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\CategoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Http\Requests\CategoryAddRequest;
use App\Http\Requests\CategoryEditRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategorySingleResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/v1/category",
     * summary="Category listing",
     * description="get all category",
     * operationId="Category",
     * tags={"Category"},
     * security={
     *           {"sanctum": {}}
     *  },
     *  @OA\Parameter(
     *      name="per_page",
     *      in="query",
     *      example="10",
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *  @OA\Parameter(
     *      name="page",
     *      in="query",
     *      example="1",
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
    public function show( Request $request )
    {
        $per_page   = $request->per_page ?? Config::get('constants.pagination_per_page');
        $categories = Category::with(['children','media'])->whereNull('parent_id')->paginate($per_page);

        if (count($categories)) {
            return (new CategoryCollection($categories))->additional(['message' => 'Category listing']);
        }

        return (new CategoryCollection($categories))->additional(['message' => 'No category data available']);
        
    }

    /**
     * @OA\Post(
     * path="/api/v1/category/add",
     * summary="Add Category",
     * description="Add new category",
     * operationId="Add Category",
     * tags={"Category"},
     * security={
     *           {"sanctum": {}}
     *  },
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  required={"name","slug"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="slug",
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
     *                  example={"name": "demo name", "slug": "demo slug" }
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
    public function add(CategoryAddRequest $request)
    {
        $request->validated();
        
        try {

            DB::beginTransaction();
            $post = (new CategoryService())->saveCategory($request);

            if (!empty($post)) {
                DB::commit();
                return new CategorySingleResource($post);
            }

            $this->sendResponse('Fail to create category', null, 422 );

        } catch (Exception $ex) {
            
            DB::rollback();
            abort(500, $ex->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/api/v1/category/edit/{id}",
     * summary="updated category",
     * description="Update category",
     * operationId="Update category",
     * tags={"Category"},
     * security={
     *           {"sanctum": {}}
     *  }, 
     *     @OA\Parameter(
     *         description="category id parameter",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  required={"name","slug"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="slug",
     *                     type="string"
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
    public function edit( CategoryEditRequest $request, $cat_id )
    {
                
        try {

            DB::beginTransaction();

            $category = (new CategoryService())->updateCategory($request, $cat_id);

            if (!empty($category)) {
                DB::commit();
                return new CategorySingleResource($category);
            }

            $this->sendResponse('Fail to update category', null, 422 );

        } catch (Exception $ex) {
            
            DB::rollback();
            abort(500, $ex->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/api/v1/category/delete/{id}",
     * summary="Delete category",
     * description="Delete category",
     * operationId="Delete category",
     * tags={"Category"},
     * security={
     *     {"sanctum": {}}
     *  }, 
     *  @OA\Parameter(
     *     description="category id parameter",
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
    public function delete( Request $request, $cat_id )
    {
        try {
            // start the transaction
            DB::beginTransaction();
            $post = (new CategoryService())->deletePost($request, $cat_id);

            if ($post) {
                DB::commit();

                return $this->sendResponse( 'Category deleted successfully', null, 200 );
            }
            
            return $this->sendResponse( 'Fail to deleted post', null, 200 );
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
