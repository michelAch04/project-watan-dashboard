<?php

namespace App\Http\Controllers;

use App\Models\MonthlyList;
use App\Models\RequestHeader;
use App\Models\HumanitarianRequest;
use App\Models\PublicRequest;
use App\Models\DiapersRequest;
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

        // Get current monthly list with all request types
        $monthlyListItems = MonthlyList::with([
            'requestHeader.requestStatus',
            'requestHeader.sender',
            'requestHeader.referenceMember',
            'requestHeader.humanitarianRequest.voter.city',
            'requestHeader.publicRequest.city',
            'requestHeader.diapersRequest.voter.city'
        ])
            ->forUser($user->id)
            ->forMonth($currentMonth, $currentYear)
            ->get();

        return view('monthly-list.index', compact('monthlyListItems', 'currentMonth', 'currentYear'));
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
            $monthlyListItems = MonthlyList::with([
                'requestHeader.humanitarianRequest.budget',
                'requestHeader.publicRequest.budget',
                'requestHeader.diapersRequest.budget'
            ])
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
            $readyDate = now(); // For HOR, ready_date is today

            foreach ($monthlyListItems as $item) {
                $originalHeader = $item->requestHeader;
                $requestType = $originalHeader->getRequestType();

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
                    'ready_date' => $user->hasRole('hor') ? $readyDate : now()->addDays(7),
                ]);

                // Create new request based on type and handle budget allocation for HOR
                if ($requestType === 'humanitarian') {
                    $original = $originalHeader->humanitarianRequest;
                    $newRequest = HumanitarianRequest::create([
                        'request_header_id' => $newRequestHeader->id,
                        'voter_id' => $original->voter_id,
                        'subtype' => $original->subtype,
                        'amount' => $original->amount,
                        'budget_id' => $original->budget_id,
                        'notes' => $original->notes,
                    ]);

                    // If HOR, allocate budget
                    if ($user->hasRole('hor') && $original->budget_id) {
                        $budget = \App\Models\Budget::find($original->budget_id);
                        if ($budget) {
                            $budget->checkAndRefill();
                            $budget->allocateForRequest(
                                $original->amount,
                                $readyDate,
                                $newRequestHeader->id,
                                "Monthly list request #{$newRequestHeader->request_number} allocated to " . $readyDate->format('F Y')
                            );
                        }
                    }
                } elseif ($requestType === 'public') {
                    $original = $originalHeader->publicRequest;
                    $newRequest = PublicRequest::create([
                        'request_header_id' => $newRequestHeader->id,
                        'city_id' => $original->city_id,
                        'description' => $original->description,
                        'requester_full_name' => $original->requester_full_name,
                        'requester_phone' => $original->requester_phone,
                        'amount' => $original->amount,
                        'budget_id' => $original->budget_id,
                        'notes' => $original->notes,
                    ]);

                    // If HOR, allocate budget
                    if ($user->hasRole('hor') && $original->budget_id) {
                        $budget = \App\Models\Budget::find($original->budget_id);
                        if ($budget) {
                            $budget->checkAndRefill();
                            $budget->allocateForRequest(
                                $original->amount,
                                $readyDate,
                                $newRequestHeader->id,
                                "Monthly list request #{$newRequestHeader->request_number} allocated to " . $readyDate->format('F Y')
                            );
                        }
                    }
                } elseif ($requestType === 'diapers') {
                    $original = $originalHeader->diapersRequest;
                    $newRequest = DiapersRequest::create([
                        'request_header_id' => $newRequestHeader->id,
                        'voter_id' => $original->voter_id,
                        'amount' => $original->amount,
                        'budget_id' => $original->budget_id,
                        'notes' => $original->notes,
                    ]);

                    // If HOR, allocate diaper budget
                    if ($user->hasRole('hor') && $original->budget_id) {
                        $diaperBudget = \App\Models\DiaperBudget::find($original->budget_id);
                        if ($diaperBudget) {
                            $diaperBudget->checkAndRefill();
                            $diaperBudget->allocateForRequest(
                                $original->amount,
                                $readyDate,
                                $newRequestHeader->id,
                                "Monthly list request #{$newRequestHeader->request_number} allocated to " . $readyDate->format('F Y')
                            );
                        }
                    }
                }

                $publishedCount++;

                // Create notification for manager if not HOR
                if (!$user->hasRole('hor') && $user->manager_id) {
                    $requestTypeLabel = ucfirst($requestType);
                    \App\Models\InboxNotification::createForUser(
                        $user->manager_id,
                        $newRequestHeader->id,
                        'request_published',
                        'Monthly Request Published',
                        "{$user->username} has published a recurring {$requestTypeLabel} request for your approval."
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully published {$publishedCount} request(s)",
                'redirect' => route('dashboard')
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
