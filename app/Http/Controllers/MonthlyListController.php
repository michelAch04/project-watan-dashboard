<?php

namespace App\Http\Controllers;

use App\Models\MonthlyList;
use App\Models\RequestHeader;
use App\Models\HumanitarianRequest;
use App\Models\RequestStatus;
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
        $monthlyListItems = MonthlyList::with([
            'requestHeader.requestStatus',
            'requestHeader.sender',
            'requestHeader.humanitarianRequest.voter.city'
        ])
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
            'request_id' => 'required|exists:request_headers,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020'
        ]);

        $user = Auth::user();

        // Check if request exists and user has access
        $requestHeader = RequestHeader::findOrFail($validated['request_id']);

        // Verify user can access this request (either sender or can view it)
        if ($requestHeader->sender_id !== $user->id && !$user->hasRole('hor')) {
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
            $monthlyListItems = MonthlyList::with(['requestHeader.humanitarianRequest'])
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
                $originalHeader = $item->requestHeader;
                $originalHumanitarian = $originalHeader->humanitarianRequest;

                // Determine status based on user role
                $statusName = $user->hasRole('hor')
                    ? RequestStatus::STATUS_FINAL_APPROVAL
                    : RequestStatus::STATUS_PUBLISHED;

                $status = RequestStatus::getByName($statusName);

                // Create new request header
                $newRequestHeader = RequestHeader::create([
                    'request_number' => RequestHeader::generateRequestNumber(),
                    'request_date' => now(),
                    'request_status_id' => $status->id,
                    'sender_id' => $user->id,
                    'current_user_id' => $user->hasRole('hor') ? null : $user->manager_id,
                    'reference_member_id' => $originalHeader->reference_member_id,
                    'notes' => $originalHeader->notes,
                    'ready_date' => now()->addDays(7), // Default ready date
                ]);

                // Create new humanitarian request
                HumanitarianRequest::create([
                    'request_header_id' => $newRequestHeader->id,
                    'voter_id' => $originalHumanitarian->voter_id,
                    'subtype' => $originalHumanitarian->subtype,
                    'amount' => $originalHumanitarian->amount,
                    'budget_id' => $originalHumanitarian->budget_id,
                ]);

                $publishedCount++;

                // Create notification for manager if not HOR
                if (!$user->hasRole('hor') && $user->manager_id) {
                    \App\Models\InboxNotification::createForUser(
                        $user->manager_id,
                        $newRequestHeader->id,
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
