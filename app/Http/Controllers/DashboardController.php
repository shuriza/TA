<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'mahasiswa') {
            return $this->studentDashboard();
        } elseif ($user->role === 'dosen') {
            return $this->lecturerDashboard();
        } else {
            return $this->adminDashboard();
        }
    }

    /**
     * Student dashboard with upcoming assignments
     */
    private function studentDashboard()
    {
        $user = auth()->user();

        // Get enrolled courses
        $courses = $user->enrolledCourses()
            ->where('is_active', true)
            ->withCount('assignments')
            ->get();

        // Get urgent assignments (next 3 days)
        $urgentAssignments = Assignment::whereHas('course.students', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('status', 'published')
        ->whereBetween('due_at', [now(), now()->addDays(3)])
        ->orderBy('due_at')
        ->with(['course', 'submissions' => function($q) use ($user) {
            $q->where('user_id', $user->id);
        }])
        ->get();

        // Get upcoming assignments (4-7 days)
        $upcomingAssignments = Assignment::whereHas('course.students', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('status', 'published')
        ->whereBetween('due_at', [now()->addDays(4), now()->addDays(7)])
        ->orderBy('due_at')
        ->with(['course', 'submissions' => function($q) use ($user) {
            $q->where('user_id', $user->id);
        }])
        ->limit(10)
        ->get();

        // Get recent submissions
        $recentSubmissions = Submission::where('user_id', $user->id)
            ->with('assignment.course')
            ->orderBy('submitted_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate statistics
        $stats = [
            'total_courses' => $courses->count(),
            'total_assignments' => Assignment::whereHas('course.students', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'published')->count(),
            'urgent_count' => $urgentAssignments->count(),
            'completed_count' => $recentSubmissions->count(),
        ];

        return view('dashboard.student', compact(
            'courses', 
            'urgentAssignments', 
            'upcomingAssignments',
            'recentSubmissions',
            'stats'
        ));
    }

    /**
     * Lecturer dashboard with course overview
     */
    private function lecturerDashboard()
    {
        $user = auth()->user();

        // Get courses taught by lecturer
        $courses = Course::where('lecturer_id', $user->id)
            ->where('is_active', true)
            ->withCount(['assignments', 'students'])
            ->get();

        // Get recent assignments
        $recentAssignments = Assignment::whereHas('course', function($q) use ($user) {
            $q->where('lecturer_id', $user->id);
        })
        ->orderBy('created_at', 'desc')
        ->with('course')
        ->limit(10)
        ->get();

        // Get pending submissions (needs grading)
        $pendingSubmissions = Submission::whereHas('assignment.course', function($q) use ($user) {
            $q->where('lecturer_id', $user->id);
        })
        ->where('status', 'submitted')
        ->with(['assignment.course', 'user'])
        ->orderBy('submitted_at', 'desc')
        ->limit(15)
        ->get();

        // Calculate statistics
        $stats = [
            'total_courses' => $courses->count(),
            'total_students' => $courses->sum('students_count'),
            'total_assignments' => $courses->sum('assignments_count'),
            'pending_grading' => $pendingSubmissions->count(),
        ];

        return view('dashboard.lecturer', compact(
            'courses',
            'recentAssignments',
            'pendingSubmissions',
            'stats'
        ));
    }

    /**
     * Admin dashboard with system overview
     */
    private function adminDashboard()
    {
        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_courses' => Course::count(),
            'total_assignments' => Assignment::count(),
            'total_submissions' => Submission::count(),
        ];

        return view('dashboard.admin', compact('stats'));
    }
}
