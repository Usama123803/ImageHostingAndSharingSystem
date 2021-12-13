<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
//Token
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
//Models
use App\Models\User;
use App\Models\token;
use App\Models\Uploadphoto;
use App\Models\PhotoPermission;

class UploadPhotoController extends Controller
{
    public function upload_Photo(Request $request) {

        // Token Create
        $jwt_token = $request->bearerToken();
        $key       = 'My_Key';
        $decoded   = JWT::decode($jwt_token, new Key($key, 'HS256'));
        $user_ID   = $decoded->data;

        // Validations
        $request->validate([
            'upload_Photo' => 'required',
            'privacy'      => 'required|string',
        ]);

        if (($request->privacy == 'Public' or $request->privacy == 'public') or ($request->privacy == 'Private' or $request->privacy == 'private')) 
        {
            $upload_Photo = null;
            if ($request->file('upload_Photo') != null) 
            {
                
                $upload_Photo = $request->file('upload_Photo')->getClientOriginalName();
                $path         = 'C:/xampp/htdocs/FinalMonthPF/Project1/CombineProject1/storage/app/Upload_Photos/' . $upload_Photo;
            }

            // DB_Query
            $upload_Photo_Query    = Uploadphoto::create([
                    'user_ID'      => $user_ID,
                    'path'         => $path,
                    'upload_Photo' => $upload_Photo,
                    'privacy'      => $request->privacy,
            ]);

            // Check_Link_Send_For_Verification
            if (isset($upload_Photo_Query)) 
            {
                return response([
                    'message' => 'Photos Uploaded',
                    'data'    => $upload_Photo_Query,
                ]);
            } 
            else {
                return response([
                    'message' => 'Wrong Credentials',
                ]);
            } 
        } else {
            return response([
                'message' => 'You have to required place Public / Private in Privacy',
            ]);
        }
    }

    public function show_Photo(Request $request) {
        $jwt_token = $request->bearerToken();
        $key       = 'My_Key';
        $decoded   = JWT::decode($jwt_token, new Key($key, 'HS256'));
        $userID    = $decoded->data;

        return Uploadphoto::all()->where('user_ID',$userID);
    }

    public function delete_Photo(Request $request , $id) {
        // Token Created
        $jwt_token = $request->bearerToken();
        $key       = 'My_Key';
        $decoded   = JWT::decode($jwt_token, new Key($key, 'HS256'));
        $userID    = $decoded->data;

        // DB_Query
        $find_photo   = Uploadphoto::where('user_ID',$userID)->where('id',$id)->first();
        $delete_photo = $find_photo->delete();

        // Checks
        if($delete_photo){
            return response([
                'status'  => 200,
                'message' => 'Image deleted'
            ]);
        }else{
            return response([
                'status'  => 404,
                'message' => 'Image not deleted'
            ]);
        }
    }

    public function search_Specifically(Request $request) {
        // Token Created
        $jwt_token = $request->bearerToken();
        $key       = 'My_Key';
        $decoded   = JWT::decode($jwt_token, new Key($key, 'HS256'));
        $userID    = $decoded->data;

        // Validation
        $request->validate([
            'upload_Photo' => 'required|string',
        ]);

        // DB_Query
        $upload_Photo = Uploadphoto::where('upload_Photo', 'LIKE', '%' . $request->upload_Photo . '%', 'AND', 'user_ID', '=', $userID)->get();

        // Check
        if (json_decode($upload_Photo)) {
            return response([
                'Searched Images' => $upload_Photo,
            ]);
        } else {
            return response([
                'message' => 'Images Not Found',
            ]);
        }
    }

    // public function public_Photos() {
    //     $upload_Photos = Uploadphoto::whereIn('privacy', array('Public', 'public'))->get();
    //     if (json_decode($upload_Photos)) {
    //         return response([
    //             'upload_Photo' => $upload_Photos,
    //         ]);
    //     } else {
    //         return response([
    //             'message' => 'Images Not Found',
    //         ]);
    //     }
    // }

    public function private_Photos(Request $request) {
        // Token Created
        $jwt_token = $request->bearerToken();
        $key       = 'My_Key';
        $decoded   = JWT::decode($jwt_token, new Key($key, 'HS256'));
        $userID    = $decoded->data;

        // Db_Query
        $upload_Photos = Uploadphoto::whereIn('privacy', array('Private', 'private'))->get();

        // Checks
        if ($upload_Photos) {
            return response([
                'upload_Photo' => $upload_Photos,
            ]);
        } else {
            return response([
                'message' => 'Images Not Found',
            ]);
        }
    }

    public function photo_Permissions_send(Request $request) {
        // Token Created
        $jwt_token = $request->bearerToken();
        $key       = 'My_Key';
        $decoded   = JWT::decode($jwt_token, new Key($key, 'HS256'));
        $userID    = $decoded->data;

        // Validation
        $request->validate([
            'permission_Given_To' => 'required',
        ]);

        // DB_Query
        $user_ID   = User::where('id',$userID)->first();
        $user_Email = User::where('email',$request->permission_Given_To)->first();
        // dd($request->permission_Given_To);

        // Checks
        if($user_Email == null){
            return response([
                    "message" => "User Does not Exist",
                ]);
        } elseif($user_ID->email == $request->permission_Given_To) {
            return response([
                'message'      => 'You cannot send request to yourself',
            ]);
        } else {
            // $already_Sent = PhotoPermission::where('permission_Given_By', '=', $decoded->data, 'AND', 'permission_Given_To', '=', $request->permission_Given_To)->first();
            $send_Permission = PhotoPermission::create([
                'permission_Given_By' => $userID,
                'permission_Given_To' => $request->permission_Given_To,
                'path'                => $request->path,
            ]);
            return response([
                'message'      => 'Send Imag Path',
                'data'         =>  $send_Permission,
            ]);
        }
    }

    public function photo_Permissions_accept(Request $request) {
        // Token Created
        $jwt_token = $request->bearerToken();
        $key       = 'My_Key';
        $decoded   = JWT::decode($jwt_token, new Key($key, 'HS256'));
        $userID    = $decoded->data;

        $request->validate([
            'path' => 'required'
        ]);

        // DB_Query
        $user_ID = Uploadphoto::where('id',$userID)->first();
        // dd($user_ID->path);

        // Checks
        if ($user_ID->path == $request->path) {
            return response([
                "message" => "Link Recieved",
                'path'    => $request->path
            ]);   
        } else {
            return response([
                "message" => "UnKnown Email or Path",
            ]);  
        }
    }
}