<?php

namespace App\Http\Controllers\API\Transaction;

use App\Http\Controllers\Controller;
use App\Models\PublicationPackage;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LostReportTransactionController extends Controller
{
    public function createLostTransaction(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'lost_report_id' => 'required|exists:lost_reports,id',
                'publication_package_id' => 'required|exists:publication_packages,id',
                'reward' => 'required|numeric',
                'total' => 'required|numeric',
            ]);
            if ($validated) {
                $package = PublicationPackage::findOrFail($request->publication_package_id);
                $expired = Carbon::now()->addDays($package->duration);
                $transaction = Transaction::create([
                    'user_id' => $request->user_id,
                    'lost_report_id' => $request->lost_report_id,
                    'publication_package_id' => $request->publication_package_id,
                    'reward' => $request->reward,
                    'total' => $request->total,
                    'transaction_date' => Carbon::now(),
                    'expired' => $expired,
                ]);
                return response()->json([
                    "status" => true,
                    "message" => 'Transaction successfully created',
                ]);
            }
            return response()->json([
                "status" => false,
                "message" => "Validation error"
            ]);
        } catch (ValidationException $ex) {
            return response()->json([
                "status" => false,
                "message" => "Validation fails",
                "error" => $ex->errors(),
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex
            ]);
        }
    }
}
