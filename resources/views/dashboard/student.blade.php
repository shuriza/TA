<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Mahasiswa') }}
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
                        <div class="text-sm text-gray-600">Total Tugas</div>
                        <div class="text-3xl font-bold text-gray-700">{{ $stats['total_assignments'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-600">Tugas Urgent</div>
                        <div class="text-3xl font-bold text-red-600">{{ $stats['urgent_count'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-600">Sudah Dikumpulkan</div>
                        <div class="text-3xl font-bold text-green-600">{{ $stats['completed_count'] }}</div>
                    </div>
                </div>
            </div>

            {{-- Urgent Assignments (H-3) --}}
            @if($urgentAssignments->count() > 0)
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Tugas Mendesak! ({{ $urgentAssignments->count() }} tugas dalam 3 hari)
                        </h3>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-red-600">🔥 Tugas Urgent (H-3)</h3>
                    <div class="space-y-3">
                        @foreach($urgentAssignments as $assignment)
                        <div class="border-l-4 border-red-500 bg-red-50 p-4 rounded hover:bg-red-100 transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 text-xs rounded-full" style="background-color: {{ $assignment->course->color }}20; color: {{ $assignment->course->color }}">
                                            {{ $assignment->course->code }}
                                        </span>
                                        <h4 class="font-semibold text-gray-800">{{ $assignment->title }}</h4>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $assignment->course->name }}</p>
                                    <div class="flex gap-4 mt-2 text-xs text-gray-500">
                                        <span>⏰ Deadline: {{ $assignment->due_at->format('d M Y, H:i') }}</span>
                                        <span>⏱️ {{ $assignment->effort_mins }} menit</span>
                                        <span>📊 {{ $assignment->max_score }} poin</span>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    @if($assignment->submissions->where('user_id', auth()->id())->count() > 0)
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full">✓ Sudah Dikumpulkan</span>
                                    @else
                                        <a href="{{ route('assignments.show', $assignment->id) }}" class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                            Kerjakan Sekarang
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Upcoming Assignments (H-7) --}}
            @if($upcomingAssignments->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">📅 Tugas Mendatang (4-7 Hari)</h3>
                    <div class="space-y-3">
                        @foreach($upcomingAssignments as $assignment)
                        <div class="border-l-4 border-blue-500 bg-blue-50 p-4 rounded hover:bg-blue-100 transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 text-xs rounded-full" style="background-color: {{ $assignment->course->color }}20; color: {{ $assignment->course->color }}">
                                            {{ $assignment->course->code }}
                                        </span>
                                        <h4 class="font-semibold text-gray-800">{{ $assignment->title }}</h4>
                                        <span class="px-2 py-1 text-xs bg-{{ $assignment->priority === 'high' ? 'red' : ($assignment->priority === 'medium' ? 'yellow' : 'green') }}-100 text-{{ $assignment->priority === 'high' ? 'red' : ($assignment->priority === 'medium' ? 'yellow' : 'green') }}-700 rounded">
                                            {{ ucfirst($assignment->priority) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $assignment->course->name }}</p>
                                    <div class="flex gap-4 mt-2 text-xs text-gray-500">
                                        <span>📅 {{ $assignment->due_at->format('d M Y, H:i') }}</span>
                                        <span>⏱️ {{ $assignment->effort_mins }} menit</span>
                                        <span>🎯 Impact: {{ $assignment->impact }}/100</span>
                                    </div>
                                </div>
                                <div>
                                    @if($assignment->submissions->where('user_id', auth()->id())->count() > 0)
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full">✓ Sudah Dikumpulkan</span>
                                    @else
                                        <a href="{{ route('assignments.show', $assignment->id) }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                            Lihat Detail
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Courses Grid --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">📚 Mata Kuliah</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($courses as $course)
                        <div class="border rounded-lg p-4 hover:shadow-lg transition cursor-pointer" style="border-left: 4px solid {{ $course->color }}">
                            <h4 class="font-semibold text-gray-800">{{ $course->name }}</h4>
                            <p class="text-sm text-gray-600">{{ $course->code }} - {{ $course->class }}</p>
                            <p class="text-xs text-gray-500 mt-2">Dosen: {{ $course->lecturer->name }}</p>
                            <div class="mt-3 flex justify-between text-sm">
                                <span class="text-gray-600">{{ $course->assignments_count }} tugas</span>
                                <a href="#" class="text-blue-600 hover:text-blue-800">Lihat Detail →</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Recent Submissions --}}
            @if($recentSubmissions->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">📝 Pengumpulan Terakhir</h3>
                    <div class="space-y-2">
                        @foreach($recentSubmissions as $submission)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded hover:bg-gray-100">
                            <div>
                                <h5 class="font-medium text-gray-800">{{ $submission->assignment->title }}</h5>
                                <p class="text-xs text-gray-500">{{ $submission->assignment->course->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">{{ $submission->submitted_at->diffForHumans() }}</p>
                                @if($submission->score !== null)
                                    <span class="text-sm font-semibold text-green-600">Nilai: {{ $submission->score }}/{{ $submission->assignment->max_score }}</span>
                                @else
                                    <span class="text-xs text-yellow-600">Menunggu penilaian</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
