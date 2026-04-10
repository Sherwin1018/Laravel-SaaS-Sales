<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutomationEventOutbox;
use App\Models\SignupIntent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmailStatusController extends Controller
{
    /**
     * Update email delivery status from n8n
     */
    public function update(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'event_name' => 'required|string',
            'email' => 'required|email',
            'status' => 'required|string|in:sent,delivered,failed,bounced',
            'sent_at' => 'nullable|date',
            'user_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            Log::error('Email status validation failed', [
                'errors' => $validator->errors(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        try {
            // Update automation event outbox if applicable
            if (isset($data['event_name'])) {
                $outbox = AutomationEventOutbox::where('event', $data['event_name'])
                    ->where('payload->email', $data['email'])
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($outbox) {
                    $outbox->update([
                        'sent_at' => $data['sent_at'] ? now()->parse($data['sent_at']) : now(),
                        'status' => $data['status'],
                    ]);

                    Log::info('Email status updated in outbox', [
                        'event' => $data['event_name'],
                        'email' => $data['email'],
                        'status' => $data['status']
                    ]);
                }
            }

            // Update signup intent email delivery tracking
            if (in_array($data['event_name'], [
                'account_owner_paid_signup_created',
                'team_member_invited', 
                'customer_portal_invited'
            ])) {
                $signupIntent = SignupIntent::where('email', $data['email'])
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($signupIntent) {
                    $signupIntent->update([
                        'email_delivered_at' => $data['sent_at'] ? now()->parse($data['sent_at']) : now(),
                        'email_status' => $data['status'],
                    ]);

                    Log::info('Signup intent email status updated', [
                        'email' => $data['email'],
                        'event' => $data['event_name'],
                        'status' => $data['status']
                    ]);
                }
            }

            // Update user activation email status if applicable
            if (isset($data['user_id'])) {
                $user = User::find($data['user_id']);
                if ($user) {
                    // Update email verification status if this was an activation email
                    if ($data['event_name'] === 'account_owner_paid_signup_created') {
                        $user->update([
                            'activation_email_sent_at' => $data['sent_at'] ? now()->parse($data['sent_at']) : now(),
                            'activation_email_status' => $data['status'],
                        ]);

                        Log::info('User activation email status updated', [
                            'user_id' => $data['user_id'],
                            'email' => $data['email'],
                            'status' => $data['status']
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Email status updated successfully',
                'data' => [
                    'event_name' => $data['event_name'],
                    'email' => $data['email'],
                    'status' => $data['status'],
                    'updated_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update email status', [
                'error' => $e->getMessage(),
                'request' => $data
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update email status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
