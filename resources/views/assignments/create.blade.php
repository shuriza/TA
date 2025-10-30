<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Buat Tugas Baru') }}
            </h2>
            <a href="{{ route('assignments.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    
                    @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">Ada beberapa kesalahan:</p>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    <form action="{{ route('assignments.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- Mata Kuliah --}}
                        <div>
                            <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Mata Kuliah <span class="text-red-500">*</span>
                            </label>
                            <select 
                                name="course_id" 
                                id="course_id" 
                                required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">-- Pilih Mata Kuliah --</option>
                                @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->code }} - {{ $course->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('course_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Judul --}}
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Judul Tugas <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="title" 
                                id="title" 
                                required
                                value="{{ old('title') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Contoh: Tugas 1 - Analisis Algoritma Sorting"
                            >
                            @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Deskripsi
                            </label>
                            <textarea 
                                name="description" 
                                id="description" 
                                rows="6"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Jelaskan detail tugas, instruksi pengerjaan, dan kriteria penilaian..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Two Columns: Deadline & Max Score --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="due_at" class="block text-sm font-medium text-gray-700 mb-2">
                                    Deadline <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="datetime-local" 
                                    name="due_at" 
                                    id="due_at" 
                                    required
                                    value="{{ old('due_at') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('due_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="max_score" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nilai Maksimal <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    name="max_score" 
                                    id="max_score" 
                                    required
                                    min="1"
                                    max="100"
                                    value="{{ old('max_score', 100) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('max_score')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Two Columns: Status & Priority --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <select 
                                    name="status" 
                                    id="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                                @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                                    Priority
                                </label>
                                <select 
                                    name="priority" 
                                    id="priority"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                </select>
                                @error('priority')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Three Columns: Effort, Impact, Tag --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="effort_mins" class="block text-sm font-medium text-gray-700 mb-2">
                                    Estimasi Waktu (menit)
                                </label>
                                <input 
                                    type="number" 
                                    name="effort_mins" 
                                    id="effort_mins" 
                                    min="1"
                                    value="{{ old('effort_mins') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="60"
                                >
                                @error('effort_mins')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="impact" class="block text-sm font-medium text-gray-700 mb-2">
                                    Impact (1-100)
                                </label>
                                <input 
                                    type="number" 
                                    name="impact" 
                                    id="impact" 
                                    min="1"
                                    max="100"
                                    value="{{ old('impact') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="50"
                                >
                                @error('impact')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="tag" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tag
                                </label>
                                <select 
                                    name="tag" 
                                    id="tag"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">-- Pilih Tag --</option>
                                    <option value="quiz" {{ old('tag') === 'quiz' ? 'selected' : '' }}>Quiz</option>
                                    <option value="tugas" {{ old('tag') === 'tugas' ? 'selected' : '' }}>Tugas</option>
                                    <option value="project" {{ old('tag') === 'project' ? 'selected' : '' }}>Project</option>
                                    <option value="uts" {{ old('tag') === 'uts' ? 'selected' : '' }}>UTS</option>
                                    <option value="uas" {{ old('tag') === 'uas' ? 'selected' : '' }}>UAS</option>
                                </select>
                                @error('tag')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Late Submission --}}
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="allow_late_submission" 
                                id="allow_late_submission" 
                                value="1"
                                {{ old('allow_late_submission') ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label for="allow_late_submission" class="ml-2 block text-sm text-gray-700">
                                Izinkan late submission (pengumpulan setelah deadline)
                            </label>
                        </div>

                        {{-- LMS URL --}}
                        <div>
                            <label for="lms_url" class="block text-sm font-medium text-gray-700 mb-2">
                                URL SPADA (Opsional)
                            </label>
                            <input 
                                type="url" 
                                name="lms_url" 
                                id="lms_url" 
                                value="{{ old('lms_url') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="https://spada.ulm.ac.id/..."
                            >
                            <p class="mt-1 text-xs text-gray-500">Link ke tugas di SPADA (jika ada)</p>
                            @error('lms_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- File Upload --}}
                        <div>
                            <label for="files" class="block text-sm font-medium text-gray-700 mb-2">
                                Lampiran File (Opsional)
                            </label>
                            <input 
                                type="file" 
                                name="files[]" 
                                id="files" 
                                multiple
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png"
                            >
                            <p class="mt-1 text-xs text-gray-500">Max 10MB per file. Format: PDF, DOC, PPT, XLS, ZIP, gambar</p>
                            @error('files.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Buttons --}}
                        <div class="flex items-center gap-4 pt-6 border-t">
                            <button 
                                type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
                            >
                                Buat Tugas
                            </button>
                            <a 
                                href="{{ route('assignments.index') }}" 
                                class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition"
                            >
                                Batal
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
