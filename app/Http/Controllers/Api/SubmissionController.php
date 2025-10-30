<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SubmissionController extends Controller
{
    /**
     * Display a listing of submissions
     * For dosen: all submissions for their assignments
     * For mahasiswa: only their own submissions
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Submission::with(['assignment.course', 'user', 'files']);

        if ($user->role === 'mahasiswa') {
            // Student can only see their own submissions
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'dosen') {
            // Lecturer can see submissions for their courses
            $query->whereHas('assignment.course', function($q) use ($user) {
                $q->where('lecturer_id', $user->id);
            });
        }

        // Filter by assignment
        if ($request->has('assignment_id')) {
            $query->where('assignment_id', $request->assignment_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $submissions = $query->orderBy('submitted_at', 'desc')->paginate($perPage);

        return response()->json($submissions);
    }

    /**
     * Store a newly created submission
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|exists:assignments,id',
            'content' => 'required|string',
            'files.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $assignment = Assignment::findOrFail($request->assignment_id);

        // Check if assignment is still accepting submissions
        if ($assignment->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Assignment is closed'
            ], 403);
        }

        // Check deadline
        if (!$assignment->allow_late_submission && $assignment->due_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Deadline has passed and late submission is not allowed'
            ], 403);
        }

        // Check if student already submitted
        $existingSubmission = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingSubmission) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted this assignment. Use update instead.'
            ], 403);
        }

        $submission = Submission::create([
            'assignment_id' => $request->assignment_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
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

        return response()->json([
            'success' => true,
            'message' => 'Submission created successfully',
            'data' => $submission->load(['files', 'assignment'])
        ], 201);
    }

    /**
     * Display the specified submission
     */
    public function show(string $id)
    {
        $submission = Submission::with(['assignment.course', 'user', 'files'])->findOrFail($id);

        // Authorization check
        $user = auth()->user();
        if ($user->role === 'mahasiswa' && $submission->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $submission
        ]);
    }

    /**
     * Update the specified submission
     */
    public function update(Request $request, string $id)
    {
        $submission = Submission::findOrFail($id);

        // Only owner can update
        if ($submission->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'string',
            'files.*' => 'nullable|file|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $submission->update($validator->validated());

        // Handle new file uploads
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

        return response()->json([
            'success' => true,
            'message' => 'Submission updated successfully',
            'data' => $submission->load('files')
        ]);
    }

    /**
     * Grade a submission (dosen only)
     */
    public function grade(Request $request, string $id)
    {
        $submission = Submission::with('assignment.course')->findOrFail($id);

        // Only lecturer of the course can grade
        if ($submission->assignment->course->lecturer_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'score' => 'required|numeric|min:0|max:' . $submission->assignment->max_score,
            'feedback' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $submission->update([
            'score' => $request->score,
            'feedback' => $request->feedback,
            'status' => 'graded',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Submission graded successfully',
            'data' => $submission
        ]);
    }

    /**
     * Remove the specified submission
     */
    public function destroy(string $id)
    {
        $submission = Submission::findOrFail($id);

        // Only owner can delete
        if ($submission->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete associated files
        foreach ($submission->files as $file) {
            Storage::disk('public')->delete($file->path);
            $file->delete();
        }

        $submission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Submission deleted successfully'
        ]);
    }
}
