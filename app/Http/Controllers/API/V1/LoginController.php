<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\StoreLoginRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LoginController extends Controller
{

    /**
     * @OA\Get(
     * path="/api/v1/user/login",
     * summary="Login in",
     * description="Login by email, password",
     * operationId="Login",
     * tags={"User"},
     *  @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      example="addy@xyz.com",
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *  @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      example="password",
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *  @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
     *        )
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        $request->validated();

        try {
            $email      = $request->email;
            $password   = $request->password;

            if (Auth::attempt(['email' => $email, 'password' => $password])) {

                $user = Auth::user();
                return new UserResource($user);
            }

            abort(401, 'Sorry, wrong email address or password. Please try again');
        } catch (Exception $ex) {

            abort($ex->getStatusCode(), $ex->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/api/v1/user/register",
     * summary="Register",
     * description="Api to register users",
     * operationId="Register",
     * tags={"User"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  required={"name","email", "password", "password_confirmation"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                @OA\Property(
     *                     property="password_confirmation",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
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
    public function register(RegisterRequest $request)
    {
        $request->validated();

        try {
            DB::beginTransaction();
            $user   = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password)
            ]);


            if ($request->hasFile('media')) {
                $user->addMediaFromRequest('media')
                    ->toMediaCollection('user');
            }

            DB::commit();
            
            return (new UserResource($user))
                ->response()
                ->setStatusCode(200);
        } catch (Exception $ex) {

            DB::rollback();
            abort(500, $ex->getMessage());
        }
    }
}
