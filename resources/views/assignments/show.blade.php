<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Tugas') }}
            </h2>
            <a href="{{ route('assignments.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Assignment Header --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full" style="background-color: {{ $assignment->course->color }}20; color: {{ $assignment->course->color }}">
                                        {{ $assignment->course->code }}
                                    </span>
                                </div>
                                <div class="flex gap-2">
                                    <span class="px-3 py-1 text-sm rounded-full {{ 
                                        $assignment->status === 'published' ? 'bg-green-100 text-green-700' : 
                                        ($assignment->status === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') 
                                    }}">
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                    <span class="px-3 py-1 text-sm rounded-full {{ 
                                        $assignment->priority === 'high' ? 'bg-red-100 text-red-700' : 
                                        ($assignment->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') 
                                    }}">
                                        {{ ucfirst($assignment->priority) }}
                                    </span>
                                </div>
                            </div>

                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $assignment->title }}</h1>
                            <p class="text-gray-600">{{ $assignment->course->name }}</p>

                            @if($assignment->description)
                            <div class="mt-6 prose max-w-none">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Deskripsi</h3>
                                <div class="text-gray-700 whitespace-pre-wrap">{{ $assignment->description }}</div>
                            </div>
                            @endif

                            {{-- Assignment Files --}}
                            @if($assignment->files->count() > 0)
                            <div class="mt-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">📎 Lampiran</h3>
                                <div class="space-y-2">
                                    @foreach($assignment->files as $file)
                                    <a href="{{ Storage::url($file->path) }}" target="_blank" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $file->original_name }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($file->size / 1024, 2) }} KB</p>
                                        </div>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if($assignment->lms_url)
                            <div class="mt-6">
                                <a href="{{ $assignment->lms_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Buka di SPADA
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Submission Form (Student Only) --}}
                    @if(auth()->user()->role === 'mahasiswa')
                        @if(!$mySubmission)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h2 class="text-xl font-semibold text-gray-900 mb-4">📝 Kumpulkan Tugas</h2>
                                
                                @if($assignment->status === 'closed')
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <p class="text-red-700">Tugas ini sudah ditutup dan tidak menerima pengumpulan lagi.</p>
                                </div>
                                @elseif(!$assignment->allow_late_submission && $assignment->due_at && $assignment->due_at->isPast())
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <p class="text-red-700">Deadline sudah lewat dan late submission tidak diperbolehkan.</p>
                                </div>
                                @else
                                <form action="{{ route('assignments.submit', $assignment) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <div class="mb-4">
                                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                            Jawaban / Komentar <span class="text-red-500">*</span>
                                        </label>
                                        <textarea 
                                            name="content" 
                                            id="content" 
                                            rows="6" 
                                            required
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="Tulis jawaban atau komentar Anda di sini..."
                                        >{{ old('content') }}</textarea>
                                        @error('content')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="files" class="block text-sm font-medium text-gray-700 mb-2">
                                            Upload File (Opsional)
                                        </label>
                                        <input 
                                            type="file" 
                                            name="files[]" 
                                            id="files" 
                                            multiple
                                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                            accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar"
                                        >
                                        <p class="mt-1 text-xs text-gray-500">Max 10MB per file. Format: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR</p>
                                        @error('files.*')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                                        Kumpulkan Tugas
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @else
                        {{-- My Submission --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-xl font-semibold text-gray-900">✅ Pengumpulan Anda</h2>
                                    @if($mySubmission->status === 'graded')
                                    <span class="px-4 py-2 bg-blue-100 text-blue-700 font-semibold rounded-lg">
                                        Nilai: {{ $mySubmission->score }}/{{ $assignment->max_score }}
                                    </span>
                                    @else
                                    <span class="px-4 py-2 bg-yellow-100 text-yellow-700 font-semibold rounded-lg">
                                        Menunggu Penilaian
                                    </span>
                                    @endif
                                </div>

                                <div class="mb-4">
                                    <p class="text-sm text-gray-600">Dikumpulkan: {{ $mySubmission->submitted_at->format('d M Y, H:i') }}</p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                    <h3 class="font-semibold text-gray-900 mb-2">Jawaban:</h3>
                                    <p class="text-gray-700 whitespace-pre-wrap">{{ $mySubmission->content }}</p>
                                </div>

                                @if($mySubmission->files->count() > 0)
                                <div class="mb-4">
                                    <h3 class="font-semibold text-gray-900 mb-2">File yang diupload:</h3>
                                    <div class="space-y-2">
                                        @foreach($mySubmission->files as $file)
                                        <a href="{{ Storage::url($file->path) }}" target="_blank" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                            <span class="text-sm text-gray-700">{{ $file->original_name }}</span>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                @if($mySubmission->feedback)
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <h3 class="font-semibold text-blue-900 mb-2">Feedback dari Dosen:</h3>
                                    <p class="text-blue-800 whitespace-pre-wrap">{{ $mySubmission->feedback }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endif

                    {{-- All Submissions (Lecturer Only) --}}
                    @if(auth()->user()->role !== 'mahasiswa' && $assignment->submissions->count() > 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">📊 Pengumpulan ({{ $assignment->submissions->count() }})</h2>
                            <div class="space-y-3">
                                @foreach($assignment->submissions as $submission)
                                <div class="border rounded-lg p-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $submission->user->name }}</h4>
                                            <p class="text-sm text-gray-600">{{ $submission->user->nim }}</p>
                                            <p class="text-xs text-gray-500">{{ $submission->submitted_at->diffForHumans() }}</p>
                                        </div>
                                        @if($submission->score !== null)
                                        <span class="px-3 py-1 bg-blue-100 text-blue-700 font-semibold rounded">
                                            {{ $submission->score }}/{{ $assignment->max_score }}
                                        </span>
                                        @else
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-sm rounded">
                                            Belum dinilai
                                        </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-700">{{ Str::limit($submission->content, 150) }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    
                    {{-- Assignment Info --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">ℹ️ Informasi</h3>
                            <dl class="space-y-3">
                                @if($assignment->due_at)
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm text-gray-600">Deadline</dt>
                                    <dd class="text-sm font-medium text-right">
                                        <div>{{ $assignment->due_at->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $assignment->due_at->format('H:i') }}</div>
                                        <div class="text-xs {{ $assignment->due_at->isPast() ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $assignment->due_at->diffForHumans() }}
                                        </div>
                                    </dd>
                                </div>
                                @endif

                                @if($assignment->effort_mins)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Estimasi</dt>
                                    <dd class="text-sm font-medium">{{ $assignment->effort_mins }} menit</dd>
                                </div>
                                @endif

                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Nilai Maksimal</dt>
                                    <dd class="text-sm font-medium">{{ $assignment->max_score }} poin</dd>
                                </div>

                                @if($assignment->impact)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Impact</dt>
                                    <dd class="text-sm font-medium">{{ $assignment->impact }}/100</dd>
                                </div>
                                @endif

                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Late Submission</dt>
                                    <dd class="text-sm font-medium">
                                        @if($assignment->allow_late_submission)
                                        <span class="text-green-600">✓ Diperbolehkan</span>
                                        @else
                                        <span class="text-red-600">✗ Tidak diperbolehkan</span>
                                        @endif
                                    </dd>
                                </div>

                                @if($assignment->tag)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Tag</dt>
                                    <dd class="text-sm font-medium capitalize">{{ $assignment->tag }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    {{-- Course Info --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">📚 Mata Kuliah</h3>
                            <div class="space-y-2">
                                <p class="text-sm">
                                    <span class="text-gray-600">Kode:</span>
                                    <span class="font-medium">{{ $assignment->course->code }}</span>
                                </p>
                                <p class="text-sm">
                                    <span class="text-gray-600">Nama:</span>
                                    <span class="font-medium">{{ $assignment->course->name }}</span>
                                </p>
                                <p class="text-sm">
                                    <span class="text-gray-600">Dosen:</span>
                                    <span class="font-medium">{{ $assignment->course->lecturer->name }}</span>
                                </p>
                                <p class="text-sm">
                                    <span class="text-gray-600">Semester:</span>
                                    <span class="font-medium">{{ $assignment->course->semester }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>
