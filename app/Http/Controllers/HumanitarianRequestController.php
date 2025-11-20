<?php

namespace App\Http\Controllers;

use App\Models\RequestHeader;
use App\Models\HumanitarianRequest;
use App\Models\RequestStatus;
use App\Models\Voter;
use App\Models\PwMember;
use App\Models\InboxNotification;
use App\Models\BudgetTransaction;
use App\Models\Budget;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HumanitarianRequestController extends Controller
{
    /**
     * Display humanitarian dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Get counts for dashboard - now using RequestHeader with humanitarian relationship
        $activeCount = RequestHeader::active()
            ->forUser($user)
            ->whereHas('humanitarianRequest')
            ->count();

        $draftCount = RequestHeader::draftsAndRejects($user)
            ->whereHas('humanitarianRequest')
            ->count();

        $completedCount = RequestHeader::completed()
            ->forUser($user)
            ->whereHas('humanitarianRequest')
            ->count();

        // Get budgets if user is HOR
        $budgets = null;
        if ($user->hasRole('hor')) {
            $budgets = Budget::notCancelled()
                ->with('zone')
                ->whereHas('zone', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->get()
                ->map(function($budget) {
                    $currentMonth = now()->month;
                    $currentYear = now()->year;

                    return [
                        'id' => $budget->id,
                        'description' => $budget->description,
                        'zone' => $budget->zone->name_en,
                        'monthly_amount' => $budget->monthly_amount_in_usd,
                        'current_remaining' => $budget->getRemainingBudgetForMonth($currentYear, $currentMonth),
                        'predicted_end_of_month' => $budget->getPredictedBudgetForMonth($currentYear, $currentMonth)
                    ];
                });
        }

        return view('humanitarian.index', compact('activeCount', 'draftCount', 'completedCount', 'budgets'));
    }

    /**
     * View active requests
     */
    public function active(HttpRequest $httpRequest)
    {
        $user = Auth::user();

        $query = RequestHeader::with([
            'requestStatus',
            'sender',
            'currentUser',
            'referenceMember',
            'humanitarianRequest.voter.city',
            'humanitarianRequest.budget'
        ])
            ->active()
            ->forUser($user)
            ->whereHas('humanitarianRequest');

        // Filter by month if provided
        $month = $httpRequest->input('month');
        $year = $httpRequest->input('year');

        if ($month && $year) {
            $query->whereYear('ready_date', $year)
                  ->whereMonth('ready_date', $month);
        }

        $requests = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get available months for filter (last 12 months)
        $availableMonths = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $availableMonths[] = [
                'month' => $date->month,
                'year' => $date->year,
                'label' => $date->format('F Y')
            ];
        }

        return view('humanitarian.active', compact('requests', 'availableMonths', 'month', 'year'));
    }

    /**
     * View completed requests
     */
    public function completed(HttpRequest $httpRequest)
    {
        $user = Auth::user();

        $query = RequestHeader::with([
            'requestStatus',
            'sender',
            'referenceMember',
            'humanitarianRequest.voter.city',
            'humanitarianRequest.budget'
        ])
            ->completed()
            ->forUser($user)
            ->whereHas('humanitarianRequest');

        // Filter by month/year if provided, default to current month
        $month = $httpRequest->input('month', now()->month);
        $year = $httpRequest->input('year', now()->year);

        if ($month && $year) {
            $query->whereYear('ready_date', $year)
                  ->whereMonth('ready_date', $month);
        }

        $requests = $query->orderBy('updated_at', 'desc')->paginate(15)->appends($httpRequest->only(['month', 'year']));

        // Get available months for filter (last 12 months)
        $availableMonths = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $availableMonths[] = [
                'month' => $date->month,
                'year' => $date->year,
                'label' => $date->format('F Y')
            ];
        }

        return view('humanitarian.completed', compact('requests', 'availableMonths', 'month', 'year'));
    }

    /**
     * View drafts and rejected requests
     */
    public function drafts()
    {
        $user = Auth::user();

        $requests = RequestHeader::with([
            'requestStatus',
            'referenceMember',
            'humanitarianRequest.voter.city'
        ])
            ->draftsAndRejects($user)
            ->whereHas('humanitarianRequest')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('humanitarian.drafts', compact('requests'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $pwMembers = PwMember::active()->orderBy('name')->get();

        return view('humanitarian.create', compact('pwMembers'));
    }

    /**
     * Store new request
     */
    public function store(HttpRequest $httpRequest)
    {
        $validated = $httpRequest->validate([
            'voter_id' => 'required|exists:voters_list,id',
            'subtype' => 'required|string|max:255',
            'reference_member_id' => 'required|exists:pw_members,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'action' => 'required|in:draft,publish',
            'budget_id' => 'nullable|exists:budgets,id',
            'ready_date' => 'nullable|date'
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            // Determine status based on action
            if ($validated['action'] === 'draft') {
                $status = RequestStatus::getByName(RequestStatus::STATUS_DRAFT);
                $currentUserId = null;
            } else {
                $status = RequestStatus::getByName(RequestStatus::STATUS_PUBLISHED);
                // Set current user to sender's manager (if HOR, auto-approve)
                if ($user->hasRole('hor')) {
                    $status = RequestStatus::getByName(RequestStatus::STATUS_FINAL_APPROVAL);
                    $currentUserId = null;
                } else {
                    $currentUserId = $user->manager_id;
                }
            }

            // Create RequestHeader
            $headerData = [
                'request_number' => RequestHeader::generateRequestNumber(),
                'request_date' => now(),
                'request_status_id' => $status->id,
                'reference_member_id' => $validated['reference_member_id'],
                'sender_id' => $user->id,
                'current_user_id' => $currentUserId,
            ];

            // Handle budget allocation for HOR users who publish with budget
            if ($user->hasRole('hor') && $validated['action'] === 'publish' && !empty($validated['budget_id']) && !empty($validated['ready_date'])) {
                // Verify budget belongs to HOR's zone
                $budget = Budget::notCancelled()->with('zone')->findOrFail($validated['budget_id']);
                if ($budget->zone->user_id !== $user->id) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only use budgets from your own zones'
                    ], 403);
                }

                // Check and refill budget if needed
                $budget->checkAndRefill();

                // Extract month/year from ready_date for proper monthly budget checking
                $readyDate = \Carbon\Carbon::parse($validated['ready_date']);
                $readyMonth = $readyDate->month;
                $readyYear = $readyDate->year;

                // Check if budget has enough for the ready_date month
                if (!$budget->hasEnoughBudget($validated['amount'], $readyYear, $readyMonth)) {
                    $remaining = $budget->getRemainingBudgetForMonth($readyYear, $readyMonth);
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient budget for ' . $readyDate->format('F Y') . '. Remaining: $' . number_format($remaining, 2)
                    ], 400);
                }

                // Add ready date to header data
                $headerData['ready_date'] = $validated['ready_date'];
            }

            // Create the request header
            $requestHeader = RequestHeader::create($headerData);

            // Create the humanitarian request
            $humanitarianData = [
                'request_header_id' => $requestHeader->id,
                'voter_id' => $validated['voter_id'],
                'subtype' => $validated['subtype'],
                'amount' => $validated['amount'],
                'notes' => $validated['notes'] ?? null,
            ];

            // Add budget_id if provided
            if (!empty($validated['budget_id'])) {
                $humanitarianData['budget_id'] = $validated['budget_id'];
            }

            $humanitarianRequest = HumanitarianRequest::create($humanitarianData);

            // Record budget allocation if HOR user allocated budget
            if ($user->hasRole('hor') && $validated['action'] === 'publish' && !empty($validated['budget_id'])) {
                $budget = Budget::notCancelled()->findOrFail($validated['budget_id']);
                $readyDate = \Carbon\Carbon::parse($validated['ready_date']);

                Log::info("Allocating amount: " . $validated['amount'] . " for new request id: " . $requestHeader->id . " to budget month: " . $readyDate->format('F Y'));

                // Allocate budget (deduct immediately if current month, or schedule for future month)
                $budget->allocateForRequest(
                    $validated['amount'],
                    $validated['ready_date'],
                    $requestHeader->id,
                    "Request #{$requestHeader->request_number} allocated to " . $readyDate->format('F Y')
                );

                Log::info("Budget allocated successfully to " . $readyDate->format('F Y'));
            }

            // Create inbox notification if published
            if ($validated['action'] === 'publish' && $currentUserId) {
                // Increment published count
                $requestHeader->increment('published_count');

                InboxNotification::createForUser(
                    $currentUserId,
                    $requestHeader->id,
                    'request_published',
                    'New Request for Approval',
                    "{$user->name} has published a humanitarian request #{$requestHeader->request_number} for your approval."
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $validated['action'] === 'draft' ? 'Request saved as draft' : 'Request published successfully',
                'redirect' => route('humanitarian.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show request details
     */
    public function show($id)
    {
        $request = RequestHeader::with([
            'requestStatus',
            'sender',
            'currentUser',
            'referenceMember',
            'humanitarianRequest.voter.city',
            'humanitarianRequest.budget'
        ])->findOrFail($id);

        $user = Auth::user();

        // Check permissions
        if (
            $request->sender_id !== $user->id &&
            $request->current_user_id !== $user->id &&
            !$user->hasRole('hor')
        ) {
            abort(403);
        }

        return view('humanitarian.show', compact('request'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $request = RequestHeader::with('humanitarianRequest')->findOrFail($id);
        $user = Auth::user();

        if (!$request->canEdit($user)) {
            abort(403, 'You cannot edit this request');
        }

        $pwMembers = PwMember::active()->orderBy('name')->get();

        return view('humanitarian.edit', compact('request', 'pwMembers'));
    }

    /**
     * Update request
     */
    public function update(HttpRequest $httpRequest, $id)
    {
        $requestHeader = RequestHeader::with('humanitarianRequest')->findOrFail($id);
        $user = Auth::user();

        if (!$requestHeader->canEdit($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot edit this request'
            ], 403);
        }

        $validated = $httpRequest->validate([
            'voter_id' => 'required|exists:voters_list,id',
            'subtype' => 'required|string|max:255',
            'reference_member_id' => 'required|exists:pw_members,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'action' => 'required|in:save,publish'
        ]);

        // Update header data
        $headerUpdateData = [];
        $headerUpdateData['reference_member_id'] = $validated['reference_member_id'];

        // Update humanitarian request data
        $humanitarianUpdateData = [
            'voter_id' => $validated['voter_id'],
            'subtype' => $validated['subtype'],
            'amount' => $validated['amount'],
            'notes' => $validated['notes'] ?? null,
        ];

        // Handle status change if publishing
        if ($validated['action'] === 'publish') {
            $publishedStatus = RequestStatus::getByName(RequestStatus::STATUS_PUBLISHED);
            $headerUpdateData['request_status_id'] = $publishedStatus->id;

            if ($user->hasRole('hor')) {
                $finalStatus = RequestStatus::getByName(RequestStatus::STATUS_FINAL_APPROVAL);
                $headerUpdateData['request_status_id'] = $finalStatus->id;
                $headerUpdateData['current_user_id'] = null;
            } else {
                $headerUpdateData['current_user_id'] = $user->manager_id;

                // Increment published count before creating notification
                $requestHeader->increment('published_count');

                // Create notification with correct message based on published count
                if ($user->manager_id) {
                    $isFirstPublish = $requestHeader->published_count === 1;
                    $message = $isFirstPublish
                        ? "{$user->name} has published a humanitarian request #{$requestHeader->request_number} for your approval."
                        : "{$user->name} has republished humanitarian request #{$requestHeader->request_number} for your approval.";

                    InboxNotification::createForUser(
                        $user->manager_id,
                        $requestHeader->id,
                        'request_published',
                        $isFirstPublish ? 'New Request for Approval' : 'Request Republished for Approval',
                        $message
                    );
                }
            }
        }

        $requestHeader->update($headerUpdateData);
        $requestHeader->humanitarianRequest->update($humanitarianUpdateData);

        return response()->json([
            'success' => true,
            'message' => $validated['action'] === 'save' ? 'Request updated' : 'Request published successfully',
            'redirect' => route('humanitarian.index')
        ]);
    }

    /**
     * Approve request
     */
    public function approve(HttpRequest $httpRequest, $id)
    {
        $requestHeader = RequestHeader::findOrFail($id);
        $user = Auth::user();

        if (!$requestHeader->canApproveReject($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot approve this request'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Check if user has manager (move up hierarchy)
            if ($user->manager_id && $user->manager_id != $user->id) {
                // Move to next level
                $requestHeader->update([
                    'current_user_id' => $user->manager_id
                ]);

                // Create notification
                InboxNotification::createForUser(
                    $user->manager_id,
                    $requestHeader->id,
                    'request_approved',
                    'Request Approved - Awaiting Your Review',
                    "{$user->name} has approved humanitarian request #{$requestHeader->request_number}. It now requires your approval."
                );

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Request approved successfully',
                    'redirect' => route('humanitarian.active')
                ]);
            } else {
                // Reached top (HOR) - need budget selection
                // Return special response to trigger budget modal
                DB::commit();

                return response()->json([
                    'success' => true,
                    'needs_budget_selection' => true,
                    'message' => 'Please select budget and ready date'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request'
            ], 500);
        }
    }

    /**
     * Final approval with budget selection (HOR only)
     */
    public function finalApprove(HttpRequest $httpRequest, $id)
    {
        $requestHeader = RequestHeader::with('humanitarianRequest')->findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole('hor')) {
            return response()->json([
                'success' => false,
                'message' => 'Only HOR can perform final approval'
            ], 403);
        }

        $validated = $httpRequest->validate([
            'budget_id' => 'required|exists:budgets,id',
            'ready_date' => 'required|date'
        ]);

        DB::beginTransaction();
        try {
            // Verify budget belongs to HOR's zone
            $budget = Budget::notCancelled()->with('zone')->findOrFail($validated['budget_id']);
            if ($budget->zone->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only use budgets from your own zones'
                ], 403);
            }

            // Check if budget has enough for the ready_date month
            $readyDate = \Carbon\Carbon::parse($validated['ready_date']);
            $readyMonth = $readyDate->month;
            $readyYear = $readyDate->year;

            // Check and refill budget if needed
            $budget->checkAndRefill();

            // Verify there's enough budget for the specified month
            $requestAmount = $requestHeader->humanitarianRequest->amount;
            if (!$budget->hasEnoughBudget($requestAmount, $readyYear, $readyMonth)) {
                $remaining = $budget->getRemainingBudgetForMonth($readyYear, $readyMonth);
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient budget for ' . $readyDate->format('F Y') . '. Remaining: $' . number_format($remaining, 2)
                ], 400);
            }

            Log::info("Allocating amount: " . $requestAmount . " for request id: " . $requestHeader->id . " to budget month: " . $readyDate->format('F Y'));

            // Update request header with final approval and ready date
            $finalStatus = RequestStatus::getByName(RequestStatus::STATUS_FINAL_APPROVAL);
            $requestHeader->update([
                'request_status_id' => $finalStatus->id,
                'current_user_id' => null,
                'ready_date' => $validated['ready_date']
            ]);

            // Update humanitarian request with budget_id
            $requestHeader->humanitarianRequest->update([
                'budget_id' => $validated['budget_id']
            ]);

            // Allocate budget (deduct immediately if current month, or schedule for future month)
            $budget->allocateForRequest(
                $requestAmount,
                $validated['ready_date'],
                $requestHeader->id,
                "Request #{$requestHeader->request_number} allocated to " . $readyDate->format('F Y')
            );

            // Notify sender
            InboxNotification::createForUser(
                $requestHeader->sender_id,
                $requestHeader->id,
                'request_final_approved',
                'Request Finally Approved',
                "Your humanitarian request #{$requestHeader->request_number} has received final approval and is scheduled for {$readyDate->format('M d, Y')}."
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request finally approved with budget allocated',
                'redirect' => route('humanitarian.active')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject request
     */
    public function reject(HttpRequest $httpRequest, $id)
    {
        $validated = $httpRequest->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $requestHeader = RequestHeader::findOrFail($id);
        $user = Auth::user();

        if (!$requestHeader->canApproveReject($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot reject this request'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $rejectedStatus = RequestStatus::getByName(RequestStatus::STATUS_REJECTED);

            $requestHeader->update([
                'request_status_id' => $rejectedStatus->id,
                'current_user_id' => null,
                'rejection_reason' => $validated['rejection_reason']
            ]);

            // Notify sender
            InboxNotification::createForUser(
                $requestHeader->sender_id,
                $requestHeader->id,
                'request_rejected',
                'Request Rejected',
                "{$user->name} has rejected your humanitarian request #{$requestHeader->request_number}. Reason: {$validated['rejection_reason']}"
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request rejected',
                'redirect' => route('humanitarian.active')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject request'
            ], 500);
        }
    }

    /**
     * Mark as ready for collection (HOR only)
     */
    public function markReady($id)
    {
        $requestHeader = RequestHeader::findOrFail($id);
        $user = Auth::user();

        if (!$user->can('mark_ready_humanitarian')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $finalApprovalStatus = RequestStatus::getByName(RequestStatus::STATUS_FINAL_APPROVAL);
        if ($requestHeader->request_status_id !== $finalApprovalStatus->id) {
            return response()->json([
                'success' => false,
                'message' => 'Request must be finally approved first'
            ], 400);
        }

        $readyStatus = RequestStatus::getByName(RequestStatus::STATUS_READY_FOR_COLLECTION);
        $requestHeader->update(['request_status_id' => $readyStatus->id]);

        // Notify sender
        InboxNotification::createForUser(
            $requestHeader->sender_id,
            $requestHeader->id,
            'request_ready',
            'Request Ready for Collection',
            "Humanitarian request #{$requestHeader->request_number} is now ready for collection."
        );

        return response()->json([
            'success' => true,
            'message' => 'Request marked as ready for collection'
        ]);
    }

    /**
     * Mark as collected (HOR only)
     */
    public function markCollected($id)
    {
        $requestHeader = RequestHeader::findOrFail($id);
        $user = Auth::user();

        if (!$user->can('mark_collected_humanitarian')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $readyStatus = RequestStatus::getByName(RequestStatus::STATUS_READY_FOR_COLLECTION);
        if ($requestHeader->request_status_id !== $readyStatus->id) {
            return response()->json([
                'success' => false,
                'message' => 'Request must be ready for collection first'
            ], 400);
        }

        $collectedStatus = RequestStatus::getByName(RequestStatus::STATUS_COLLECTED);
        $requestHeader->update(['request_status_id' => $collectedStatus->id]);

        // Notify sender
        InboxNotification::createForUser(
            $requestHeader->sender_id,
            $requestHeader->id,
            'request_collected',
            'Request Collected',
            "Humanitarian request #{$requestHeader->request_number} has been collected."
        );

        return response()->json([
            'success' => true,
            'message' => 'Request marked as collected'
        ]);
    }

    /**
     * Delete draft (HOR only, draft status, current handler)
     * Uses soft delete by setting cancelled = 1
     */
    public function destroy($id)
    {
        $requestHeader = RequestHeader::findOrFail($id);
        $user = Auth::user();

        if (!$requestHeader->canDelete($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete drafts that you created'
            ], 403);
        }

        $requestNumber = $requestHeader->request_number;
        $requestHeader->update(['cancelled' => 1]);

        return response()->json([
            'success' => true,
            'message' => "Draft #{$requestNumber} deleted successfully"
        ]);
    }

    /**
     * Download request as PDF (placeholder)
     */
    public function download($id)
    {
        $request = RequestHeader::with([
            'requestStatus',
            'sender',
            'referenceMember',
            'humanitarianRequest.voter.city'
        ])->findOrFail($id);

        $user = Auth::user();

        // Only HOR can download final approved requests
        if (!$user->can('final_approve_humanitarian')) {
            abort(403);
        }

        $finalApprovalStatus = RequestStatus::getByName(RequestStatus::STATUS_FINAL_APPROVAL);
        if ($request->request_status_id < $finalApprovalStatus->id) {
            abort(403, 'Request must be finally approved to download');
        }

        // TODO: Implement Arabic PDF generation
        // For now, return a simple view
        return view('humanitarian.download', compact('request'));
    }

    /**
     * Search voters (AJAX)
     */
    public function searchVoters(HttpRequest $httpRequest)
    {
        $search = $httpRequest->input('search');

        // Enforce minimum 2 characters for performance
        if (!$search || strlen($search) < 2) {
            return response()->json([]);
        }

        $user = Auth::user();
        $query = Voter::with('city');

        // If admin, can search all voters
        if (!$user->hasRole('admin')) {
            if ($user->zones()->count() > 0) {
                // User manages zones - can search in all cities within their zones
                $zoneCityIds = $user->zones()
                    ->with('cities')
                    ->get()
                    ->pluck('cities')
                    ->flatten()
                    ->pluck('id')
                    ->unique();
                $query->whereIn('city_id', $zoneCityIds);
            } else if ($user->cities()->count() > 0) {
                // User manages specific cities
                $cityIds = $user->cities()->pluck('id');
                $query->whereIn('city_id', $cityIds);
            }
        }

        $query->search($search);

        $voters = $query->limit(20)->get()->map(function ($voter) {
            return [
                'id' => $voter->id,
                'full_name' => $voter->full_name,
                'first_name' => $voter->first_name,
                'father_name' => $voter->father_name,
                'last_name' => $voter->last_name,
                'city_id' => $voter->city_id,
                'city_name' => $voter->city->name,
                'ro_number' => $voter->ro_number,
                'phone' => $voter->phone,
                'display_text' => "{$voter->full_name} - {$voter->city->name} ({$voter->ro_number})"
            ];
        });

        return response()->json($voters);
    }

    /**
     * Search PW members (AJAX)
     */
    public function searchMembers(HttpRequest $httpRequest)
    {
        $search = $httpRequest->input('search');

        // Enforce minimum 2 characters for performance
        if (!$search || strlen($search) < 2) {
            return response()->json([]);
        }

        $members = PwMember::active()
            ->where('name', 'like', "%{$search}%")
            ->limit(20)
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'phone' => $member->phone
                ];
            });

        return response()->json($members);
    }

    /**
     * Get request amount (AJAX)
     */
    public function getAmount($id)
    {
        $requestHeader = RequestHeader::with('humanitarianRequest')->findOrFail($id);
        return response()->json([
            'amount' => $requestHeader->humanitarianRequest->amount
        ]);
    }

    /**
     * Export monthly requests to PDF
     * TODO: Implement actual PDF generation using Humanitarian Request Format
     */
    public function exportMonthlyPDF(HttpRequest $httpRequest)
    {
        $user = Auth::user();
        $month = $httpRequest->input('month');
        $year = $httpRequest->input('year');

        if (!$month || !$year) {
            abort(400, 'Month and year are required');
        }

        // Get all completed requests for the specified month
        $requests = RequestHeader::with([
            'requestStatus',
            'sender',
            'referenceMember',
            'humanitarianRequest.voter.city',
            'humanitarianRequest.budget'
        ])
            ->completed()
            ->forUser($user)
            ->whereHas('humanitarianRequest')
            ->whereYear('ready_date', $year)
            ->whereMonth('ready_date', $month)
            ->orderBy('request_number')
            ->get();

        // Use download.blade.php for multiple requests
        return view('humanitarian.download', compact('requests', 'month', 'year'));
    }

    /**
     * Export active requests to PDF
     */
    public function exportActivePDF(HttpRequest $httpRequest)
    {
        $user = Auth::user();
        $month = $httpRequest->input('month');
        $year = $httpRequest->input('year');

        if (!$month || !$year) {
            abort(400, 'Month and year are required');
        }

        // Get all active requests for the specified month
        $requests = RequestHeader::with([
            'requestStatus',
            'sender',
            'referenceMember',
            'humanitarianRequest.voter.city',
            'humanitarianRequest.budget'
        ])
            ->active()
            ->forUser($user)
            ->whereHas('humanitarianRequest')
            ->whereYear('ready_date', $year)
            ->whereMonth('ready_date', $month)
            ->orderBy('request_number')
            ->get();

        // Use download.blade.php for multiple requests
        return view('humanitarian.download', compact('requests', 'month', 'year'));
    }
}
