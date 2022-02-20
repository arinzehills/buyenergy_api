<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/user',[UserController::class, 'getCurrentUser']);
Route::post('/update', [UserController::class, 'update']);
Route::get('/logout', [UserController::class, 'logout']);
Route::post('/forgotPassword', [UserController::class, 'forgotPassword']);
Route::post('/resetPassword', [UserController::class, 'resetPassword'])->
                                            name('password.reset');
Route::get('/getBalance', [ApiController::class, 'getBalance']);
Route::post('/verifyCustomer', [ApiController::class, 'verifyCustomer']);
Route::post('/buyElectricity', [ApiController::class, 'buyElectricity']);
Route::post('/buyAirtime', [ApiController::class, 'buyAirtime']);
Route::post('/requeryTransactions', [ApiController::class, 'requeryTransactions']);
Route::get('/getAllTransactions', [TransactionController::class, 'getAllTransactions']);
Route::post('/getUserTransactions', [TransactionController::class, 'getUserTransactions']);
Route::post('/getUserEnergyUsage', [TransactionController::class, 'getUserEnergyUsage']);
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return $user->createToken($request->device_name)->plainTextToken;
});
// Route::group(['middleware' => ['web']], function () {
//     // your routes here
    
// // Route::get('/sanctum/csrf-cookie ', [CsrfCookieController::class, 'show']); 
// });