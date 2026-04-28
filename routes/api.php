<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;

Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Incorrect credentials.'], 422);
    }

    return response()->json([
        'token' => $user->createToken('mini-crm-token')->plainTextToken
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    Route::get('/dashboard/stats', function () {
        return response()->json([
            'total_companies' => App\Models\Company::count(),
            'total_contacts' => App\Models\Contact::count(),
        ]);
    });

    Route::get('/companies/trashed', [CompanyController::class, 'trashed']);
    Route::post('/companies/{id}/restore', [CompanyController::class, 'restore']);
    Route::apiResource('companies', CompanyController::class);

    Route::apiResource('contacts', ContactController::class);
});