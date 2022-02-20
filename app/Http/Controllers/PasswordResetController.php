<?php

namespace App\Http\Controllers;
use Mail;
use App\Mail\SendMailreset;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    //
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
            //  try { 
            //      // verify the credentials and create a token for the user
            //      if (! $token = JWTAuth::fromUser($user)) { 
            //          return response()->json(['error' => 'invalid_credentials'], 401);
            //      } 
            //  } catch (JWTException $e) { 
            //      // something went wrong 
            //      return response()->json(['error' => 'could_not_create_token'], 500); 
            //  } 
             // if no errors are encountered we can return a JWT 
        //  return response()->json(compact('token')); 

            $status = Password::sendResetLink($email);
        
            return $status === Password::RESET_LINK_SENT
                    ? response()->json(['status' => $status])
                    : response()->json(['email' => $status]);

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
            // return json_encode($response);
            

         }
     } 

}
