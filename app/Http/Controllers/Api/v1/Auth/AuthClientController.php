<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\PhoneNumberRequest;
use Illuminate\Http\Request;

class AuthClientController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/auth/client-registers",
     *   tags={"Client register"},
     *   summary="First step",
     *
     *   @OA\Parameter(
     *      name="phone_number",
     *      in="query",
     *      required=true,
     *
     *      @OA\Schema(
     *          type="number"
     *      )
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   )
     * )
     **/
    public function client_register_step_1(PhoneNumberRequest $request)
    {
        $clientService = new ClientService();

        return $clientService->verifyPhone($request);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/client-registers/2",
     *   tags={"Client register"},
     *   summary="last step",
     *
     *   @OA\Parameter(
     *      name="client_register_id",
     *      in="query",
     *      required=true,
     *
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *
     *   @OA\Parameter(
     *      name="sms_code",
     *      in="query",
     *      required=true,
     *
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *
     *   @OA\Parameter(
     *      name="fcm_token",
     *      in="query",
     *      required=false,
     *
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   )
     * )
     **/
    public function client_register_step_2(ClientRegisterRequest $request)
    {
        $clientService = new ClientService();

        return $clientService->setClient($request);
    }
}
