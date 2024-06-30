<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendOTP;
use App\Models\OneTimePassword;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function sendOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'email|required|exists:users,email'
            ]);
    
            if (!$validated) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $request->errors()
                ]);
            }
    
            $otp = rand(100000, 999999);
            $user = User::where('email', $request->email)->first();
            $makeOtp = OneTimePassword::updateOrCreate(
                [
                    'user_id' => $user->id
                ],
                [
                    'otp' => $otp,
                    'expired' => Carbon::now()->addMinutes(5),
                ]
            );
    
            $data = [
                'title' => 'Reset Password OTP',
                'body' => "Your OTP Code: {$otp}"
            ];
    
            Mail::to($user->email)->send(new SendOTP($data));
    
            return response()->json([
                'status' => true,
                'message' => "OTP successfully sent to {$user->email}",
                'email' => $request->email
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ],400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e
            ],500);
        }
    }

    public function verifOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'email|required|exists:users,email',
                'otp' => 'required|min:6'
            ]);

            $user = User::where('email', $request->email)->first();
            $otp = OneTimePassword::where('user_id',$user->id)->first();
            if ($otp and $otp->otp == $request->otp and $otp->expired > Carbon::now()) {
                return response()->json([
                    'status' => true,
                    'message' => "OTP verification success",
                ]);
            }
            return response()->json([
                'status' => false,
                'message' => "OTP not match",
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ],400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e
            ],500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required',
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|min:6'
            ]);
    
            $user = User::find($request->user_id);
    
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ]);
            }
    
            if ($request->new_password == $request->confirm_password) {
                $user->update(['password' => Hash::make($request->new_password)]);
    
                return response()->json([
                    'status' => true,
                    'message' => 'Password reset successfully',
                    'data' => $user
                ]);
            }
    
            return response()->json([
                'status' => false,
                'message' => 'Password not match',
            ],400);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ],400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e
            ],500);
        }
    }
}
