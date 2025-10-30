<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Tugas') }}
            </h2>
            @if(auth()->user()->role !== 'mahasiswa')
            <a href="{{ route('assignments.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                + Buat Tugas Baru
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Filters --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('assignments.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mata Kuliah</label>
                            <select name="course_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Mata Kuliah</option>
                                @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->code }} - {{ $course->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                            <select name="priority" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Priority</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                            <div class="flex gap-2">
                                <select name="sort" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="due_at" {{ request('sort') == 'due_at' ? 'selected' : '' }}>Deadline</option>
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Created</option>
                                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Title</option>
                                </select>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Assignments List --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($assignments->count() > 0)
                    <div class="space-y-4">
                        @foreach($assignments as $assignment)
                        <div class="border-l-4 p-4 rounded-lg hover:shadow-md transition {{ 
                            $assignment->priority === 'high' ? 'border-red-500 bg-red-50' : 
                            ($assignment->priority === 'medium' ? 'border-yellow-500 bg-yellow-50' : 'border-green-500 bg-green-50') 
                        }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background-color: {{ $assignment->course->color }}20; color: {{ $assignment->course->color }}">
                                            {{ $assignment->course->code }}
                                        </span>
                                        <h3 class="text-lg font-semibold text-gray-800">{{ $assignment->title }}</h3>
                                        
                                        {{-- Status Badge --}}
                                        <span class="px-2 py-1 text-xs rounded-full {{ 
                                            $assignment->status === 'published' ? 'bg-green-100 text-green-700' : 
                                            ($assignment->status === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') 
                                        }}">
                                            {{ ucfirst($assignment->status) }}
                                        </span>

                                        {{-- Priority Badge --}}
                                        <span class="px-2 py-1 text-xs rounded-full {{ 
                                            $assignment->priority === 'high' ? 'bg-red-100 text-red-700' : 
                                            ($assignment->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') 
                                        }}">
                                            {{ ucfirst($assignment->priority) }} Priority
                                        </span>
                                    </div>

                                    <p class="text-sm text-gray-600 mb-3">{{ $assignment->course->name }}</p>

                                    @if($assignment->description)
                                    <p class="text-sm text-gray-700 mb-3">{{ Str::limit($assignment->description, 150) }}</p>
                                    @endif

                                    <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                                        @if($assignment->due_at)
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span>Deadline: {{ $assignment->due_at->format('d M Y, H:i') }}</span>
                                            <span class="text-xs {{ $assignment->due_at->isPast() ? 'text-red-600' : 'text-gray-500' }}">
                                                ({{ $assignment->due_at->diffForHumans() }})
                                            </span>
                                        </div>
                                        @endif

                                        @if($assignment->effort_mins)
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>{{ $assignment->effort_mins }} menit</span>
                                        </div>
                                        @endif

                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                            </svg>
                                            <span>{{ $assignment->max_score }} poin</span>
                                        </div>

                                        @if($assignment->tag)
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            <span class="capitalize">{{ $assignment->tag }}</span>
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Submission Status for Students --}}
                                    @if(auth()->user()->role === 'mahasiswa')
                                        @php
                                            $mySubmission = $assignment->submissions->where('user_id', auth()->id())->first();
                                        @endphp
                                        @if($mySubmission)
                                        <div class="mt-3 flex items-center gap-2">
                                            <span class="px-3 py-1 bg-green-100 text-green-700 text-sm rounded-full">
                                                ✓ Sudah Dikumpulkan
                                            </span>
                                            @if($mySubmission->score !== null)
                                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">
                                                Nilai: {{ $mySubmission->score }}/{{ $assignment->max_score }}
                                            </span>
                                            @endif
                                        </div>
                                        @endif
                                    @endif
                                </div>

                                <div class="flex flex-col gap-2 ml-4">
                                    <a href="{{ route('assignments.show', $assignment->id) }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 text-center whitespace-nowrap">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $assignments->links() }}
                    </div>
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada tugas</h3>
                        <p class="mt-1 text-sm text-gray-500">Belum ada tugas yang sesuai dengan filter Anda.</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
