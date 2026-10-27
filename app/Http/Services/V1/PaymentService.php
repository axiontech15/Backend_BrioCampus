<?php

namespace App\Http\Services\V1;

use App\Models\Payment;

class PaymentService
{
    public function getAllPayments()
    {
        return Payment::all();
    }

    public function getPaymentById(int $id): ?Payment
    {
        return Payment::find($id);
    }

    public function createPayment(array $data): Payment
    {
        return Payment::create($data);
    }

    public function updatePayment(int $id, array $data): bool
    {
        return Payment::where('id', $id)->update($data) > 0;
    }

    public function deletePayment(int $id): bool
    {
        $payment = Payment::find($id);

        if (! $payment) {
            return false;
        }

        $payment->delete();

        return true;
    }
}
