<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     * Supports filtering by course, status, priority, and upcoming deadlines
     */
    public function index(Request $request)
    {
        $query = Assignment::with(['course', 'submissions']);

        // Filter by course
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter upcoming assignments (next N days)
        if ($request->has('upcoming_days')) {
            $query->where('status', 'published')
                ->whereBetween('due_at', [now(), now()->addDays($request->upcoming_days)])
                ->orderBy('due_at');
        }

        // Filter by enrolled courses for student
        if ($request->has('my_courses') && auth()->check()) {
            $query->whereHas('course.students', function($q) {
                $q->where('user_id', auth()->id());
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'due_at');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $assignments = $query->paginate($perPage);

        return response()->json($assignments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Assignment::class);

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_at' => 'nullable|date',
            'status' => 'required|in:draft,published,closed',
            'priority' => 'required|in:low,medium,high',
            'effort_mins' => 'nullable|integer|min:0',
            'impact' => 'nullable|integer|min:0|max:100',
            'tag' => 'nullable|string|max:50',
            'lms_url' => 'nullable|url',
            'allow_late_submission' => 'boolean',
            'max_score' => 'integer|min:0',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $assignment = Assignment::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Assignment created successfully',
            'data' => $assignment->load('course')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $assignment = Assignment::with(['course', 'submissions.user', 'files', 'reminders'])
            ->findOrFail($id);

        // Check if user has access
        if (auth()->user()->role === 'mahasiswa') {
            $this->authorize('view', $assignment);
        }

        return response()->json([
            'success' => true,
            'data' => $assignment
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $assignment = Assignment::findOrFail($id);
        $this->authorize('update', $assignment);

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'due_at' => 'nullable|date',
            'status' => 'in:draft,published,closed',
            'priority' => 'in:low,medium,high',
            'effort_mins' => 'nullable|integer|min:0',
            'impact' => 'nullable|integer|min:0|max:100',
            'tag' => 'nullable|string|max:50',
            'lms_url' => 'nullable|url',
            'allow_late_submission' => 'boolean',
            'max_score' => 'integer|min:0',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $assignment->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Assignment updated successfully',
            'data' => $assignment->load('course')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $assignment = Assignment::findOrFail($id);
        $this->authorize('delete', $assignment);

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assignment deleted successfully'
        ]);
    }

    /**
     * Get assignments summary/statistics
     */
    public function summary(Request $request)
    {
        $userId = auth()->id();
        $userRole = auth()->user()->role;

        $query = Assignment::query();

        // If student, only show assignments from enrolled courses
        if ($userRole === 'mahasiswa') {
            $query->whereHas('course.students', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        $stats = [
            'total' => $query->count(),
            'published' => (clone $query)->where('status', 'published')->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
            'urgent' => (clone $query)->where('priority', 'high')
                ->where('status', 'published')
                ->whereBetween('due_at', [now(), now()->addDays(3)])
                ->count(),
            'upcoming' => (clone $query)->where('status', 'published')
                ->whereBetween('due_at', [now(), now()->addDays(7)])
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
