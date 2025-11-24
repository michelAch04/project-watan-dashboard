<?php

namespace App\Http\Controllers;

use App\Models\RequestHeader;
use App\Models\DiapersRequest;
use App\Models\DiapersRequestItem;
use App\Models\RequestStatus;
use App\Models\Voter;
use App\Models\PwMember;
use App\Models\InboxNotification;
use App\Models\DiaperBudget;
use App\Models\DiaperBudgetTransaction;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiapersRequestController extends Controller
{
    /**
     * Display diapers dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Get counts for dashboard
        $activeCount = RequestHeader::active()
            ->forUser($user)
            ->whereHas('diapersRequest')
            ->count();

        $draftCount = RequestHeader::draftsAndRejects($user)
            ->whereHas('diapersRequest')
            ->count();

        $completedCount = RequestHeader::completed()
            ->forUser($user)
            ->whereHas('diapersRequest')
            ->count();

        // Get budgets if user is HOR
        $budgets = null;
        if ($user->hasRole('hor')) {
            $budgets = DiaperBudget::notCancelled()
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
                        'zone' => $budget->zone->name,
                        'current_stock' => $budget->current_stock,
                        'monthly_restock' => $budget->monthly_restock,
                        'remaining_stock' => $budget->getRemainingStockForMonth($currentYear, $currentMonth)
                    ];
                });
        }

        return view('diapers-requests.index', compact('activeCount', 'draftCount', 'completedCount', 'budgets'));
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
            'diapersRequest.voter.city',
            'diapersRequest.diaperBudget',
            'diapersRequest.items'
        ])
            ->active()
            ->forUser($user)
            ->whereHas('diapersRequest');

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

        return view('diapers-requests.active', compact('requests', 'availableMonths', 'month', 'year'));
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
            'diapersRequest.voter.city',
            'diapersRequest.diaperBudget',
            'diapersRequest.items'
        ])
            ->completed()
            ->forUser($user)
            ->whereHas('diapersRequest');

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

        return view('diapers-requests.completed', compact('requests', 'availableMonths', 'month', 'year'));
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
            'diapersRequest.voter.city',
            'diapersRequest.items'
        ])
            ->draftsAndRejects($user)
            ->whereHas('diapersRequest')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('diapers-requests.drafts', compact('requests'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $pwMembers = PwMember::active()->orderBy('first_name')->get();
        $availableSizes = DiapersRequestItem::getAvailableSizes();

        return view('diapers-requests.create', compact('pwMembers', 'availableSizes'));
    }

    /**
     * Store new request
     */
    public function store(HttpRequest $httpRequest)
    {
        $validated = $httpRequest->validate([
            'voter_id' => 'required|exists:voters_list,id',
            'reference_member_id' => 'required|exists:pw_members,id',
            'items' => 'required|array|min:1',
            'items.*.size' => 'required|string',
            'items.*.count' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'action' => 'required|in:draft,publish',
            'diaper_budget_id' => 'nullable|exists:diaper_budgets,id',
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

            // Convert items to quantities array for budget checking
            $quantities = [];
            foreach ($validated['items'] as $item) {
                $size = strtolower($item['size']);
                $quantities[$size] = ($quantities[$size] ?? 0) + $item['count'];
            }

            // Handle budget allocation for HOR users who publish with budget
            if ($user->hasRole('hor') && $validated['action'] === 'publish' && !empty($validated['diaper_budget_id']) && !empty($validated['ready_date'])) {
                // Verify budget belongs to HOR's zone
                $budget = DiaperBudget::notCancelled()->with('zone')->findOrFail($validated['diaper_budget_id']);
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
                if (!$budget->hasEnoughStock($quantities, $readyYear, $readyMonth)) {
                    $remaining = $budget->getRemainingStockForMonth($readyYear, $readyMonth);
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock for ' . $readyDate->format('F Y') . '. Remaining: ' . json_encode($remaining)
                    ], 400);
                }

                // Add ready date to header data
                $headerData['ready_date'] = $validated['ready_date'];
            }

            // Create the request header
            $requestHeader = RequestHeader::create($headerData);

            // Create the diapers request
            $diapersData = [
                'request_header_id' => $requestHeader->id,
                'voter_id' => $validated['voter_id'],
                'notes' => $validated['notes'] ?? null,
            ];

            // Add diaper_budget_id if provided
            if (!empty($validated['diaper_budget_id'])) {
                $diapersData['diaper_budget_id'] = $validated['diaper_budget_id'];
            }

            $diapersRequest = DiapersRequest::create($diapersData);

            // Create items
            foreach ($validated['items'] as $item) {
                DiapersRequestItem::create([
                    'diapers_request_id' => $diapersRequest->id,
                    'size' => strtolower($item['size']),
                    'count' => $item['count']
                ]);
            }

            // Record budget allocation if HOR user allocated budget
            if ($user->hasRole('hor') && $validated['action'] === 'publish' && !empty($validated['diaper_budget_id'])) {
                $budget = DiaperBudget::notCancelled()->findOrFail($validated['diaper_budget_id']);
                $readyDate = \Carbon\Carbon::parse($validated['ready_date']);

                Log::info("Allocating diapers for new request id: " . $requestHeader->id . " to budget month: " . $readyDate->format('F Y'));

                // Allocate budget (deduct immediately if current month, or schedule for future month)
                $budget->allocateForRequest(
                    $quantities,
                    $validated['ready_date'],
                    $requestHeader->id,
                    "Diapers Request #{$requestHeader->request_number} allocated to " . $readyDate->format('F Y')
                );

                Log::info("Diaper budget allocated successfully to " . $readyDate->format('F Y'));
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
                    "{$user->username} has published a diapers request #{$requestHeader->request_number} for your approval."
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $validated['action'] === 'draft' ? 'Request saved as draft' : 'Request published successfully',
                'redirect' => route('diapers-requests.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating diapers request: ' . $e->getMessage());
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
            'diapersRequest.voter.city',
            'diapersRequest.diaperBudget',
            'diapersRequest.items'
        ])->findOrFail($id);

        $user = Auth::user();

        // Check permissions - can view if user is sender, current approver, or has view_diapers permission
        if (
            $request->sender_id !== $user->id &&
            $request->current_user_id !== $user->id &&
            !$user->can('view_diapers')
        ) {
            abort(403);
        }

        return view('diapers-requests.show', compact('request'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $request = RequestHeader::with('diapersRequest.items')->findOrFail($id);
        $user = Auth::user();

        if (!$request->canEdit($user)) {
            abort(403, 'You cannot edit this request');
        }

        $pwMembers = PwMember::active()->orderBy('first_name')->orderBy('last_name')->get();
        $availableSizes = DiapersRequestItem::getAvailableSizes();

        return view('diapers-requests.edit', compact('request', 'pwMembers', 'availableSizes'));
    }

    /**
     * Update request
     */
    public function update(HttpRequest $httpRequest, $id)
    {
        $requestHeader = RequestHeader::with('diapersRequest.items')->findOrFail($id);
        $user = Auth::user();

        if (!$requestHeader->canEdit($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot edit this request'
            ], 403);
        }

        $validated = $httpRequest->validate([
            'voter_id' => 'required|exists:voters_list,id',
            'reference_member_id' => 'required|exists:pw_members,id',
            'items' => 'required|array|min:1',
            'items.*.size' => 'required|string',
            'items.*.count' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'action' => 'required|in:save,publish',
            'diaper_budget_id' => 'nullable|exists:diaper_budgets,id',
            'ready_date' => 'nullable|date'
        ]);

        DB::beginTransaction();
        try {
            // Update header data
            $headerUpdateData = [];
            $headerUpdateData['reference_member_id'] = $validated['reference_member_id'];

            // Update diapers request data
            $diapersUpdateData = [
                'voter_id' => $validated['voter_id'],
                'notes' => $validated['notes'] ?? null,
            ];

            // Delete old items and create new ones
            $requestHeader->diapersRequest->items()->delete();

            foreach ($validated['items'] as $item) {
                DiapersRequestItem::create([
                    'diapers_request_id' => $requestHeader->diapersRequest->id,
                    'size' => strtolower($item['size']),
                    'count' => $item['count']
                ]);
            }

            // Convert items to quantities array for budget checking
            $quantities = [];
            foreach ($validated['items'] as $item) {
                $size = strtolower($item['size']);
                $quantities[$size] = ($quantities[$size] ?? 0) + $item['count'];
            }

            // Handle status change if publishing
            if ($validated['action'] === 'publish') {
                $publishedStatus = RequestStatus::getByName(RequestStatus::STATUS_PUBLISHED);
                $headerUpdateData['request_status_id'] = $publishedStatus->id;

                if ($user->hasRole('hor')) {
                    // HOR publishing - check for budget allocation
                    if (!empty($validated['diaper_budget_id']) && !empty($validated['ready_date'])) {
                        // Verify budget belongs to HOR's zone
                        $budget = DiaperBudget::notCancelled()->with('zone')->findOrFail($validated['diaper_budget_id']);
                        if ($budget->zone->user_id !== $user->id) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'You can only use budgets from your own zones'
                            ], 403);
                        }

                        // Check and refill budget if needed
                        $budget->checkAndRefill();

                        // Extract year and month from ready_date
                        $readyDate = \Carbon\Carbon::parse($validated['ready_date']);
                        $readyYear = $readyDate->year;
                        $readyMonth = $readyDate->month;

                        // Check if budget has enough stock
                        if (!$budget->hasEnoughStock($quantities, $readyYear, $readyMonth)) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Insufficient stock in selected budget for the ready date'
                            ], 400);
                        }

                        // Allocate stock from budget
                        $budget->allocateForRequest($quantities, $validated['ready_date'], $requestHeader->id);

                        // Update request with budget and ready_date
                        $diapersUpdateData['diaper_budget_id'] = $validated['diaper_budget_id'];
                        $headerUpdateData['ready_date'] = $validated['ready_date'];
                    }

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
                            ? "{$user->username} has published a diapers request #{$requestHeader->request_number} for your approval."
                            : "{$user->username} has republished diapers request #{$requestHeader->request_number} for your approval.";

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
            $requestHeader->diapersRequest->update($diapersUpdateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $validated['action'] === 'save' ? 'Request updated' : 'Request published successfully',
                'redirect' => route('diapers-requests.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating diapers request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update request: ' . $e->getMessage()
            ], 500);
        }
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
                    "{$user->username} has approved diapers request #{$requestHeader->request_number}. It now requires your approval."
                );

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Request approved successfully',
                    'redirect' => route('diapers-requests.active')
                ]);
            } else {
                // Reached top (HOR) - need budget selection
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
        $requestHeader = RequestHeader::with('diapersRequest.items')->findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole('hor')) {
            return response()->json([
                'success' => false,
                'message' => 'Only HOR can perform final approval'
            ], 403);
        }

        $validated = $httpRequest->validate([
            'diaper_budget_id' => 'required|exists:diaper_budgets,id',
            'ready_date' => 'required|date'
        ]);

        DB::beginTransaction();
        try {
            // Verify budget belongs to HOR's zone
            $budget = DiaperBudget::notCancelled()->with('zone')->findOrFail($validated['diaper_budget_id']);
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

            // Get quantities from items
            $quantities = [];
            foreach ($requestHeader->diapersRequest->items as $item) {
                $size = strtolower($item->size);
                $quantities[$size] = ($quantities[$size] ?? 0) + $item->count;
            }

            // Verify there's enough budget for the specified month
            if (!$budget->hasEnoughStock($quantities, $readyYear, $readyMonth)) {
                $remaining = $budget->getRemainingStockForMonth($readyYear, $readyMonth);
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for ' . $readyDate->format('F Y') . '. Remaining: ' . json_encode($remaining)
                ], 400);
            }

            Log::info("Allocating diapers for request id: " . $requestHeader->id . " to budget month: " . $readyDate->format('F Y'));

            // Update request header with final approval and ready date
            $finalStatus = RequestStatus::getByName(RequestStatus::STATUS_FINAL_APPROVAL);
            $requestHeader->update([
                'request_status_id' => $finalStatus->id,
                'current_user_id' => null,
                'ready_date' => $validated['ready_date']
            ]);

            // Update diapers request with diaper_budget_id
            $requestHeader->diapersRequest->update([
                'diaper_budget_id' => $validated['diaper_budget_id']
            ]);

            // Allocate budget (deduct immediately if current month, or schedule for future month)
            $budget->allocateForRequest(
                $quantities,
                $validated['ready_date'],
                $requestHeader->id,
                "Diapers Request #{$requestHeader->request_number} allocated to " . $readyDate->format('F Y')
            );

            // Notify sender
            InboxNotification::createForUser(
                $requestHeader->sender_id,
                $requestHeader->id,
                'request_final_approved',
                'Request Finally Approved',
                "Your diapers request #{$requestHeader->request_number} has received final approval and is scheduled for {$readyDate->format('M d, Y')}."
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request finally approved with budget allocated',
                'redirect' => route('diapers-requests.active')
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
                "{$user->username} has rejected your diapers request #{$requestHeader->request_number}. Reason: {$validated['rejection_reason']}"
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request rejected',
                'redirect' => route('diapers-requests.active')
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

        if (!$user->can('mark_ready_diapers')) {
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
            "Diapers Request #{$requestHeader->request_number} is now ready for collection."
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

        if (!$user->can('mark_collected_diapers')) {
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
            "Diapers Request #{$requestHeader->request_number} has been collected."
        );

        return response()->json([
            'success' => true,
            'message' => 'Request marked as collected'
        ]);
    }

    /**
     * Delete draft
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
     * Download request as PDF (placeholder - disabled for now)
     */
    public function download($id)
    {
        abort(404, 'Download functionality is not yet available for diapers requests');
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
                'first_name' => $voter->first_name,
                'father_name' => $voter->father_name,
                'last_name' => $voter->last_name,
                'mother_full_name' => $voter->mother_full_name,
                'city_id' => $voter->city_id,
                'city_name' => $voter->city->name,
                'register_number' => $voter->register_number,
                'phone' => $voter->phone,
                'display_text' => "{$voter->first_name} {$voter->father_name} {$voter->last_name} - {$voter->city->name} ({$voter->register_number})"
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
            ->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'first_name' => $member->first_name,
                    'father_name' => $member->father_name,
                    'last_name' => $member->last_name,
                    'mother_full_name' => $member->mother_full_name,
                    'phone' => $member->phone,
                    'display_text' => trim("{$member->first_name} {$member->father_name} {$member->last_name}")
                ];
            });

        return response()->json($members);
    }

    /**
     * Get diaper budgets for a zone (AJAX)
     */
    public function getDiaperBudgets(HttpRequest $httpRequest)
    {
        $user = Auth::user();

        if (!$user->hasRole('hor')) {
            return response()->json([]);
        }

        $budgets = DiaperBudget::notCancelled()
            ->with('zone')
            ->whereHas('zone', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get()
            ->map(function($budget) {
                return [
                    'id' => $budget->id,
                    'description' => $budget->description,
                    'zone_name' => $budget->zone->name,
                    'current_stock' => $budget->current_stock,
                    'monthly_restock' => $budget->monthly_restock
                ];
            });

        return response()->json($budgets);
    }

    /**
     * Get remaining stock for a budget and month (AJAX)
     */
    public function getRemainingStock(HttpRequest $httpRequest)
    {
        $budgetId = $httpRequest->input('budget_id');
        $readyDate = $httpRequest->input('ready_date');

        if (!$budgetId || !$readyDate) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        $budget = DiaperBudget::notCancelled()->findOrFail($budgetId);
        $date = \Carbon\Carbon::parse($readyDate);

        $remainingStock = $budget->getRemainingStockForMonth($date->year, $date->month);

        return response()->json([
            'remaining_stock' => $remainingStock,
            'monthly_restock' => $budget->monthly_restock
        ]);
    }

    /**
     * Export monthly requests to PDF (disabled)
     */
    public function exportMonthlyPDF(HttpRequest $httpRequest)
    {
        abort(404, 'Export functionality is not yet available for diapers requests');
    }

    /**
     * Export active requests to PDF (disabled)
     */
    public function exportActivePDF(HttpRequest $httpRequest)
    {
        abort(404, 'Export functionality is not yet available for diapers requests');
    }
}
