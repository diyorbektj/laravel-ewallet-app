<?php

namespace App\Services;

use App\Http\Requests\Client\PhoneNumberRequest;
use App\Models\ClientRegister;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthClientService
{
    public function verifyPhone(PhoneNumberRequest $request): array
    {
        $phone_number = $request->validated()['phone_number'];
        $c_reg = ClientRegister::query()->where('phone_number', $phone_number)->first();
        if ($c_reg && $c_reg->step_1) {
            $seconds = config('client-api.limit_seconds_to_client_registers');
            if ($c_reg->updated_at->diffInSeconds() < $seconds) {
                $sec = $seconds - $c_reg->updated_at->diffInSeconds();

                return response()->error(trans('message.unique_try', ['sec' => $sec]), 422);
            }
        }
        //            $sms_code = (string) rand(1000, 9999);
        $sms_code = '0000';
        $c_reg = $this->createClientRegister($phone_number, $sms_code);

        $result = [
            'client_register_id' => $c_reg->id,
        ];

        return response()->success(result:$result);
    }
    private function createClientRegister($phone_number, $sms_code): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
    {
        return ClientRegister::query()->create([
            'id' => Str::uuid(),
            'phone_number' => $phone_number,
            'sms_code' => $sms_code,
            'step_1' => true,
            'step_2' => false,
            'count' => 1,
        ]);
    }
    public function setClient($request)
    {
        $c_reg = ClientRegister::find($request->client_register_id);
        $minute = config('client-api.limit_minute_to_verification_code_client');

        if ($c_reg && $c_reg->count >= 10) {
            return response()->error(trans('message.many_try'));
        }

        if ($c_reg && ($c_reg->updated_at->diffInMinutes() > $minute || $c_reg->step_2)) {
            return response()->error(trans('message.try_again_step_1'));
        }

        if ($c_reg->sms_code !== $request->sms_code) {
            return response()->error(trans('message.sms_code_not_found'));
        }

        try {
            DB::beginTransaction();
            $c_reg->step_2 = true;
            $c_reg->save();
            $client = Client::where('phone', $c_reg->phone_number)->first();
            if (! $client) {
                $client = Client::create([
                    'phone' => $c_reg->phone_number,
                ]);
            }

            $pro = $this->set_promo_code($client->id);


            if (empty($client->user_id)) {
                $account = new Accounts();
                $account->model_type = Client::class;
                $account->model_id = $client->id;
                $account->save();
                $id = (string) $account->id;
                $str = str_repeat('0', abs(6 - strlen($id)));
                $user = User::create([
                    'login' => 'client'.$id,
                    'password' => bcrypt('client'.$id),
                    'status' => 1,
                ]);
                $client->user_id = $user->id;
                $client->login = $str.$id;
                $client->save();
            }


            if($request->has('fcm_token')) {
                $client->update(['fcm_token' => $request->fcm_token]);
            }
            $tokenResult = $client->user->createToken('Client Access Token');
            DB::commit();
            $result = [
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString(),
            ];

            return response()->success($result);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/auth_error.log'),
            ])->error($e->getMessage(), $e->getTrace());

            return response()->error(trans('message.error_server'), 500);
        }
    }
}
