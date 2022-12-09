<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BusinessSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\CentralLogics\Helpers;


class SystemController extends Controller
{

    public function restaurant_data()
    {
        $new_order = DB::table('orders')->where(['checked' => 0])->count();
        return response()->json([
            'success' => 1,
            'data' => ['new_order' => $new_order]
        ]);
    }
    
    
    public function otp(){
        $receiver = "8904809218";
        $ch = curl_init ();
     
         
         $headers  = [
            'x-api-key: XXXXXX',
            'Content-Type: application/json'
        ];
        $postData = ['mobile' => $receiver];


            curl_setopt($ch, CURLOPT_URL,"https://yza9sioxij.execute-api.ap-south-1.amazonaws.com/Prod/api/Otp/SendOTP");
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
                $response = 'success';
            } else {
                $response = 'error';
            }
     
      return $response;
    }
    
    public function verifyotp(){
        $receiver = "8904809218";
        $ch = curl_init ();
     
         
         $headers  = [
            'x-api-key: XXXXXX',
            'Content-Type: application/json'
        ];
        $postData = ['mobile' => $receiver, 
                     'otp' => '9687'];


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
                $response = 'success';
            } else {
                $response = 'error';
            }
     
      return $response;
        
    }
    public function settings()
    {
        return view('admin-views.settings');
    }

    public function settings_update(Request $request)
    {
        $request->validate([
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|unique:admins,email,'.auth('admin')->id(),
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:admins,phone,'.auth('admin')->id(),
        ], [
            'f_name.required' => trans('messages.first_name_is_required'),
            'l_name.required' => trans('messages.Last name is required!'),
        ]);

        $admin = Admin::find(auth('admin')->id());

        if ($request->has('image')) {
            $image_name =Helpers::update('admin/', $admin->image, 'png', $request->file('image'));
        } else {
            $image_name = $admin['image'];
        }


        $admin->f_name = $request->f_name;
        $admin->l_name = $request->l_name;
        $admin->email = $request->email;
        $admin->phone = $request->phone;
        $admin->image = $image_name;
        $admin->save();
        Toastr::success(trans('messages.admin_updated_successfully'));
        return back();
    }

    public function settings_password_update(Request $request)
    {
        $request->validate([
            'password' => 'required|same:confirm_password',
            'confirm_password' => 'required',
        ]);

        $admin = Admin::find(auth('admin')->id());
        $admin->password = bcrypt($request['password']);
        $admin->save();
        Toastr::success(trans('messages.admin_password_updated_successfully'));
        return back();
    }

    public function maintenance_mode()
    {
        $maintenance_mode = BusinessSetting::where('key', 'maintenance_mode')->first();
        if (isset($maintenance_mode) == false) {
            DB::table('business_settings')->insert([
                'key' => 'maintenance_mode',
                'value' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('business_settings')->where(['key' => 'maintenance_mode'])->update([
                'key' => 'maintenance_mode',
                'value' => $maintenance_mode->value == 1 ? 0 : 1,
                'updated_at' => now(),
            ]);
        }

        if (isset($maintenance_mode) && $maintenance_mode->value){
            return response()->json(['message'=>'Maintenance is off.']);
        }
        return response()->json(['message'=>'Maintenance is on.']);
    }
}
