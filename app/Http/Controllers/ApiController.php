<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Transactions;


class ApiController extends Controller
{
    // public static $url='https://mobilenig.com/API/';

    public static $apikey='a4f727322a9adf12c344279b71f662ea';
    public static $live_url='https://sandbox.vtpass.com/api/';
    public static $sanboxurl='https://sandbox.vtpass.com/api/';
    public static $email='arinzehill@gmail.com';
    public static $password='Arinze12.';

    
    public  function verifyCustomer(Request $request){
             $billersCode=$request->billersCode;
            $serviceID=$request->serviceID;
            $type=$request->type;
    
            //meter-no-04177195122
            
            $response = Http::withBasicAuth(static::$email,static::$password)
                    ->post(static::$sanboxurl.'merchant-verify', [
                        'billersCode'=>$billersCode,
                        'serviceID' => $serviceID,//or company e.g ikeja electic
                        'type' => $type,//prepaid or post paid //or company prepaid
            ]);
            $res=$response->body();
            $json=json_decode($res,true);
            $response_description=$json['response_description'] ?? '';
            $error=$json['content']['errors'] ?? $json['content']['error'] ?? '';
            // return $json;
            if($response_description==''){
                if($error==''){
                    return response()->json([
                        'success'=>true,     
                        'customer_name'=> $json['content']['Customer_Name'] ?? '',
                        'meter_number'=>  $json['content']['Meter_Number'] ?? '',
                        'address'=>  $json['content']['Address'] ?? '',
                    ], 422);
                }else{
                    return response()->json([
                        'success'=>false,     
                        'error'=> $error ?? '',
                    ], 422);
                }
                
            }else{
                
            return response()->json([
                'success'=>false,     
                'response_description='=> $response_description ?? '',
                'error'=>$error,
            ], 422);
            }
        }
        public  function buyElectricity(Request $request){
            $serviceID=$request->serviceID;
            $variation_code=$request->variation_code;
            $billersCode=$request->billersCode;
            $type=$request->type;
            $phone=$request->phone;
            $amount=$request->amount;
            $date = Carbon::now();// will get you the current date, time 
            $request_id=$date->format("Ymd");
            echo($request_id .Str::random(8));
            
            $response = Http::withBasicAuth(static::$email,static::$password)
                        ->post(static::$sanboxurl.'pay', [
                'request_id'=>$request_id.Str::random(8),
                'serviceID' => $serviceID,//company to pay to                
                'billersCode' => $billersCode,//meter no
                'variation_code' => $variation_code,//prepaid or post paid
                'amount' => $amount,
                'phone' => $phone,//phone no

            ]);
            $res=$response->body();
            $json=json_decode($res,true);
            // return $json;
            $status=$json['response_description'];
            if($status=='TRANSACTION SUCCESSFUL'){   
                if($variation_code=='prepaid'){
                    $units;
                    switch($serviceID){
                        case 'kano-electric':
                            $units='';
                            break;
                        case 'abuja-electric':
                            $units=$json['PurchasedUnits'];
                            break;
                        case 'eko-electric':
                            $units=$json['mainTokenUnits'];
                            break;
                        case 'ibadan-electric':
                            $units=$json['Units'];
                            break;
                        default:
                        $units=$json['units'] ?? $json['Units'] ?? '';
                        break;
                    }
                    // $units=$json['mainTokenUnits'] ?? $json['units'];
                    // $token=$json['mainToken'] ?? $json['token'];
                    $bonus=$json['bonusToken'] ?? '';
                    $purchase_code=$json['purchased_code'];
                    $requestId=$json['requestId'];
                    
                    Transactions::create($request->all()+['order_id'=>$requestId]);
                        return response()->json([
                            'success'=>true,     
                            'units'=> $units,
                            'bonus'=> $bonus,
                            'requestId'=> $requestId,
                            'purchased_code'=> $purchase_code,
                        ], 422);

                     }else{//for post paid
                        $ref=$json['exchangeReference'];
                        $purchase_code=$json['purchased_code'];
                        
                    return response()->json([
                        'success'=>true,            
                        'exchangeReference'=> $ref,
                        'purchased_code'=> $purchase_code,
                    ], 422);
                }          
            }else  if($status=='TRANSACTION FAILED'){

                return response()->json([
                    'success'=>false,            
                    'error'=> 'check meter number',
                ], 400);
            }else{//if there are other errors from the input such as 
                //no service or amount enterred
                return response()->json([
                    'success'=>false,            
                    'error'=> $json['content']['errors'] ??$json['response_description']?? 'Check input field',
                    'response_description'=> $json['response_description'] ?? 'Check input field',
                ], 504);
            }
        
       
        }
        public  function buyAirtime(Request $request){
            $serviceID=$request->serviceID;
            $phone=$request->phone;
            $amount=$request->amount;
            $date = Carbon::now();// will get you the current date, time 
            $request_id=$date->format("Ymd");

            $response = Http::withBasicAuth(static::$email,static::$password)
            ->post(static::$sanboxurl.'pay', [
                    'request_id'=>$request_id.Str::random(8),
                    'serviceID' => $serviceID,//company to pay to e.g mtn      
                    'amount' => $amount,
                    'phone' => $phone,//phone no
                    ]);
                    $res=$response->body();
                    $json=json_decode($res,true);
                    $status=$json['response_description'];
            if($status=='TRANSACTION SUCCESSFUL'){             
                    $requestId=$json['requestId'];
                    $phone=$json['content']['transactions']
                    ['unique_element'] ?? '';
                   
                    $inputs=Transactions::create( $request->all()+['order_id'=>$requestId]);
                    return response()->json([
                        'success'=>true,     
                        'phone'=> $phone,
                        'requestId'=> $requestId,
                    ], 422);
                    
                        }else  if($status=='TRANSACTION FAILED'){

                            return response()->json([
                                'success'=>false,            
                                'status'=>$status,            
                                'error'=>$json['response_description']?? 'check your inputs',
                            ], 501);
                        }else{//if there are other errors from the input such as 
                            //no service or amount enterred
                            return response()->json([
                                'success'=>false,            
                                'error'=> $json['content']['errors'][0] ?? 'check ur inputs',
                            ], 504);
                        }
                    return $json;

        }
        
        public static function requeryTransactions($request_id,$bonus,$type,$purchased_code){
            $response = Http::withBasicAuth(static::$email,static::$password)
                        ->post(static::$sanboxurl.'requery', [
                'request_id'=>$request_id,
            ]);

            $res=$response->body();
            return $res;
            $json=json_decode($res,true);
            $purchased_code_prepaid=$json['purchased_code'] ?? '';//for post paid
            $token=$json['token'] ?? '';
            $units=$json['units'] ?? '';

            return response()->json([
                'success'=>true,     
                'units'=> $units,
                'bonus'=> $bonus,
                'purchased_code'=>$type=='postpaid' ? $purchased_code_prepaid 
                                   :$purchased_code ,
            ], 422);

}
        // public  function buyAirtime(Request $request){
        //                 $phone=$request->phone;
        //                 $network_id=$request->network_id;
        //                 $amount=$request->amount;
        //                 $response = Http::get(static::$url.'airtime', [
        //                     'username' => 'arinzehills',
        //                     'password' => 'H12LAR1..',
        //                     'phone' => $phone,//phone no
        //                     'network_id' => $network_id,//company to pay to//eg MTN
        //                     'amount' => $amount,
        //                 ]);
        //                 return $response->body();
        //             }

}
