<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\PaymentExemption;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PaymentController extends Controller
{
    //Page for recording a payment
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

    //Page for payment history
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

        $exemptedPayments = PaymentExemption::with(['reservation', 'guest', 'user.office'])
            ->where('reservation_id', $id)
            ->get();

        return Inertia::render('Admin/Payment/ReservationPaymentHistory/ReservationPaymentHistory', [
            'reservationPaymentHistory' => $reservationPaymentHistory,
            'exemptedPayments' => $exemptedPayments
        ]);
    }

    //Change payment type to pay later
    public function payLater(Request $request)
    {
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)->findOrFail($request->id);

        DB::transaction(function () use ($reservation) {
            $reservation->payment_type = 'pay_later';
            $reservation->save();
        });

        return to_route('reservation.show', ['id' => $reservation->id])->with('success', 'Successfully change to pay later.');
    }

    //Page for exempting a payment
    public function exemptPaymentForm(int $id)
    {
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)
            ->with([
                'guests' => function($query) {
                    $query->whereHas('stayDetails', function($q) {
                        $q->whereIn('status', ['confirmed', 'checked_in'])
                          ->where('is_exempted', false);
                    })
                    ->with('stayDetails.bed.room');
                }
            ])
            ->findOrFail($id);

        return Inertia::render('Admin/Payment/ExemptPayment/ExemptPaymentForm', [
            'reservation' => $reservation
        ]);
    }

    public function exemptPayment(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'selected_guest_id' => ['required', 'exists:guests,id'],
            'reason' => ['required', 'string'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $reservation = Reservation::findOrFail($validated['reservation_id']);
                $guest = Guest::findOrFail($validated['selected_guest_id']);
                $stayDetails = $guest->stayDetails;

                if($stayDetails->is_exempted) {
                    throw new \Exception('The selected guest is already exempted.');
                }

                $bed = Bed::whereHas('stayDetails', function ($query) use ($reservation, $guest) {
                    $query->where('reservation_id', $reservation->id)
                        ->where('guest_id', $guest->id);
                })->first();

                if (!$bed || !$bed->price) {
                    throw new \Exception('The selected guest does not have an associated bed.');
                }

                PaymentExemption::create([
                    'reservation_id' => $validated['reservation_id'],
                    'price' => $bed->price,
                    'guest_id' => $guest->id,
                    'user_id' => Auth::user()->id,
                    'reason' => $validated['reason'],
                ]);

                // make guest exempted to payment
                $stayDetails->is_exempted = true;
                $stayDetails->individual_billings = 0;
                $stayDetails->save();

                $reservation->recomputeBillings();
            });
        } catch (\Exception $e) {
            return redirect()->back()->with("error", $e->getMessage());
        }

        return redirect()->route('reservation.show', [
            'id' => $validated['reservation_id']
        ])->with('success', 'Successfully exempted payment.');
    }
}
