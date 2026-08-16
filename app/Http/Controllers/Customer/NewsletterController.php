<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('email')
            ], 422);
        }

        $email = strtolower(trim($request->email));

        $existing = Subscriber::where('email', $email)->first();

        if ($existing) {
            if ($existing->status === 'Subscribed') {
                return response()->json([
                    'success' => true,
                    'already_subscribed' => true,
                    'message' => 'You are already subscribed to our newsletter!'
                ]);
            } else {
                $existing->update([
                    'status' => 'Subscribed',
                    'ip_address' => $request->ip()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Congratulations! Welcome back to our newsletter subscription!'
                ]);
            }
        }

        Subscriber::create([
            'email' => $email,
            'status' => 'Subscribed',
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Congratulations! You have successfully subscribed to our newsletter!'
        ]);
    }

    public function toggle(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->email) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 401);
        }

        $email = strtolower(trim($user->email));
        $subscribe = filter_var($request->input('subscribe'), FILTER_VALIDATE_BOOLEAN);

        $subscriber = Subscriber::firstOrCreate(
            ['email' => $email],
            ['status' => $subscribe ? 'Subscribed' : 'Unsubscribed', 'ip_address' => $request->ip()]
        );

        $subscriber->update([
            'status' => $subscribe ? 'Subscribed' : 'Unsubscribed',
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'subscribed' => $subscribe,
            'message' => $subscribe 
                ? 'You have successfully subscribed to our newsletter!' 
                : 'You have unsubscribed from our newsletter.'
        ]);
    }
}
