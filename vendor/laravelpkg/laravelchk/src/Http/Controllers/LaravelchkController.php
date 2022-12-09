<?php

namespace Laravelpkg\Laravelchk\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaravelchkController extends Controller
{
    public function dmvf(Request $request)
    {
        if (self::is_local()) {
            session()->put(base64_decode('cHVyY2hhc2Vfa2V5'), $request[base64_decode('cHVyY2hhc2Vfa2V5')]);//pk
            session()->put(base64_decode('dXNlcm5hbWU='), $request[base64_decode('dXNlcm5hbWU=')]);//un
            return redirect()->route(base64_decode('c3RlcDM='));//s3
        } else {
            session()->put(base64_decode('cHVyY2hhc2Vfa2V5'), $request[base64_decode('cHVyY2hhc2Vfa2V5')]);//pk
            session()->put(base64_decode('dXNlcm5hbWU='), $request[base64_decode('dXNlcm5hbWU=')]);//un
            return redirect()->route(base64_decode('c3RlcDM='));//s3
        }
    }

    public function actch()
    {
        if (self::is_local()) {
            return response()->json([
                'active' => 1
            ]);
        } else {
            return response()->json([
                'active' => 1
            ]);
        }
    }

    public function is_local()
    {
        return true;
    }
}
