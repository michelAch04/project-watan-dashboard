<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Models\RequestType;
use App\Models\RequestStatus;
use App\Models\Voter;
use App\Models\PwMember;
use App\Models\InboxNotification;
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

        // Get counts for dashboard
        $activeCount = Request::ofType('humanitarian')
            ->active()
            ->forUser($user)
            ->count();

        $draftCount = Request::ofType('humanitarian')
            ->draftsAndRejects($user)
            ->count();

        $completedCount = Request::ofType('humanitarian')
            ->completed()
            ->forUser($user)
            ->count();

        // Get budgets if user is HOR
        $budgets = null;
        if ($user->hasRole('hor')) {
            $budgets = \App\Models\Budget::with('zone')
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

        $query = Request::with([
            'requestType',
            'requestStatus',
            'sender',
            'currentUser',
            'requesterCity',
            'voter',
            'referenceMember',
            'budget'
        ])
            ->ofType('humanitarian')
            ->active()
            ->forUser($user);

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

        $query = Request::with([
            'requestType',
            'requestStatus',
            'sender',
            'requesterCity',
            'voter',
            'referenceMember',
            'budget'
        ])
            ->ofType('humanitarian')
            ->completed()
            ->forUser($user);

        // Filter by month/year if provided
        $month = $httpRequest->input('month');
        $year = $httpRequest->input('year');

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

        $requests = Request::with([
            'requestType',
            'requestStatus',
            'requesterCity',
            'voter',
            'referenceMember'
        ])
            ->ofType('humanitarian')
            ->draftsAndRejects($user)
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
            'voter_id' => 'nullable|exists:voters_list,id',
            'requester_first_name' => 'required_without:voter_id|string|max:255',
            'requester_father_name' => 'required_without:voter_id|string|max:255',
            'requester_last_name' => 'required_without:voter_id|string|max:255',
            'requester_city_id' => 'required|exists:cities,id',
            'requester_ro_number' => 'nullable|string|max:255',
            'requester_phone' => 'nullable|string|max:255',
            'subtype' => 'required|string|max:255',
            'reference_member_id' => 'required|exists:pw_members,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'action' => 'required|in:draft,publish',
            'budget_id' => 'nullable|exists:budgets,id',
            'ready_date' => 'nullable|date'
        ]);

        $user = Auth::user();
        $humanitarianType = RequestType::getByName('humanitarian');

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

            $requestData = [
                'request_type_id' => $humanitarianType->id,
                'request_status_id' => $status->id,
                'sender_id' => $user->id,
                'current_user_id' => $currentUserId,
            ];

            // If voter selected, use voter data
            if ($validated['voter_id']) {
                $voter = Voter::find($validated['voter_id']);
                $requestData['voter_id'] = $voter->id;
                $requestData['requester_first_name'] = $voter->first_name;
                $requestData['requester_father_name'] = $voter->father_name;
                $requestData['requester_last_name'] = $voter->last_name;
                $requestData['requester_city_id'] = $voter->city_id;
                $requestData['requester_ro_number'] = $voter->ro_number;
                $requestData['requester_phone'] = $voter->phone;
            } else {
                $requestData = array_merge($requestData, [
                    'requester_first_name' => $validated['requester_first_name'],
                    'requester_father_name' => $validated['requester_father_name'],
                    'requester_last_name' => $validated['requester_last_name'],
                    'requester_city_id' => $validated['requester_city_id'],
                    'requester_ro_number' => $validated['requester_ro_number'] ?? null,
                    'requester_phone' => $validated['requester_phone'] ?? null,
                ]);
            }

            $requestData['subtype'] = $validated['subtype'];
            $requestData['reference_member_id'] = $validated['reference_member_id'];
            $requestData['amount'] = $validated['amount'];
            $requestData['notes'] = $validated['notes'] ?? null;

            // Handle budget allocation for HOR users who publish with budget
            if ($user->hasRole('hor') && $validated['action'] === 'publish' && !empty($validated['budget_id']) && !empty($validated['ready_date'])) {
                // Verify budget belongs to HOR's zone
                $budget = \App\Models\Budget::with('zone')->findOrFail($validated['budget_id']);
                if ($budget->zone->user_id !== $user->id) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only use budgets from your own zones'
                    ], 403);
                }

                // Check and refill budget if needed
                $budget->checkAndRefill();

                // Check if budget has enough balance
                if ($budget->current_balance < $validated['amount']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient budget balance for this request. Current balance: $' . number_format($budget->current_balance)
                    ], 400);
                }

                // Add budget and ready date to request data
                $requestData['budget_id'] = $validated['budget_id'];
                $requestData['ready_date'] = $validated['ready_date'];
            }

            // Create the request
            $request = Request::create($requestData);

            // Deduct from budget if HOR user allocated budget
            if ($user->hasRole('hor') && $validated['action'] === 'publish' && !empty($validated['budget_id'])) {
                $budget = \App\Models\Budget::findOrFail($validated['budget_id']);

                Log::info("Deducting amount: " . $validated['amount'] . " for new request id: " . $request->id);

                $budget->deduct(
                    $validated['amount'],
                    $request->id,
                    "Request #{$request->request_number} - {$request->requester_full_name}"
                );

                Log::info("Budget deducted successfully. New balance: " . $budget->current_balance);
            }

            // Create inbox notification if published
            if ($validated['action'] === 'publish' && $currentUserId) {
                InboxNotification::createForUser(
                    $currentUserId,
                    $request->id,
                    'request_published',
                    'New Request for Approval',
                    "{$user->name} has published a humanitarian request #{$request->request_number} for your approval."
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
        $request = Request::with([
            'requestType',
            'requestStatus',
            'sender',
            'currentUser',
            'requesterCity',
            'voter',
            'referenceMember'
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
        $request = Request::findOrFail($id);
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
        $request = Request::findOrFail($id);
        $user = Auth::user();

        if (!$request->canEdit($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot edit this request'
            ], 403);
        }

        $validated = $httpRequest->validate([
            'voter_id' => 'nullable|exists:voters_list,id',
            'requester_first_name' => 'required_without:voter_id|string|max:255',
            'requester_father_name' => 'required_without:voter_id|string|max:255',
            'requester_last_name' => 'required_without:voter_id|string|max:255',
            'requester_city_id' => 'required|exists:cities,id',
            'requester_ro_number' => 'nullable|string|max:255',
            'requester_phone' => 'nullable|string|max:255',
            'subtype' => 'required|string|max:255',
            'reference_member_id' => 'required|exists:pw_members,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'action' => 'required|in:save,publish'
        ]);

        // Update request data
        $updateData = [];

        if ($validated['voter_id']) {
            $voter = Voter::find($validated['voter_id']);
            $updateData['voter_id'] = $voter->id;
            $updateData['requester_first_name'] = $voter->first_name;
            $updateData['requester_father_name'] = $voter->father_name;
            $updateData['requester_last_name'] = $voter->last_name;
            $updateData['requester_city_id'] = $voter->city_id;
            $updateData['requester_ro_number'] = $voter->ro_number;
            $updateData['requester_phone'] = $voter->phone;
        } else {
            $updateData = [
                'voter_id' => null,
                'requester_first_name' => $validated['requester_first_name'],
                'requester_father_name' => $validated['requester_father_name'],
                'requester_last_name' => $validated['requester_last_name'],
                'requester_city_id' => $validated['requester_city_id'],
                'requester_ro_number' => $validated['requester_ro_number'] ?? null,
                'requester_phone' => $validated['requester_phone'] ?? null,
            ];
        }

        $updateData['subtype'] = $validated['subtype'];
        $updateData['reference_member_id'] = $validated['reference_member_id'];
        $updateData['amount'] = $validated['amount'];
        $updateData['notes'] = $validated['notes'] ?? null;

        // Handle status change if publishing
        if ($validated['action'] === 'publish') {
            $publishedStatus = RequestStatus::getByName(RequestStatus::STATUS_PUBLISHED);
            $updateData['request_status_id'] = $publishedStatus->id;

            if ($user->hasRole('hor')) {
                $finalStatus = RequestStatus::getByName(RequestStatus::STATUS_FINAL_APPROVAL);
                $updateData['request_status_id'] = $finalStatus->id;
                $updateData['current_user_id'] = null;
            } else {
                $updateData['current_user_id'] = $user->manager_id;

                // Create notification
                if ($user->manager_id) {
                    InboxNotification::createForUser(
                        $user->manager_id,
                        $request->id,
                        'request_published',
                        'Request Republished for Approval',
                        "{$user->name} has republished humanitarian request #{$request->request_number} for your approval."
                    );
                }
            }
        }

        $request->update($updateData);

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
        $request = Request::findOrFail($id);
        $user = Auth::user();

        if (!$request->canApproveReject($user)) {
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
                $request->update([
                    'current_user_id' => $user->manager_id
                ]);

                // Create notification
                InboxNotification::createForUser(
                    $user->manager_id,
                    $request->id,
                    'request_approved',
                    'Request Approved - Awaiting Your Review',
                    "{$user->name} has approved humanitarian request #{$request->request_number}. It now requires your approval."
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
        $request = Request::findOrFail($id);
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
            $budget = \App\Models\Budget::with('zone')->findOrFail($validated['budget_id']);
            if ($budget->zone->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only use budgets from your own zones'
                ], 403);
            }

            // Check if budget has enough current balance
            $readyDate = \Carbon\Carbon::parse($validated['ready_date']);

            // Check and refill budget if needed
            $budget->checkAndRefill();
            
            Log::info("Deducting amount: " . $request->amount . " for request id: " . $request->id);
            
            $budget->deduct(
                $request->amount,
                $request->id,
                "Request #{$request->request_number} - {$request->requester_full_name}"
            );
            
            Log::info("Budget deducted successfully. New balance: " . $budget->current_balance);

            if ($budget->current_balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient budget balance for this request. Current balance: $' . number_format($budget->current_balance)
                ], 400);
            }

            // Deduct from budget and create transaction record
            $budget->deduct(
                $request->amount,
                $request->id,
                "Request #{$request->request_number} - {$request->requester_full_name}"
            );

            // Update request with final approval, budget, and ready date
            $finalStatus = RequestStatus::getByName(RequestStatus::STATUS_FINAL_APPROVAL);
            $request->update([
                'request_status_id' => $finalStatus->id,
                'current_user_id' => null,
                'budget_id' => $validated['budget_id'],
                'ready_date' => $validated['ready_date']
            ]);

            // Notify sender
            InboxNotification::createForUser(
                $request->sender_id,
                $request->id,
                'request_final_approved',
                'Request Finally Approved',
                "Your humanitarian request #{$request->request_number} has received final approval and is scheduled for {$readyDate->format('M d, Y')}."
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

        $request = Request::findOrFail($id);
        $user = Auth::user();

        if (!$request->canApproveReject($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot reject this request'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $rejectedStatus = RequestStatus::getByName(RequestStatus::STATUS_REJECTED);

            $request->update([
                'request_status_id' => $rejectedStatus->id,
                'current_user_id' => null,
                'rejection_reason' => $validated['rejection_reason']
            ]);

            // Notify sender
            InboxNotification::createForUser(
                $request->sender_id,
                $request->id,
                'request_rejected',
                'Request Rejected',
                "{$user->name} has rejected your humanitarian request #{$request->request_number}. Reason: {$validated['rejection_reason']}"
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
        $request = Request::findOrFail($id);
        $user = Auth::user();

        if (!$user->can('mark_ready_humanitarian')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $finalApprovalStatus = RequestStatus::getByName(RequestStatus::STATUS_FINAL_APPROVAL);
        if ($request->request_status_id !== $finalApprovalStatus->id) {
            return response()->json([
                'success' => false,
                'message' => 'Request must be finally approved first'
            ], 400);
        }

        $readyStatus = RequestStatus::getByName(RequestStatus::STATUS_READY_FOR_COLLECTION);
        $request->update(['request_status_id' => $readyStatus->id]);

        // Notify sender
        InboxNotification::createForUser(
            $request->sender_id,
            $request->id,
            'request_ready',
            'Request Ready for Collection',
            "Humanitarian request #{$request->request_number} is now ready for collection."
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
        $request = Request::findOrFail($id);
        $user = Auth::user();

        if (!$user->can('mark_collected_humanitarian')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $readyStatus = RequestStatus::getByName(RequestStatus::STATUS_READY_FOR_COLLECTION);
        if ($request->request_status_id !== $readyStatus->id) {
            return response()->json([
                'success' => false,
                'message' => 'Request must be ready for collection first'
            ], 400);
        }

        $collectedStatus = RequestStatus::getByName(RequestStatus::STATUS_COLLECTED);
        $request->update(['request_status_id' => $collectedStatus->id]);

        // Notify sender
        InboxNotification::createForUser(
            $request->sender_id,
            $request->id,
            'request_collected',
            'Request Collected',
            "Humanitarian request #{$request->request_number} has been collected."
        );

        return response()->json([
            'success' => true,
            'message' => 'Request marked as collected'
        ]);
    }

    /**
     * Download request as PDF (placeholder)
     */
    public function download($id)
    {
        $request = Request::with([
            'requestType',
            'requestStatus',
            'sender',
            'requesterCity',
            'voter',
            'referenceMember'
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

        if ($search) {
            $query->search($search);
        }

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
        $request = Request::findOrFail($id);
        return response()->json([
            'amount' => $request->amount
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
        $requests = Request::with([
            'requestType',
            'requestStatus',
            'sender',
            'requesterCity',
            'voter',
            'referenceMember',
            'budget'
        ])
            ->ofType('humanitarian')
            ->completed()
            ->forUser($user)
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
        $requests = Request::with([
            'requestType',
            'requestStatus',
            'sender',
            'requesterCity',
            'voter',
            'referenceMember',
            'budget'
        ])
            ->ofType('humanitarian')
            ->active()
            ->forUser($user)
            ->whereYear('ready_date', $year)
            ->whereMonth('ready_date', $month)
            ->orderBy('request_number')
            ->get();

        // Use download.blade.php for multiple requests
        return view('humanitarian.download', compact('requests', 'month', 'year'));
    }
}
