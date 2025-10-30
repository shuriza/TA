<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Dosen') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-600">Mata Kuliah</div>
                        <div class="text-3xl font-bold text-blue-600">{{ $stats['total_courses'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-600">Total Mahasiswa</div>
                        <div class="text-3xl font-bold text-green-600">{{ $stats['total_students'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-600">Total Tugas</div>
                        <div class="text-3xl font-bold text-gray-700">{{ $stats['total_assignments'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-600">Perlu Dinilai</div>
                        <div class="text-3xl font-bold text-orange-600">{{ $stats['pending_grading'] }}</div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">⚡ Quick Actions</h3>
                    <div class="flex gap-4">
                        <a href="{{ route('assignments.create') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Buat Tugas Baru
                        </a>
                        <a href="{{ route('assignments.index') }}" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Lihat Semua Tugas
                        </a>
                    </div>
                </div>
            </div>

            {{-- Pending Grading --}}
            @if($pendingSubmissions->count() > 0)
            <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-orange-800">
                            Ada {{ $pendingSubmissions->count() }} tugas yang perlu dinilai
                        </h3>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-orange-600">📝 Perlu Penilaian</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tugas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Kuliah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dikumpulkan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pendingSubmissions as $submission)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                                                {{ substr($submission->user->name, 0, 1) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $submission->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $submission->user->nim }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $submission->assignment->title }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $submission->assignment->course->code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $submission->submitted_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('assignments.show', $submission->assignment_id) }}" class="text-blue-600 hover:text-blue-900">
                                            Nilai Sekarang
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- My Courses --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">📚 Mata Kuliah Saya</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($courses as $course)
                        <div class="border rounded-lg p-4 hover:shadow-lg transition cursor-pointer" style="border-left: 4px solid {{ $course->color }}">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-gray-800">{{ $course->name }}</h4>
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                    {{ $course->students_count }} mahasiswa
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">{{ $course->code }} - {{ $course->class }}</p>
                            <p class="text-xs text-gray-500 mt-2">{{ $course->semester }}</p>
                            <div class="mt-4 flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ $course->assignments_count }} tugas</span>
                                <a href="{{ route('assignments.index', ['course_id' => $course->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                    Lihat Tugas →
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Recent Assignments --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">📋 Tugas Terbaru</h3>
                    <div class="space-y-3">
                        @foreach($recentAssignments as $assignment)
                        <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 text-xs rounded-full" style="background-color: {{ $assignment->course->color }}20; color: {{ $assignment->course->color }}">
                                        {{ $assignment->course->code }}
                                    </span>
                                    <h4 class="font-semibold text-gray-800">{{ $assignment->title }}</h4>
                                    <span class="px-2 py-1 text-xs rounded-full bg-{{ $assignment->status === 'published' ? 'green' : ($assignment->status === 'draft' ? 'yellow' : 'gray') }}-100 text-{{ $assignment->status === 'published' ? 'green' : ($assignment->status === 'draft' ? 'yellow' : 'gray') }}-700">
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                </div>
                                <div class="flex gap-4 mt-2 text-xs text-gray-500">
                                    <span>📅 Deadline: {{ $assignment->due_at ? $assignment->due_at->format('d M Y, H:i') : 'No deadline' }}</span>
                                    <span>📊 {{ $assignment->max_score }} poin</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('assignments.show', $assignment->id) }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                    Detail
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
