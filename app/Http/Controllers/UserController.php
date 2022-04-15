<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function authenticate(Request $request){
        $credentials = $request->only('email', 'password');
              try {
        if (! $token = JWTAuth::attempt($credentials)) {
                           return response()->json(['error' => 'invalid_credentials'], 400);
                           }
                   } catch (JWTException $e) {
                       return response()->json(['error' => 'could_not_create_token'], 500);
                   }
                   return response()->json(compact('token'));
               }

    public function register(Request $request){
               $validator = Validator::make($request->all(), [
               'name' => 'required|string|max:255',
               'email' => 'required|string|email|max:255|unique:users',
               'password' => 'required|string|min:6|confirmed',]);
           if($validator->fails()){
                   return response()->json($validator->errors()->first(), 400);}
           $user = User::create([
               'name' => $request->get('name'),
               'email' => $request->get('email'),
               'password' => Hash::make($request->get('password')),]);
           $token = JWTAuth::fromUser($user);
           return response()->json(compact('user','token'),201);}
           public function index()
           {
               $admins = User::all();
               $respond = [
                   'status' => 201,
                   'message' =>  "admins",
                   'data' => $admins
               ];
               return $respond;
           }
           public function destroy(Request $request,$id)
    {
        $data = User::find($id);
        $user = JWTAuth::user();
        if (isset($data) && isset($user)) {
            if($user->id==$data->id){
                $all_users = User::all();
                $respond = [
                    'status' => 400,
                    'message' => "can't delete yourself",
                    'data' => $all_users
                ];
                return $respond;
            }

            $data->delete();
            $all_users = User::all();
            $respond = [
                'status' => 201,
                'message' => "User $id deleted successfully",
                'data' => $all_users
            ];
            return $respond;
        }
        $respond = [
            'status' => 400,
            'message' => "User $id is not found",
            'data' => null
        ];
        return $respond;

    }
    public function edit(Request $request, $id)
    {

        $respond = [
            'status' => 201,
            'message' => null,
            'data' => null
        ];
        $data = User::find($id);

        if(!isset($data)){
            $respond["status"]=400;
            $respond["message"]= "User $id doesn't exist";
            return $respond;
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',]);
        if($validator->fails()){
            $respond["status"]=400;
            $respond["message"]= $validator->errors()->first();
            return $respond;
        }
        $email_user =User::where('email',$request->email)->first();
        if(isset($email_user) && $email_user->id !=$data->id) {
            $respond["status"]=400;
            $respond["message"]= "email already exist";
            return $respond;
        }


        $data->update([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),]);



        $respond = [
            'status' => 201,
            'message' => "User $id Edited successfully",
            'data' => $data
        ];
        return $respond;
    }

}

