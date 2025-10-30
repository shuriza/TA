<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentViewController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    )
    {
    }

    /**
     * Display a listing of assignments
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Assignment::with(['course', 'submissions']);

        // Filter by role
        if ($user->role === 'mahasiswa') {
            $query->whereHas('course.students', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->role === 'dosen') {
            $query->whereHas('course', function($q) use ($user) {
                $q->where('lecturer_id', $user->id);
            });
        }

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

        // Sort
        $sortBy = $request->get('sort', 'due_at');
        $sortOrder = $request->get('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $assignments = $query->paginate(15);

        // Get courses for filter
        if ($user->role === 'mahasiswa') {
            $courses = $user->enrolledCourses;
        } elseif ($user->role === 'dosen') {
            $courses = $user->taughtCourses;
        } else {
            $courses = Course::all();
        }

        return view('assignments.index', compact('assignments', 'courses'));
    }

    /**
     * Show assignment detail
     */
    public function show(Assignment $assignment)
    {
        $user = auth()->user();

        // Check authorization
        if ($user->role === 'mahasiswa') {
            if (!$assignment->course->students->contains($user->id)) {
                abort(403, 'Unauthorized');
            }
        } elseif ($user->role === 'dosen') {
            if ($assignment->course->lecturer_id !== $user->id) {
                abort(403, 'Unauthorized');
            }
        }

        $assignment->load(['course', 'files', 'submissions.user']);

        // Get user's submission if student
        $mySubmission = null;
        if ($user->role === 'mahasiswa') {
            $mySubmission = Submission::where('assignment_id', $assignment->id)
                ->where('user_id', $user->id)
                ->with('files')
                ->first();
        }

        return view('assignments.show', compact('assignment', 'mySubmission'));
    }

    /**
     * Show create assignment form (dosen/admin only)
     */
    public function create()
    {
        $user = auth()->user();

        if (!in_array($user->role, ['dosen', 'admin'])) {
            abort(403, 'Unauthorized');
        }

        $courses = $user->role === 'dosen' 
            ? $user->taughtCourses 
            : Course::all();

        return view('assignments.create', compact('courses'));
    }

    /**
     * Store new assignment
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['dosen', 'admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_at' => 'required|date',
            'status' => 'required|in:draft,published,closed',
            'priority' => 'required|in:low,medium,high',
            'effort_mins' => 'nullable|integer|min:0',
            'impact' => 'nullable|integer|min:0|max:100',
            'tag' => 'nullable|string|max:50',
            'allow_late_submission' => 'boolean',
            'max_score' => 'required|integer|min:0',
            'files.*' => 'nullable|file|max:10240',
        ]);

        $assignment = Assignment::create($validated);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('assignments/' . $assignment->id, 'public');
                
                $assignment->files()->create([
                    'filename' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Send notifications to students if published
        if ($validated['status'] === 'published') {
            $this->notificationService->notifyNewAssignment($assignment);
        }

        return redirect()
            ->route('assignments.show', $assignment)
            ->with('success', 'Assignment created successfully!');
    }

    /**
     * Submit assignment (student only)
     */
    public function submit(Request $request, Assignment $assignment)
    {
        $user = auth()->user();

        if ($user->role !== 'mahasiswa') {
            abort(403, 'Only students can submit assignments');
        }

        // Check if already submitted
        $existingSubmission = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingSubmission) {
            return back()->with('error', 'You have already submitted this assignment');
        }

        $validated = $request->validate([
            'content' => 'required|string',
            'files.*' => 'nullable|file|max:10240',
        ]);

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('submissions/' . $submission->id, 'public');
                
                $submission->files()->create([
                    'filename' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Notify lecturer about new submission
        $this->notificationService->notifyNewSubmission($submission);

        return redirect()
            ->route('assignments.show', $assignment)
            ->with('success', 'Assignment submitted successfully!');
    }
}
