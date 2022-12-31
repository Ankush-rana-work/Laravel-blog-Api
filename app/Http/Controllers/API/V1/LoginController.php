<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserCollection;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Client as OClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserWithoutTokenResource;

class LoginController extends Controller
{
    public $successStatus = 200;
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

        try {
            $email      = $request->email;
            $password   = $request->password;

            if (Auth::attempt(['email' => $email, 'password' => $password])) {

                $user = Auth::user();
                $oClient = OClient::where('password_client', 1)->first();
                $token_result = $this->getTokenAndRefreshToken($oClient, $email, $password);

                if (!empty($token_result)) {
                    $user->passport_token = $token_result;
                }
                return new UserResource($user->load('media', 'tokens'));
            }

            abort(401, 'You have entered an invalid email or password');
        } catch (Exception $ex) {

            abort(500, $ex->getMessage());
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

            return $this->sendResponse('Successfully user has been registered', new UserWithoutTokenResource($user), 201);
        } catch (Exception $ex) {

            DB::rollback();
            abort(500, $ex->getMessage());
        }
    }

    public function getTokenAndRefreshToken(OClient $oClient, $email, $password)
    {
        try {
            $oClient    = OClient::where('password_client', 1)->first();
            $http       = new Client;
            $response   = $http->request('POST', env('APP_URL') . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $oClient->id,
                    'client_secret' => $oClient->secret,
                    'username' => $email,
                    'password' => $password,
                    'scope' => '*',
                ],
            ]);

            return json_decode((string) $response->getBody()->getContents(), true);
        } catch (ClientException $e) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            $messsage_array = json_decode($responseBodyAsString, true);
            return abort(401, $messsage_array['message']);
        } catch (\Exception $ex) {
            die();
            abort(500, $ex->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/api/v1/user/refresh-token",
     * summary="Refresh-token",
     * description="Api to refresh token",
     * operationId="Refresh-token",
     * tags={"User"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  required={"name","email", "password", "password_confirmation"},
     *                 @OA\Property(
     *                     property="refresh_token",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *  @OA\Response(
     *    response=401,
     *    description="validation error",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The refresh token is invalid")
     *        )
     *     )
     * )
     */
    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required'
        ]);

        if ($validator->fails()) {
            return abort(400, $validator->errors()->first());
        }

        try {

            $oClient    = OClient::where('password_client', 1)->first();
            $http         = new Client;
            $response     = $http->request('POST', env('APP_URL') . '/oauth/token', [
                'form_params' => [
                    'grant_type'     => 'refresh_token',
                    'refresh_token' => $request->refresh_token,
                    'client_id'     => $oClient->id,
                    'client_secret' => $oClient->secret,
                    'scope'         => ''
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $this->sendResponse('Successfully token has been refreshed', $result, 200);
        } catch (ClientException $e) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            $messsage_array = json_decode($responseBodyAsString, true);
            return abort(401, $messsage_array['message']);
        } catch (\Exception $ex) {
            abort(500, $ex->getMessage());
        }
    }

    /**
     * @OA\Get(
     * path="/api/v1/user/list",
     * summary="User List",
     * description="User list",
     * operationId="List",
     * tags={"User"},
     *  @OA\Parameter(
     *      name="type",
     *      in="query",
     *      required=true,
     *      example="user or admin",
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *  @OA\Parameter(
     *      name="per_page",
     *      in="query",
     *      required=true,
     *      example="15",
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *  @OA\Response(
     *    response=422,
     *    description="The type field is required",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The type field is required")
     *        )
     *     )
     * )
     */
    public function userList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:admin,user'
        ],[
           'type.in' => 'The selected type must be admin or user' 
        ]);

        if ($validator->fails()) {
            return abort(422, $validator->errors()->first());
        }

        $per_page   = $request->per_page ?? Config::get('constants.pagination_per_page');
        $users      =  User::with(['media'])->withCount(['post']);

        if ($request->type) {
            $users->where('type', $request->type);
        }

        $users = $users->orderBY('id', 'desc')->paginate($per_page);

        if (count($users)) {
            return (new UserCollection($users))->additional(['message' => 'User listing']);
        }

        return (new UserCollection($users))->additional(['message' => 'No user data available']);
    }
}
