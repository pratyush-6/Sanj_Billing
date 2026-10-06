<?php

namespace App\Http\Middleware;

use App\Services\BranchContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchSelected
{
    public function __construct(private BranchContextService $branchContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->branchContext->isAllBranches()) {
            return redirect()->route('dashboard')->with('error', 'Select a specific branch before recording transactions. "All branches" is for reports only.');
        }

        if ($this->branchContext->current() === null) {
            return redirect()->route('dashboard')->with('error', 'No branch is available for you. Ask an administrator to create one or assign you to it.');
        }

        return $next($request);
    }
}
