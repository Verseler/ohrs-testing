<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function paymentForm($id)
    {
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)->findOrFail($id);
        return Inertia::render('Admin/Payment/PaymentForm/PaymentForm', [
            'reservation' => $reservation
        ]);
    }


    //make a payment for reservation
    public function payment(Request $request)
    {
        $reservation = Reservation::findOrFail($request->reservation_id);

        $validated = $request->validate([
            'reservation_id' => ['required', 'numeric'],
            'or_number' => ['required', 'string', 'unique:payments,or_number'],
            'or_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['cash', 'online'])],
            'transaction_id' => ['required', 'string', 'unique:payments,transaction_id'],
            'amount' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($reservation) {
                    if ($value > $reservation->remaining_balance) {
                        $fail('The payment amount must not exceed the remaining balance.');
                    }
                }
            ],
        ], [
            'or_number.unique' => 'OR number already existed.',
            'transaction_id.unique' => 'Transaction ID already existed.',
            'amount.min' => 'The payment amount must not be zero or less.',
        ]);

        try {
            DB::transaction(function () use (&$validated, $reservation) {
                $payment = Payment::create([
                    'amount' => $validated['amount'],
                    'or_number' => $validated['or_number'],
                    'or_date' => $validated['or_date'],
                    'transaction_id' => $validated['transaction_id'],
                    'payment_method' => $validated['payment_method'],
                    'reservation_id' => $validated['reservation_id'],
                ]);

                $latestBalance = $reservation->remaining_balance - $payment->amount;

                //Update reservation remaining balance
                $reservation->update(['remaining_balance' => $latestBalance]);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => 'Payment processing failed. Please try again.']);
        }

        return to_route('reservation.paymentHistory', ['id' => $validated['reservation_id']])->with('success', 'Successfully record a payment.');
    }
    public function paymentHistory(int $id)
    {
        $reservationPaymentHistory = Reservation::where('id', $id)
            ->where('hostel_office_id', Auth::user()->office_id)
            ->with([
                'payments' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->first();

        return Inertia::render('Admin/Payment/ReservationPaymentHistory/ReservationPaymentHistory', [
            'reservationPaymentHistory' => $reservationPaymentHistory
        ]);
    }

    public function payLater(Request $request)
    {
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)->findOrFail($request->id);

        DB::transaction(function () use ($reservation) {
            $reservation->payment_type = 'pay_later';
            $reservation->save();
        });

        return to_route('reservation.show', ['id' => $reservation->id])->with('success', 'Successfully change to pay later.');
    }
}
