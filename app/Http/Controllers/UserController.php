<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail;
use App\Mail\SendMailreset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Str;
use Illuminate\Auth\Events\PasswordReset;

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
    public function forgotPassword(Request $request){
        // $request->validate(['email' => 'required|email']);
        
        $email = $request->only('email');
        $rules = ['email'=>'required:users,email'];
         $validator = Validator::make($request->all(), $rules);
         if ($validator->fails()) {
            // handler errors
            $erros = $validator->errors();
            // echo $erros;
            return $erros;
         }else{
             $user = User::where('email', '=', $email)->first();
             try { 
                 // verify the credentials and create a token for the user
                 if (! $token = JWTAuth::fromUser($user)) { 
                     return response()->json(['error' => 'invalid_credentials'], 401);
                 } 
             } catch (JWTException $e) { 
                 // something went wrong 
                 return response()->json(['error' => 'could_not_create_token'], 500); 
             } 
             // if no errors are encountered we can return a JWT 
        //  return response()->json(compact('token')); 

            $status = Password::sendResetLink($email);
        
            return $status === Password::RESET_LINK_SENT
                    ? response()->json(['status' => $status])
                    : response()->json(['status' => $status]);

         }

    }
    public function resetPassword(Request $request)
     {   
        // $this->validate($request, [
        //         'token' => 'required',
        //         'email' => 'required|email',
        //         'password' => 'required|confirmed',
        // ]); 
        $rules = ['email'=>'required:users,email','password' => 'required|confirmed',
                    'token'=>'required  '];
         $validator = Validator::make($request->all(), $rules);

         if ($validator->fails()) {
            // handler errors
            $erros = $validator->errors();
            // echo $erros;
            return $erros;
         }else{
            $credentials = $request->only(
                    'email', 'password', 'password_confirmation', 'token'
            );  
            // $response = $request->password->reset($credentials, function($user, $password) {
            //         $user->password = bcrypt($password);
            //         $user->save();
            //         $this->auth->login($user);
            // }); 
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    // $user->forceFill([
                    //     'password' => Hash::make($password)
                    // ])->setRememberToken(Str::random(60));
                    $user->password = bcrypt($password);
                    $user->save();
         
                    event(new PasswordReset($user));
                }
            );
         
            return $status === Password::PASSWORD_RESET
                        ? response()->json(['status', __($status)])
                        :response()->json(['email' => __($status)]);
            return json_encode($response);
            

         }
     } 


    //  public function iSaveithere(Request $request){//this function is to create a token
    //         //for users from their email
    //     // $request->validate(['email' => 'required|email']);
    //     $rules = ['email'=>'required:users,email'];
    //      $validator = Validator::make($request->all(), $rules);
    //      if ($validator->fails()) {
    //         // handler errors
    //         $erros = $validator->errors();
    //         // echo $erros;
    //         return $erros;
    //      }else{
    //          $email = $request->input('email');
    //          $user = User::where('email', '=', $email)->first();
    //          try { 
    //              // verify the credentials and create a token for the user
    //              if (! $token = JWTAuth::fromUser($user)) { 
    //                  return response()->json(['error' => 'invalid_credentials'], 401);
    //              } 
    //          } catch (JWTException $e) { 
    //              // something went wrong 
    //              return response()->json(['error' => 'could_not_create_token'], 500); 
    //          } 
    //          // if no errors are encountered we can return a JWT 
             
    //      return response()->json(compact('token')); 
        
    //      }

    // }
}