<?php

namespace App\Http\Controllers;

use App\Services\AIAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIAssistantController extends Controller
{
    public function __construct(
        private AIAssistantService $aiService
    ) {}

    /**
     * Show AI assistant page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get initial recommendations
        $recommendations = $this->aiService->getAssignmentRecommendations($user);
        
        return view('ai.assistant', compact('recommendations'));
    }

    /**
     * Get assignment recommendations
     */
    public function recommendations(Request $request): JsonResponse
    {
        $recommendations = $this->aiService->getAssignmentRecommendations($request->user());
        
        return response()->json($recommendations);
    }

    /**
     * Get study plan
     */
    public function studyPlan(Request $request): JsonResponse
    {
        $days = $request->input('days', 7);
        $plan = $this->aiService->getStudyPlan($request->user(), $days);
        
        return response()->json($plan);
    }

    /**
     * Chat with AI
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'context' => 'nullable|array',
        ]);

        $response = $this->aiService->chat(
            $request->user(),
            $validated['message'],
            $validated['context'] ?? []
        );

        return response()->json([
            'message' => $response,
        ]);
    }

    /**
     * Get student insights
     */
    public function insights(Request $request): JsonResponse
    {
        $insights = $this->aiService->getStudentInsights($request->user());
        
        return response()->json($insights);
    }
}
