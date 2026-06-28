<?php

namespace App\Http\Controllers;

use App\Services\SchoolProgramService;
use Illuminate\Http\Request;

class CouponVerificationController extends Controller
{
    public function __construct(private SchoolProgramService $service) {}

    public function showVerifyForm()
    {
        return view('public.coupon-verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:20',
        ]);

        $result = $this->service->verifyCoupon($request->coupon_code);

        return back()->with('verification_result', $result)->withInput();
    }

    public function use(Request $request)
    {
        $request->validate([
            'coupon_code'  => 'required|string|max:20',
            'code_parrain' => 'required|string|max:20',
        ]);

        try {
            $enrollment = $this->service->useCoupon($request->coupon_code, $request->code_parrain);
            return back()->with('use_success', $enrollment);
        } catch (\Exception $e) {
            return back()->with('use_error', $e->getMessage())->withInput();
        }
    }
}
