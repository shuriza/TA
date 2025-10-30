<?php

namespace App\Services\Lms;

/**
 * Interface untuk LMS Connectors
 * 
 * Semua connector (SPADA, Moodle, Google Classroom, Canvas) 
 * harus implement interface ini untuk konsistensi.
 */
interface LmsConnectorInterface
{
    /**
     * Authenticate ke LMS
     * 
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function authenticate(string $username, string $password): bool;

    /**
     * Check if current session is authenticated
     * 
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Get list of courses
     * 
     * @return array Array of courses dengan format:
     * [
     *   'external_id' => string,
     *   'code' => string,
     *   'name' => string,
     *   'semester' => string,
     *   'description' => string|null,
     * ]
     */
    public function getCourses(): array;

    /**
     * Get assignments dari course tertentu
     * 
     * @param string $courseId External course ID dari LMS
     * @return array Array of assignments dengan format:
     * [
     *   'external_id' => string,
     *   'course_external_id' => string,
     *   'title' => string,
     *   'description' => string|null,
     *   'due_at' => Carbon|null,
     *   'allow_late_submission' => bool,
     *   'lms_url' => string,
     * ]
     */
    public function getAssignments(string $courseId): array;

    /**
     * Get materials dari course tertentu
     * 
     * @param string $courseId External course ID dari LMS
     * @return array Array of materials dengan format:
     * [
     *   'external_id' => string,
     *   'title' => string,
     *   'type' => string, // resource, url, page, folder, dll
     *   'url' => string|null,
     *   'description' => string|null,
     * ]
     */
    public function getMaterials(string $courseId): array;
}
