<?php

// app/Http/Controllers/PaymentController.php
namespace App\Http\Controllers\Backend;

use App\Models\Payment;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['user', 'userCourse.course'])->get();
        return view('payments.index', compact('payments'));
    }

    public function create(UserCourse $userCourse)
    {
        return view('backend.pages.student.payment', compact('userCourse'));
    }

    public function store(Request $request, UserCourse $userCourse)
    {
        $request->validate([
            'payment_receipt' => 'required|file|mimes:jpeg,png,pdf|max:2048',
        ]);

        // Store receipt
        $path = $request->file('payment_receipt')->store('payment-receipts');

        // Create payment record
        Payment::create([
            'user_id' => auth()->id(),
            'user_course_id' => $userCourse->id,
            'status' => 'pending_verification',
            'type' => 'bank_transfer',
            'receipt_path' => $path,
        ]);

        // Update enrollment status
        $userCourse->update([
            'status' => 'approved'
        ]);
        
        return redirect()->route('student.my-courses')
            ->with('success', 'Payment submitted successfully! Your enrollment is now active.');
    }

    public function show(Payment $payment)
    {
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        return view('payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'type' => 'required|string',
            'discount_code' => 'nullable|string',
        ]);

        $payment->update($validated);

        return redirect()->route('payments.index')->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return redirect()->route('payments.index')->with('success', 'Payment deleted successfully.');
    }
}