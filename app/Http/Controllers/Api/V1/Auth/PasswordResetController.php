<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\SMS_module;

class PasswordResetController extends Controller
{
    public function reset_password_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $customer = User::Where(['phone' => $request['phone']])->first();

        if (isset($customer)) {
            if(env('APP_MODE')=='demo')
            {
                return response()->json(['message' => trans('messages.otp_sent_successfull')], 200);
            }
            $token = rand(1000,9999);
         //   $country_code = '+91';
        //    $phone = preg_replace('/^\+?1|\|1|\D/', '', ($request['phone']));
            DB::table('password_resets')->insert([
                'email' => $customer->email,
                'token' => $token,
                'created_at' => now(),
            ]);
            // Mail::to($customer['email'])->send(new \App\Mail\PasswordResetMail($token));
            // return response()->json(['message' => 'Email sent successfully.'], 200);
            $response = SMS_module::send($request['phone'],$token);
            if($response == 'success')
            {
                return response()->json(['message' => trans('messages.otp_sent_successfull')], 200);
            }
            else
            {
                return response()->json([
                    'errors' => [
                        ['code' => 'otp', 'message' => trans('messages.failed_to_send_sms')]
                ]], 405);
            }
        }
        return response()->json(['errors' => [
            ['code' => 'not-found', 'message' => 'Phone number not found!']
        ]], 404);
    }

    public function verify_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'reset_token'=> 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user=User::where('phone', $request->phone)->first();
        if (!isset($user)) {
            return response()->json(['errors' => [
                ['code' => 'not-found', 'message' => 'Phone number not found!']
            ]], 404);
        }

        if(env('APP_MODE')=='demo')
        {
            if($request['reset_token']=="1234")
            {
                return response()->json(['message'=>"OTP found, you can proceed"], 200);
            }
            return response()->json(['errors' => [
                ['code' => 'invalid', 'message' => 'Invalid OTP.']
            ]], 400);
        }

        $data = DB::table('password_resets')->where(['email'=>$user->email])->first();
        if (isset($data)) {
            $country_code = '+91';
                //   $receiver = preg_replace('/[0-9]+/', '', $request->phone);

            $ch = curl_init ();
     
         
         $headers  = [
            'x-api-key: XXXXXX',
            'Content-Type: application/json'
        ];
        $postData = ['mobile' => preg_replace('/^\+?91|\|1|\D/', '', ($request->phone)), 
                     'otp' => $request->reset_token];


            curl_setopt($ch, CURLOPT_URL,"https://yza9sioxij.execute-api.ap-south-1.amazonaws.com/Prod/api/Otp/VerifiyOTP");
           curl_setopt($ch, CURLOPT_POST, 1);
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));           
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $err = curl_error($ch);
            if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
	     	}
	     	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);
      
          if ($httpcode == 200) {
            return response()->json(['message'=>"OTP found, you can proceed"], 200);
            } else {
               return response()->json(['errors' => [
            ['code' => 'invalid', 'message' => 'Invalid OTP.']
        ]], 400);
            }
           // return response()->json(['message'=>"OTP found, you can proceed"], 200);
        }
        return response()->json(['errors' => [
            ['code' => 'invalid', 'message' => 'Invalid OTP.']
        ]], 400);
    }

    public function reset_password_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|exists:users,phone',
            'reset_token'=> 'required',
            'password'=> 'required|min:6',
            'confirm_password'=> 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if(env('APP_MODE')=='demo')
        {
            if($request['reset_token']=="1234")
            {
                DB::table('users')->where(['phone' => $request['phone']])->update([
                    'password' => bcrypt($request['confirm_password'])
                ]);
                return response()->json(['message' => 'Password changed successfully.'], 200);
            }
            return response()->json([
                'message' => 'Phone number and otp not matched!'
            ], 404);
        }

        
      //  $data = DB::table('password_resets')->where(['token' => $request['reset_token']])->first();
       // if (isset($data)) {
            if ($request['password'] == $request['confirm_password']) {
                DB::table('users')->where(['phone' => $request['phone']])->update([
                    'password' => bcrypt($request['confirm_password'])
                ]);
                DB::table('password_resets')->where(['token' => $request['reset_token']])->delete();
                return response()->json(['message' => 'Password changed successfully.'], 200);
            }
            return response()->json(['errors' => [
                ['code' => 'mismatch', 'message' => 'Password did,t match!']
            ]], 401);
       // }
     //   return response()->json(['errors' => [
     //       ['code' => 'invalid', 'message' => trans('messages.invalid_otp')]
     //   ]], 400);
    }
}
