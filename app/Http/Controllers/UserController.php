<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail;
use Validator;
class UserController extends Controller {
    protected $auth;

    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }
    public function register(Request $request){
        $email=$request->email;
        $plainPassword=$request->password;
        $password=bcrypt($request->password);
        $request->request->add(['password'=>$password]);
        // echo $request->email;
        $rules = ['email'=>'unique:users,email'];
         $validator = Validator::make($request->all(), $rules);
        // echo $validator;

        // create the user account
        if ($validator->fails()) {
            // handler errors
            $erros = $validator->errors();
            // echo $erros;
            return $erros;
         }else{
            $created=User::create($request->all());
        $request->request->add(['password'=>$plainPassword]);
        //login now..
        return $this->login($request);
         }
        

    }

    public function login(Request $request){
        $input =$request->only('email','password');
        $jwt_token=null;
        if(!$jwt_token= JWTAuth::attempt($input)){
            return response()->json([
                'message'=> 'Invalid email or password',
                'success'=>false,
            ], 401);
        }
        //get the user
        $user=Auth::user();

        return response()->json([
            'success'=>true,            
            'token'=> $jwt_token,
            'user'=> $user,
        ], 422);
    }
    public function logout(Request $request){
        if(!User::checkToken($request)){
            return response()->json([
                'message'=> 'Token is required',
                'success'=>false,
            ], 422);
        }
        try {
            JWTAuth::invalidate(JWTAuth::parseToken($request->token));
            return response()->json([
                'message'=> 'user logged out successfully!',
                'success'=>true,
            ], 500);
        }catch(JWTException $exception){

            return response()->json([
                'message'=> 'Sorry user cannot be logged out!',
                'success'=>false,
            ], 500);
        }
}

        public function getCurrentUser(Request $request){
            if(!User::checkToken($request)){
                
                return response()->json([
                    'message'=> 'Token is required',
                ], 422);
            }
        
        $user=JWTAuth::parseToken()->authenticate();
        //add is profileUpdated...
        $isProfileUpdated=false;
        if($user->isPicUpdated==1 && $user->isEmailUpdated){
            $isProfileUpdated=true;
        }
        $user->isProfileUpdated= $isProfileUpdated;

         return $user;
    }

    public function update(Request $request){
        $user=$this->getCurrentUser($request);
        // echo $user->id;
        $data=$request->all();
        if(!$user){
            return response()->json([
                'success' => false,
                'message' => 'User is not found'
            ]);
        }
       
        // echo($data['token']);
        unset($data['token']);
        
        echo($request->id);
        $updatedUser = User::where('id', $user->id)->update($data);
        $user =  User::find($user->id);
    
        return response()->json([
            'success' => true, 
            'message' => 'Information has been updated successfully!',
            'user' =>$user
        ]);
    }
}