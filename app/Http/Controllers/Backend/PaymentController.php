<?php

// app/Http/Controllers/PaymentController.php
namespace App\Http\Controllers\Backend;

use App\Models\Payment;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['user', 'userCourse.course'])->get();
        return view('payments.index', compact('payments'));
    }



    // In PaymentController
    public function create()
    {
        // Check if there's pending enrollment data
        $enrollmentData = session('pending_enrollment');
        
        if (!$enrollmentData) {
            return redirect()->route('courses.index')
                ->with('error', 'No pending enrollment found. Please select a course first.');
        }

        // Get the course details
        $course = UserCourse::findOrFail($enrollmentData['course_id']);

        return view('backend.pages.student.payment', compact('course', 'enrollmentData'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Get enrollment data from session
            $enrollmentData = session('pending_enrollment');
            
            if (!$enrollmentData) {
                throw new \Exception('Enrollment session expired. Please start over.');
            }

            // Validate payment receipt
            $validated = $request->validate([
                'payment_receipt' => 'required|file|mimes:jpeg,png,pdf,jpg|max:2048',
            ]);

            // Get course details
            $course = UserCourse::findOrFail($enrollmentData['course_id']);
            $fileName = 'receipt_' . time() . '_' . Str::random(10) . '.' . $request->file('payment_receipt')->getClientOriginalExtension();
            $path = $request->file('payment_receipt')->storeAs('payment-receipts', $fileName);
            
            // 1. CREATE USERCOURSE RECORD (only here, after payment validation)

            $userCourse = UserCourse::create([
                'user_id' => auth()->id(),
                'course_id' => $enrollmentData['course_id'],
                'buy_date' => now(),
                'lesson_day' => $enrollmentData['lesson_day'],
                'lesson_hour' => $enrollmentData['lesson_hour'],
                'status' => 'approved',
                'lesson_count' => $enrollmentData['lesson_count'],
                'payment_status' => 'pending_verification',
                'payment_receipt_path' => $path
            ]);

            // 2. Store payment receipt
            

            // 3. Create payment record
            $payment = Payment::create([
                'user_id' => auth()->id(),
                'user_course_id' => $userCourse->id,
                'status' => 'pending_verification',
                'type' => 'bank_transfer',
                'receipt_path' => $path,
                'amount' => $enrollmentData['course_price'],
            ]);

            // Commit transaction
            DB::commit();

            // Clear the session data
            session()->forget('pending_enrollment');

            return redirect()->route('student.my-courses')
                ->with('success', 'Enrollment and payment completed successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->validator)->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
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