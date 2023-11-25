<?php
/* The API users */
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    // Send user details to the db
    public function register(Request $request){
        // Validate details
        $validator = Validator::make($request->all(), [
            'name' => 'string|required|max:40',
            'email' => 'string|required|max:40',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                "status" => 422,
                "errors" => $validator->errors(),
            ], 422);
        }else{
            $input = $request->all();

            // Encrypt the password
            $input['password'] = bcrypt($input['password']);
            
            User::create($input);
            
            return response()->json([
                "status"=>200,
                "message"=>"User registered successfully."
            ], 200);
        }

    }

    // Authenticate and login the user
    public function login(Request $request){     
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            
            /** @var \App\Models\User $user **/
            $user = Auth::user();
            $success['name'] = $user->name;

            // Create token for user
            $success['token'] = $user->createToken('DrugsApi', expiresAt:now()->addMonth())->plainTextToken;

            return response()->json([
                "user" => $success,
                "status" => 200,
                "message" => "Login successful."
            ], 200);
            
        }else{
            return response()->json([
                "status" => 400,
                "message" => "Authentication failed."
            ], 400);
        }

    }

    // Log out the user
    public function logout(Request $request){
        $request->user()->token()->revoke();

        return response()->json([
            'status' => 200,
            'message' => 'Successfully logged out'
        ], 200);
    }

    public function index($last_login = null){
        if($last_login !== null){
            $users = User::where('last_login', $last_login);
        }else{
            $users = User::all();
        }
        return response()->json($users, 200);
    }

    public function show($id){
        $user = User::find($id);
        if(!empty($user)){
            return response()->json($user, 200);
        }else{
            return response()->json([
                "status" => 404,
                "message" => "User not found."
            ], 404);
        }
    }
}
