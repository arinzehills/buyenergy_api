<?php

namespace App\Http\Controllers;
use App\Models\Transactions;
use Validator;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    //
    public function getAllTransactions(Request $request){
        $transactions=Transactions::with('transactions')
        ->orderBy('created_at')
        ->get();
      
        $amount=Transactions::where('transaction_type','electricity')
        ->orderBy('created_at')
        ->get();
        $totalUnits=Transactions::sum('units');
        $totalAmount=Transactions::sum('amount');
        $totalTransactions=Transactions::count();//return the list of units used
        $amountList=$this->getUserAmountsList($amount);//return the list of amount
        // $TransactionList=//return the transaction list
        $json = response()->json([
            'total_amount'=>$totalAmount,     
            'total_units'=> $totalUnits,
            'total_transactions'=> $totalTransactions,
            'transactions'=>$transactions,
            'amount_spent'=>$amount,
        ], 422)->header('Content-Type', 'application/json');
      
       
        return $json;
    }
    public function getUserTransactions(Request $request){
        $id=$request->user_id;
        $rules = ['user_id'=>'required:transactions,user_id'];
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            // handler errors
            $erros = $validator->errors();
            // echo $erros;
            return $erros;
         }else{
                $transactions=Transactions::
                orderBy('created_at')
                ->where('user_id',$id)
                ->get();

                $totalUnits=Transactions::where('user_id',$id)->sum('units');
                $totalAmount=Transactions::where('user_id',$id)->sum('amount');
                $totalTransactions=Transactions::where('user_id',$id)->count();
                $unitList=$this->getUserUnitsList($transactions);//return the list of units used
                $amountList=$this->getUserAmountsList($transactions);//return the list of amount
                $totalAmtonElec=Transactions::where(
                    'transaction_type','electricity')
                    ->where(
                        'user_id',$id)  
                     ->sum('amount');
                // $TransactionList=//return the transaction list
                $json = response()->json([
                    'total_amount'=>$totalAmount,     
                    'total_units'=> $totalUnits,
                    'on_electricity'=> $totalAmtonElec,
                    'total_transactions'=> $totalTransactions,
                    'transactions'=>$transactions
                ], 422)->header('Content-Type', 'application/json');
                // return ;
                // echo $amt;
                return $json;
         }    
    }
    
    public function getUserEnergyUsage(Request $request){
        $id=$request->user_id;
        $rules = ['user_id'=>'required:transactions,user_id'];
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            // handler errors
            $erros = $validator->errors();
            // echo $erros;
            return $erros;
         }else{
            $EnergyTransactions=Transactions::
            orderBy('created_at')
            ->where('user_id',$id)
            ->where('transaction_type','electricity')
            ->get();

                $totalUnits=Transactions::where(
                            'transaction_type','electricity')
                            ->where(
                                'user_id',$id)
                            ->sum('units');
                $totalAmount=Transactions::where(
                    'transaction_type','electricity')
                    ->where(
                        'user_id',$id)  
                     ->sum('amount');
                $totalTransactions=Transactions::where('transaction_type','electricity')
                ->count();

                $json = response()->json([
                    'total_amount'=>$totalAmount,     
                    'total_units'=> $totalUnits,
                    'total_transactions'=> $totalTransactions,
                    'transactions'=>$EnergyTransactions
                    
                ], 422)->header('Content-Type', 'application/json');
              
               
                return $json;
         }    
    }
    public function getUserUnitsList($transactions){
        $units=array();
        $created_at=array();
        foreach($transactions as $trs){
             $units[]= $trs->units;
             $created_at[]= $trs->created_at;
         }
        //  $json=json_encode($units);
        //  return $units;
         return  response()->json([
            'units'=>$units,     
        ], 422);
      }
      public function getUserAmountsList($transactions){
        $amount=array();
        $created_at=array();

        foreach($transactions as $trs){
             $amount[]= $trs->amount;
             $created_at[]= $trs->created_at;
         }
        //  $json=json_encode($amount);
        //  return $amount;
        return  response()->json([
            'amounts'=>$amount,     
            'created_at'=> $created_at,
        ], 422);
     }
     public function getUserTransactionList($transactions){
        $amount=array();
        foreach($transactions as $trs){
             $amount[]= $trs->amount;
         }
         $json=json_encode($amount);
         return $amount;
     }
}
