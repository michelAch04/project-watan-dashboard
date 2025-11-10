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

        return view('humanitarian.index', compact('activeCount', 'draftCount', 'completedCount'));
    }

    /**
     * View active requests
     */
    public function active()
    {
        $user = Auth::user();

        $requests = Request::with([
            'requestType',
            'requestStatus',
            'sender',
            'currentUser',
            'requesterCity',
            'voter',
            'referenceMember'
        ])
            ->ofType('humanitarian')
            ->active()
            ->forUser($user)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('humanitarian.active', compact('requests'));
    }

    /**
     * View completed requests
     */
    public function completed()
    {
        $user = Auth::user();

        $requests = Request::with([
            'requestType',
            'requestStatus',
            'sender',
            'requesterCity',
            'voter',
            'referenceMember'
        ])
            ->ofType('humanitarian')
            ->completed()
            ->forUser($user)
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('humanitarian.completed', compact('requests'));
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
            'action' => 'required|in:draft,publish'
        ]);

        $user = Auth::user();
        $humanitarianType = RequestType::getByName('humanitarian');

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

        $request = Request::create($requestData);

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

        return response()->json([
            'success' => true,
            'message' => $validated['action'] === 'draft' ? 'Request saved as draft' : 'Request published successfully',
            'redirect' => route('humanitarian.index')
        ]);
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
            } else {
                // Reached top (HOR) - final approval
                $finalStatus = RequestStatus::getByName(RequestStatus::STATUS_FINAL_APPROVAL);
                $request->update([
                    'request_status_id' => $finalStatus->id,
                    'current_user_id' => null
                ]);

                // Notify sender
                InboxNotification::createForUser(
                    $request->sender_id,
                    $request->id,
                    'request_final_approved',
                    'Request Finally Approved',
                    "Your humanitarian request #{$request->request_number} has received final approval and is now ready for processing."
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request approved successfully',
                'redirect' => route('humanitarian.active')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request'
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
}
