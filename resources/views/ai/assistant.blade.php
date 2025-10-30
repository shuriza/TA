<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('AI Assistant') }} 🤖
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Chat Interface --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">💬 Chat dengan AI</h3>
                            
                            {{-- Chat Messages --}}
                            <div id="chat-messages" class="space-y-4 mb-4 h-96 overflow-y-auto bg-gray-50 rounded-lg p-4">
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white">
                                            🤖
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="bg-white rounded-lg p-3 shadow-sm">
                                            <p class="text-sm text-gray-800">Halo! Saya AI Assistant Anda. Saya bisa membantu Anda dengan:</p>
                                            <ul class="mt-2 text-sm text-gray-600 list-disc list-inside">
                                                <li>Rekomendasi prioritas tugas</li>
                                                <li>Membuat jadwal belajar</li>
                                                <li>Memberikan tips pengerjaan tugas</li>
                                                <li>Analisis performa akademik</li>
                                            </ul>
                                            <p class="mt-2 text-sm text-gray-800">Ada yang bisa saya bantu?</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Chat Input --}}
                            <form id="chat-form" class="flex gap-2">
                                <input 
                                    type="text" 
                                    id="chat-input"
                                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Ketik pesan Anda..."
                                    autocomplete="off"
                                >
                                <button 
                                    type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
                                >
                                    Kirim
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Assignment Recommendations --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">📋 Rekomendasi Tugas</h3>
                                <button 
                                    onclick="refreshRecommendations()"
                                    class="text-sm text-blue-600 hover:text-blue-800"
                                >
                                    🔄 Refresh
                                </button>
                            </div>

                            <div id="recommendations-container">
                                @if(isset($recommendations['recommendations']) && count($recommendations['recommendations']) > 0)
                                    @foreach($recommendations['recommendations'] as $rec)
                                    <div class="mb-4 p-4 border rounded-lg {{ 
                                        $rec['priority_level'] === 'critical' ? 'border-red-300 bg-red-50' : 
                                        ($rec['priority_level'] === 'high' ? 'border-orange-300 bg-orange-50' : 
                                        ($rec['priority_level'] === 'medium' ? 'border-yellow-300 bg-yellow-50' : 'border-green-300 bg-green-50'))
                                    }}">
                                        <div class="flex items-start justify-between mb-2">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ 
                                                $rec['priority_level'] === 'critical' ? 'bg-red-600 text-white' : 
                                                ($rec['priority_level'] === 'high' ? 'bg-orange-600 text-white' : 
                                                ($rec['priority_level'] === 'medium' ? 'bg-yellow-600 text-white' : 'bg-green-600 text-white'))
                                            }}">
                                                {{ strtoupper($rec['priority_level']) }}
                                            </span>
                                            <span class="text-xs text-gray-600">⏱️ {{ $rec['estimated_time'] }}</span>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900 mb-1">{{ $rec['reason'] }}</p>
                                        @if(isset($rec['tips']) && count($rec['tips']) > 0)
                                        <div class="mt-2">
                                            <p class="text-xs font-semibold text-gray-700">Tips:</p>
                                            <ul class="text-xs text-gray-600 list-disc list-inside">
                                                @foreach($rec['tips'] as $tip)
                                                <li>{{ $tip }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach

                                    @if(isset($recommendations['summary']))
                                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                        <p class="text-sm text-blue-900">📊 {{ $recommendations['summary'] }}</p>
                                    </div>
                                    @endif
                                @else
                                <p class="text-gray-500 text-center py-8">Tidak ada tugas yang perlu direkomendasi saat ini.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    
                    {{-- Quick Actions --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">⚡ Quick Actions</h3>
                            <div class="space-y-2">
                                <button 
                                    onclick="askAI('Buatkan jadwal belajar untuk 7 hari ke depan')"
                                    class="w-full text-left px-4 py-2 bg-blue-50 hover:bg-blue-100 rounded-lg text-sm text-blue-700 transition"
                                >
                                    📅 Buat Jadwal Belajar
                                </button>
                                <button 
                                    onclick="askAI('Tugas mana yang harus saya kerjakan duluan?')"
                                    class="w-full text-left px-4 py-2 bg-purple-50 hover:bg-purple-100 rounded-lg text-sm text-purple-700 transition"
                                >
                                    🎯 Prioritas Tugas
                                </button>
                                <button 
                                    onclick="getInsights()"
                                    class="w-full text-left px-4 py-2 bg-green-50 hover:bg-green-100 rounded-lg text-sm text-green-700 transition"
                                >
                                    📈 Analisis Performa
                                </button>
                                <button 
                                    onclick="askAI('Berikan tips untuk meningkatkan produktivitas belajar')"
                                    class="w-full text-left px-4 py-2 bg-orange-50 hover:bg-orange-100 rounded-lg text-sm text-orange-700 transition"
                                >
                                    💡 Tips Produktivitas
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Study Plan --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-gray-900">📆 Study Plan</h3>
                                <button 
                                    onclick="generateStudyPlan()"
                                    class="text-xs text-blue-600 hover:text-blue-800"
                                >
                                    Generate
                                </button>
                            </div>
                            <div id="study-plan-container">
                                <p class="text-sm text-gray-500 text-center py-4">Klik Generate untuk membuat jadwal</p>
                            </div>
                        </div>
                    </div>

                    {{-- AI Status --}}
                    <div class="bg-gradient-to-br from-blue-500 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg text-white">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                                <h3 class="font-semibold">AI Status</h3>
                            </div>
                            <p class="text-sm opacity-90">GPT-4o Mini</p>
                            <p class="text-xs opacity-75 mt-2">AI siap membantu Anda 24/7</p>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script>
        let chatContext = [];

        // Chat functionality
        document.getElementById('chat-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('chat-input');
            const message = input.value.trim();
            
            if (!message) return;

            // Add user message to chat
            addMessageToChat('user', message);
            input.value = '';

            // Add to context
            chatContext.push({ role: 'user', content: message });

            // Show loading
            const loadingId = addMessageToChat('assistant', '⏳ Mengetik...');

            try {
                const response = await fetch('/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ message, context: chatContext }),
                });

                const data = await response.json();
                
                // Remove loading, add actual response
                document.getElementById(loadingId).remove();
                addMessageToChat('assistant', data.message);
                
                // Add to context
                chatContext.push({ role: 'assistant', content: data.message });
            } catch (error) {
                document.getElementById(loadingId).remove();
                addMessageToChat('assistant', 'Maaf, terjadi kesalahan. Silakan coba lagi.');
            }
        });

        function addMessageToChat(role, message) {
            const container = document.getElementById('chat-messages');
            const messageId = 'msg-' + Date.now();
            
            const messageHtml = `
                <div id="${messageId}" class="flex gap-3 ${role === 'user' ? 'justify-end' : ''}">
                    ${role === 'assistant' ? `
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white">
                            🤖
                        </div>
                    </div>
                    ` : ''}
                    <div class="flex-1 ${role === 'user' ? 'max-w-xs' : ''}">
                        <div class="${role === 'user' ? 'bg-blue-600 text-white' : 'bg-white'} rounded-lg p-3 shadow-sm">
                            <p class="text-sm whitespace-pre-wrap">${message}</p>
                        </div>
                    </div>
                    ${role === 'user' ? `
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white">
                            👤
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', messageHtml);
            container.scrollTop = container.scrollHeight;
            
            return messageId;
        }

        function askAI(question) {
            document.getElementById('chat-input').value = question;
            document.getElementById('chat-form').dispatchEvent(new Event('submit'));
        }

        async function refreshRecommendations() {
            const container = document.getElementById('recommendations-container');
            container.innerHTML = '<p class="text-center py-8 text-gray-500">⏳ Loading...</p>';

            try {
                const response = await fetch('/ai/recommendations');
                const data = await response.json();
                
                // Reload page to show new recommendations
                window.location.reload();
            } catch (error) {
                container.innerHTML = '<p class="text-center py-8 text-red-500">Error loading recommendations</p>';
            }
        }

        async function generateStudyPlan() {
            const container = document.getElementById('study-plan-container');
            container.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">⏳ Generating...</p>';

            try {
                const response = await fetch('/ai/study-plan?days=7');
                const data = await response.json();
                
                if (data.daily_plan && data.daily_plan.length > 0) {
                    let html = '<div class="space-y-3">';
                    data.daily_plan.slice(0, 3).forEach(day => {
                        html += `
                            <div class="text-sm">
                                <p class="font-semibold text-gray-900">${day.day}</p>
                                <p class="text-xs text-gray-600">${day.total_hours} jam</p>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<p class="text-sm text-gray-500">Tidak ada jadwal</p>';
                }
            } catch (error) {
                container.innerHTML = '<p class="text-sm text-red-500">Error</p>';
            }
        }

        async function getInsights() {
            addMessageToChat('user', 'Analisis performa saya');
            const loadingId = addMessageToChat('assistant', '⏳ Menganalisis data...');

            try {
                const response = await fetch('/ai/insights');
                const data = await response.json();
                
                document.getElementById(loadingId).remove();
                
                if (data.insights && data.insights.length > 0) {
                    let message = '📊 Analisis Performa:\n\n';
                    message += data.insights.join('\n');
                    addMessageToChat('assistant', message);
                } else {
                    addMessageToChat('assistant', data.message || 'Belum ada data untuk dianalisis.');
                }
            } catch (error) {
                document.getElementById(loadingId).remove();
                addMessageToChat('assistant', 'Gagal menganalisis data.');
            }
        }
    </script>
</x-app-layout>
