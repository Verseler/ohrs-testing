<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\ORNumber;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function paymentForm($id)
    {
        $reservation = Reservation::findOrFail($id);
        return Inertia::render('ReservationManagement/PaymentReservationForm', [
            'reservation' => $reservation
        ]);
    }

    public function generateORNumber()
    {
        $date = now()->format('Ymd');
        $lastOR = ORNumber::orderBy('id', 'desc')->first();

        $sequence = $lastOR ? $lastOR->sequence + 1 : 1;
        $ORNumber = $date . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        ORNumber::create([
            'or_number' => $ORNumber,
            'sequence' => $sequence
        ]);

        return $ORNumber;
    }

    //make a payment for reservation
    public function payment(Request $request)
    {
        $reservation = Reservation::findOrFail($request->reservation_id);

        $validated = $request->validate([
            'reservation_id'    => ['required', 'numeric'],
            'amount'            => ['required', 'numeric', 'min:1', 
                function ($attribute, $value, $fail) use ($reservation) {
                    if ($value > $reservation->remaining_balance) {
                        $fail('The payment amount must not exceed the remaining balance.');
                    }
                }
            ],
        ], [
            'amount.min' => 'The payment amount must not be zero or less.',
        ]);
        
        try {
            DB::transaction(function () use (&$validated, $reservation) {
                $ORNumber = $this->generateORNumber();
                
                $payment = Payment::create([
                    'reservation_id' => $validated['reservation_id'],
                    'amount' => $validated['amount'],
                    'or_number' => $ORNumber,
                    'payment_date' => now(),
                ]);

                $latestBalance = $reservation->remaining_balance - $payment->amount; 
                
                //Populate needed data for payment receipt
                $hostOffice = Office::findOrFail($reservation->host_office_id);
                $bookBy = "$reservation->first_name $reservation->last_name";
                $validated['host_office_name'] = $hostOffice->name;
                $validated['payment_receipt'] = $payment->or_number;
                $validated['date_issued'] = now()->format('Y-m-d');
                $validated['book_by'] = $bookBy;
                $validated['contact'] = $reservation->phone; 
                $validated['reservation_code'] = $reservation->reservation_code;
                $validated['total_amount_paid'] = $payment->amount;
                $validated['total_billing'] = $reservation->total_billing; 
                $validated['previous_balance'] = $reservation->remaining_balance; 
                $validated['latest_balance'] = $latestBalance; 

                //Update reservation remaining balance
               $reservation->update(['remaining_balance' => $latestBalance]);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => 'Payment processing failed. Please try again.']);
        }

        return to_route('reservation.paymentReceipt', [
            'hostel'            => $validated['host_office_name'],
            'payment_receipt'   => $validated['payment_receipt'],
            'date_issued'       => $validated['date_issued'],
            'book_by'           => $validated['book_by'],
            'contact'           => $validated['contact'],
            'reservation_code'  => $validated['reservation_code'],
            'total_amount_paid' => $validated['total_amount_paid'],
            'total_billing'     => $validated['total_billing'],
            'previous_balance'  => $validated['previous_balance'],
            'latest_balance'    => $validated['latest_balance']
        ]);
    }

    public function paymentReceipt(Request $request)
    {
        return Inertia::render('ReservationManagement/PaymentReceipt', [
            'hostel'             => $request->hostel,
            'paymentReceipt'     => $request->payment_receipt,
            'dateIssued'         => $request->date_issued,
            'bookBy'             => $request->book_by,
            'contact'            => $request->contact,
            'reservationCode'    => $request->reservation_code,
            'totalAmountPaid'    => $request->total_amount_paid,
            'totalBilling'       => $request->total_billing,
            'previousBalance'    => $request->previous_balance,
            'latestBalance'      => $request->latest_balance
        ]);
    }
}
