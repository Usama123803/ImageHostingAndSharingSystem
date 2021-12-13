<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
//Token
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
//Models
use App\Models\User;
use App\Models\token;
use App\Models\password_reset;
//Password_Security
use Illuminate\Support\Facades\Hash;
//Mail_Sending
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordLink;

class PasswordResetController extends Controller
{   
    // Token Created
    public function create_Token($data) {
        date_default_timezone_set('Asia/Karachi');
        $issued_At = time() + 3600;
        $key = "My_Key";
        $payload = array(
            "iss"  => "http://127.0.0.1:8000",
            "aud"  => "http://127.0.0.1:8000",
            "iat"  => time(),
            "exp"  => $issued_At,
            "data" => $data,
        );
        //JWT Token Generate with Parameters
        $jwt_token = JWT::encode($payload, $key, 'HS256');
        return $jwt_token;
    }

    // Validations
    public function valid_Email($email) {
       return !!User::where('email', $email)->first();
    }

    // Reset Link Send
    public function password_Reset_Link(Request $request) {
        // If email does not exist
        if(!$this->valid_Email($request->email)) {
            return response([
                'message' => 'Email does not exist.'
            ]);
        } else {
            $jwt_token = $this->create_Token(time());
            // If email exists
            $url = url('admin/password_Reset_Process/' . $request->email . '/' . $jwt_token);
            Mail::to($request->email)->send(new ResetPasswordLink($request->email,$url));
            $password_Data = password_reset::create([
                'email' => $request->email,
                'token' => $jwt_token,
            ]);
            return response([
                'message' => 'Check your inbox, we have sent a link to reset email.'
            ]);            
        }
    }

    public function password_Reset_Process(Request $request){
        // Validation
        if(!$this->valid_Email($request->email)) {
            return response([
                'message' => 'Email does not exist.'
            ]);
        }

        // DB_Query
        $password_Data = password_reset::where([
           'email' => $request->email,
           'token' => $request->jwt_token
        ]);

        // find email
        $user_Data = User::where('email',$request->email)->first();
        // update password
        if(isset($user_Data)) {
            $user_Data->update([
                'password'=>bcrypt($request->password)
            ]);;
            // reset password response
            return response()->json([
              'message'  => 'Password has been updated.',
              'password' => $request->password,

            ]);
        } else {
            return response()->json([
                'error' => 'Either your email or token is wrong.'
            ]);
        }
    }   

}
