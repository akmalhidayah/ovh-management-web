<?php

namespace App\Http\Controllers\User\Approval;

use App\Http\Controllers\Controller;
use App\Support\UserRoleUiData;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        return view('user.approval.review.index', UserRoleUiData::approvalReview());
    }
}
