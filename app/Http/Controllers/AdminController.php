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
use App\Mail\Verification_Mail;



class AdminController extends Controller
{
    // Create Token
    public function create_Token($data)
    {
        // date_default_timezone_set('Asia/Karachi');
        // $issued_At = time() + 3600;
        $key = "My_Key";
        $payload = array(
            "iss"  => "http://127.0.0.1:8000",
            "aud"  => "http://127.0.0.1:8000",
            "iat"  => time(),
            // "exp"  => $issued_At,
            "nbf"  => 1357000000,
            "data" => $data,
        );
        //JWT Token Generate with Parameters
        $jwt_token = JWT::encode($payload, $key, 'HS256');
        return $jwt_token;
    }

    // User Registration
    public function register_User(Request $request)
    {
        // Validations
        $request->validate([
            'name'            => 'required|string|min:3',
            'age'             => 'required|string',
            'email'           => 'required|string|email|unique:users',
            'password'        => 'required|confirmed',
            // 'profile_picture' => 'required',
        ]);

        $jwt_token = $this->create_Token(time());
        // DB_Query
        $user_Data   = User::create([
                'name'            => $request->name,
                'age'             => $request->age,
                'email'           => $request->email,
                'password'        => Hash::make($request->password),
                // 'profile_picture' => $request->file('profile_picture')->store('Images_Stored'),
                'remember_token'  => $jwt_token,
        ]);

        // Mail_Sending_Process
        $url       = url('admin/EmailConfirmation/' . $request->email . '/' . $jwt_token);
        Mail::to($request->email)->send(new Verification_Mail($url,'ua758323@gmail.com', $request->name));

        // Check_Link_Send_For_Verification
        if ($user_Data) {
            return response()->json([
                'message' => 'Verification Link has been Sent. Check Your Email',
                'data'    => $user_Data,
            ]);
        } else {
            return response()->json([
                'message' => 'Wrong Credentials',
            ]);
        }

        // return response()->json($request->all());
    }

    // User Account Varification
    public function verify_Email($email, $hash)
    {
        // DB_Query
        $user_Exist = User::where('email', $email)->first();

        // Check_User_Existance
        if (!$user_Exist) {
            return response([
                'message' => 'User Does Not Exists',
            ]);
        } elseif ($user_Exist->email_verified_at != null) {
            return response([
                'message' => 'Link has been Expired',
            ]);
        } elseif ($user_Exist->remember_token != $hash) {
            return response([
                'message' => 'Unauthenticated',
            ]);
        } else {
            $user_Exist->email_verified_at = time();
            $user_Exist->save();
            return response([
                'message' => 'User has been Verified',
            ]);
        }
    }

    // User Login
    public function login_User(Request $request)
    {
        // dd($request->password);

        // Validations
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);
        // DB_Query
        // $password = Hash::check($request->password);
        $user = User::where('email', $request->email)->first();
        // dd($user);
        // if ($user->password == Hash::check($request->password)) {
            // code...
        // Token_Create
            $token = $this->create_Token($user->id);
            $already_exist = token::where('user_id', $user->id)->first();

            // Check_Already_Exist_User
            if ($already_exist) {
                $already_exist->delete();
                token::create([
                    'user_id' => $user->id,
                    'token'   => $token,
                ]);
                return response([
                    'user'  => $user,
                    'token' => $token,
                ]);
            } else {
                token::create([
                    'user_id' => $user->id,
                    'token'   => $token,
                ]);

                return response([
                    'user'  => $user,
                    'token' => $token,
                ]);
            }
        // }
    }

    // User Logout
    public function logout_User(Request $request)
    {
        $Token = $request->bearerToken();
        $decode = JWT::decode($Token, new Key('My_Key', 'HS256'));
        $token_exist = token::where('user_id', $decode->data)->first();
        if ($token_exist) {
            $token_exist->delete();
            return response([
                'message' => 'Logout Successfully',
            ]);
        } else {
            return response([
                'message' => 'Already Logout',
            ]);
        }
    }

    // Profile Updated
    public function profile_Update(Request $request)
    {
        // Token Created
        $Token = $request->bearerToken();
        $decode = JWT::decode($Token, new Key('My_Key', 'HS256'));

        // Validation
        $request->validate([
            'name' => 'string|min:3',
        ]);

        // DB_Query
        $user_Data = User::where('id', '=', $decode->data)->first();

        // Checks
        if (isset($user_Data)) {
            if (isset($request->name)) {
                $user_Data->name            = $request->name;
                $user_Data->save();
            }
            if (isset($request->age)) {
                $user_Data->age            = $request->age;
                $user_Data->save();
            }
            if (isset($request->email)) {
                $user_Data->email            = $request->email;
                $user_Data->save();
            }
            if (isset($request->profile_picture)) {
                unlink(storage_path('app/' . $user_Data->profile_picture));
                $user_Data->profile_picture = $request->file('profile_picture')->store('Images_Stored');
                $user_Data->save();
            }
            return response([
                'message' => 'Profile Updated',
                'data'    =>  $user_Data,
            ]);
        } else {
            return response([
                'message' => 'No User Found',
            ]);
        }
    }

    // Password Updated
    public function update_password(Request $request)
    {
        // Token Created
        $Token  = $request->bearerToken();
        $decode = JWT::decode($Token, new Key('My_Key', 'HS256'));

        // Validation
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|confirmed',
        ]);

        // DB_Query
        $user_query     = User::find($decode->data);
        $check_password = Hash::check($request->current_password, $user_query->password);

        // Checks
        if (($user_query and $check_password) == true) {
            $password_update = $user_query->update(['password' => Hash::make($request->new_password)]);
            if (isset($password_update)) {
                return response([
                    'message' => 'Password Updated Successfully',
                ]);
            } else {
                return response([
                    'message' => 'Something Went Wrong',
                ]);
            }
        } else {
            return response([
                'message' => 'Your Current Password is Wrong',
            ]);
        }
    }
}
