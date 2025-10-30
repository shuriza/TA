<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm opacity-80">Total Users</div>
                                <div class="text-4xl font-bold mt-2">{{ $stats['total_users'] }}</div>
                            </div>
                            <div class="text-white opacity-50">
                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm opacity-80">Total Courses</div>
                                <div class="text-4xl font-bold mt-2">{{ $stats['total_courses'] }}</div>
                            </div>
                            <div class="text-white opacity-50">
                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm opacity-80">Total Assignments</div>
                                <div class="text-4xl font-bold mt-2">{{ $stats['total_assignments'] }}</div>
                            </div>
                            <div class="text-white opacity-50">
                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm opacity-80">Total Submissions</div>
                                <div class="text-4xl font-bold mt-2">{{ $stats['total_submissions'] }}</div>
                            </div>
                            <div class="text-white opacity-50">
                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4zm7 5a1 1 0 10-2 0v1.586l-.293-.293a1 1 0 10-1.414 1.414l2 2a1 1 0 001.414 0l2-2a1 1 0 00-1.414-1.414l-.293.293V9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">⚡ System Management</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('assignments.create') }}" class="p-6 border-2 border-dashed border-blue-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-center">
                            <svg class="w-12 h-12 mx-auto text-blue-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <h4 class="font-semibold text-gray-800">Create Assignment</h4>
                            <p class="text-sm text-gray-600 mt-1">Add new assignment</p>
                        </a>

                        <a href="{{ route('assignments.index') }}" class="p-6 border-2 border-dashed border-green-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition text-center">
                            <svg class="w-12 h-12 mx-auto text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h4 class="font-semibold text-gray-800">View All Assignments</h4>
                            <p class="text-sm text-gray-600 mt-1">Manage assignments</p>
                        </a>

                        <button onclick="syncSpada()" class="p-6 border-2 border-dashed border-purple-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition text-center">
                            <svg class="w-12 h-12 mx-auto text-purple-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <h4 class="font-semibold text-gray-800">Sync SPADA</h4>
                            <p class="text-sm text-gray-600 mt-1">Sync from SPADA Polinema</p>
                        </button>
                    </div>
                </div>
            </div>

            {{-- System Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">📊 System Information</h3>
                        <dl class="grid grid-cols-1 gap-4">
                            <div class="flex justify-between items-center py-3 border-b">
                                <dt class="text-sm font-medium text-gray-600">Laravel Version</dt>
                                <dd class="text-sm text-gray-900 font-semibold">12.36.0</dd>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b">
                                <dt class="text-sm font-medium text-gray-600">PHP Version</dt>
                                <dd class="text-sm text-gray-900 font-semibold">{{ PHP_VERSION }}</dd>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b">
                                <dt class="text-sm font-medium text-gray-600">Database</dt>
                                <dd class="text-sm text-gray-900 font-semibold">MySQL (lms_cerdas)</dd>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b">
                                <dt class="text-sm font-medium text-gray-600">Timezone</dt>
                                <dd class="text-sm text-gray-900 font-semibold">Asia/Jakarta</dd>
                            </div>
                            <div class="flex justify-between items-center py-3">
                                <dt class="text-sm font-medium text-gray-600">Environment</dt>
                                <dd>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ app()->environment('production') ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ strtoupper(app()->environment()) }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">🔧 Quick Links</h3>
                        <div class="space-y-3">
                            <a href="/horizon" class="block p-3 bg-gray-50 rounded hover:bg-gray-100 transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-800">Horizon Dashboard</h4>
                                        <p class="text-xs text-gray-600">Monitor queues & jobs</p>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>

                            <a href="/telescope" class="block p-3 bg-gray-50 rounded hover:bg-gray-100 transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-800">Telescope</h4>
                                        <p class="text-xs text-gray-600">Debug & monitor requests</p>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>

                            <a href="/logs" class="block p-3 bg-gray-50 rounded hover:bg-gray-100 transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-800">Application Logs</h4>
                                        <p class="text-xs text-gray-600">View system logs</p>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function syncSpada() {
            if (confirm('Start SPADA sync? This may take a few minutes.')) {
                alert('Sync command should be run from terminal:\nphp artisan spada:sync');
            }
        }
    </script>
    @endpush
</x-app-layout>
