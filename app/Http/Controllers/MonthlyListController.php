<?php

namespace App\Http\Controllers;

use App\Models\MonthlyList;
use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonthlyListController extends Controller
{
    /**
     * Display monthly list management page
     */
    public function index()
    {
        $user = Auth::user();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Get current monthly list
        $monthlyListItems = MonthlyList::with(['request.requestType', 'request.requestStatus', 'request.requesterCity'])
            ->forUser($user->id)
            ->forMonth($currentMonth, $currentYear)
            ->get();

        return view('humanitarian.monthly-list', compact('monthlyListItems', 'currentMonth', 'currentYear'));
    }

    /**
     * Add request to monthly list
     */
    public function add(HttpRequest $httpRequest)
    {
        $validated = $httpRequest->validate([
            'request_id' => 'required|exists:requests,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020'
        ]);

        $user = Auth::user();

        // Check if request exists and user has access
        $request = Request::findOrFail($validated['request_id']);

        // Verify user can access this request (either sender or can view it)
        if ($request->sender_id !== $user->id && !$user->hasRole('hor')) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot add this request to your monthly list'
            ], 403);
        }

        // Check if already in monthly list
        $exists = MonthlyList::where('user_id', $user->id)
            ->where('request_id', $validated['request_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Request already in monthly list'
            ], 400);
        }

        MonthlyList::create([
            'user_id' => $user->id,
            'request_id' => $validated['request_id'],
            'month' => $validated['month'],
            'year' => $validated['year']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request added to monthly list'
        ]);
    }

    /**
     * Remove request from monthly list (soft delete)
     */
    public function remove($id)
    {
        $user = Auth::user();
        $item = MonthlyList::findOrFail($id);

        if ($item->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $item->update(['cancelled' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Request removed from monthly list'
        ]);
    }

    /**
     * Publish all requests in monthly list
     * This creates copies of the requests with current date
     */
    public function publishAll(HttpRequest $httpRequest)
    {
        $validated = $httpRequest->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020'
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $monthlyListItems = MonthlyList::with('request')
                ->forUser($user->id)
                ->forMonth($validated['month'], $validated['year'])
                ->get();

            if ($monthlyListItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No requests in monthly list'
                ], 400);
            }

            $publishedCount = 0;

            foreach ($monthlyListItems as $item) {
                $originalRequest = $item->request;

                // Create new request based on the template
                $newRequestData = [
                    'request_type_id' => $originalRequest->request_type_id,
                    'request_status_id' => \App\Models\RequestStatus::getByName(\App\Models\RequestStatus::STATUS_PUBLISHED)->id,
                    'sender_id' => $user->id,
                    'current_user_id' => $user->manager_id,
                    'requester_first_name' => $originalRequest->requester_first_name,
                    'requester_father_name' => $originalRequest->requester_father_name,
                    'requester_last_name' => $originalRequest->requester_last_name,
                    'requester_city_id' => $originalRequest->requester_city_id,
                    'requester_ro_number' => $originalRequest->requester_ro_number,
                    'requester_phone' => $originalRequest->requester_phone,
                    'voter_id' => $originalRequest->voter_id,
                    'subtype' => $originalRequest->subtype,
                    'reference_member_id' => $originalRequest->reference_member_id,
                    'amount' => $originalRequest->amount,
                    'notes' => $originalRequest->notes
                ];

                // If HOR, auto-approve
                if ($user->hasRole('hor')) {
                    $newRequestData['request_status_id'] = \App\Models\RequestStatus::getByName(\App\Models\RequestStatus::STATUS_FINAL_APPROVAL)->id;
                    $newRequestData['current_user_id'] = null;
                }

                Request::create($newRequestData);
                $publishedCount++;

                // Create notification for manager if not HOR
                if (!$user->hasRole('hor') && $user->manager_id) {
                    \App\Models\InboxNotification::createForUser(
                        $user->manager_id,
                        $item->request->id,
                        'request_published',
                        'Monthly Request Published',
                        "{$user->name} has published a recurring humanitarian request for your approval."
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully published {$publishedCount} request(s)",
                'redirect' => route('humanitarian.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish requests: ' . $e->getMessage()
            ], 500);
        }
    }
}
