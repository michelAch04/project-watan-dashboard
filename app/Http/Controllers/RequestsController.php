<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RequestHeader;
use App\Models\RequestStatus;

class RequestsController extends Controller
{
    /**
     * Display requests hub with all request types
     */
    public function index()
    {
        $user = Auth::user();

        // Get counts for each request type
        $humanitarianStats = $this->getRequestStats('humanitarian', $user);
        $publicStats = $this->getRequestStats('public', $user);
        $diapersStats = $this->getRequestStats('diapers', $user);

        return view('requests.index', [
            'humanitarian' => $humanitarianStats,
            'public' => $publicStats,
            'diapers' => $diapersStats,
        ]);
    }

    /**
     * Get statistics for a specific request type
     */
    private function getRequestStats($type, $user)
    {
        // Get active count
        $activeQuery = RequestHeader::active()->forUser($user);
        if ($type == 'humanitarian') {
            $activeQuery->whereHas('humanitarianRequest');
        } elseif ($type == 'public') {
            $activeQuery->whereHas('publicRequest');
        } elseif ($type == 'diapers') {
            $activeQuery->whereHas('diapersRequest');
        }
        $activeCount = $activeQuery->count();

        // Get drafts and rejected count
        $draftQuery = RequestHeader::draftsAndRejects($user);
        if ($type == 'humanitarian') {
            $draftQuery->whereHas('humanitarianRequest');
        } elseif ($type == 'public') {
            $draftQuery->whereHas('publicRequest');
        } elseif ($type == 'diapers') {
            $draftQuery->whereHas('diapersRequest');
        }
        $draftCount = $draftQuery->count();

        // Get completed count
        $completedQuery = RequestHeader::completed()->forUser($user);
        if ($type == 'humanitarian') {
            $completedQuery->whereHas('humanitarianRequest');
        } elseif ($type == 'public') {
            $completedQuery->whereHas('publicRequest');
        } elseif ($type == 'diapers') {
            $completedQuery->whereHas('diapersRequest');
        }
        $completedCount = $completedQuery->count();

        return [
            'active' => $activeCount,
            'drafts' => $draftCount,
            'completed' => $completedCount,
        ];
    }
}
