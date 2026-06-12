<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $character->name }} - AI Persona</title>
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #09090b; /* zinc-950 */
            color: #fafafa;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.02); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 4px; }
    </style>
</head>
<body class="min-h-screen relative overflow-x-hidden flex flex-col lg:h-screen lg:overflow-hidden">
    <!-- Dynamic AI Background -->
    <div id="dynamicBg" class="fixed inset-0 w-full h-full bg-zinc-950 z-[-1] transition-all duration-1000 ease-in-out opacity-0"></div>
    <div class="fixed inset-0 w-full h-full bg-zinc-950/70 z-[-1]"></div> <!-- Overlay -->
    
    <!-- Subtle Ambient Background -->
    <div id="glow1" class="fixed top-0 left-0 w-[800px] h-[800px] bg-zinc-800/10 rounded-full blur-[120px] pointer-events-none -translate-x-1/2 -translate-y-1/2 z-0 transition-colors duration-1000"></div>
    
    <!-- Navigation -->
    <nav class="relative z-10 w-full p-4 lg:px-8 lg:py-6 flex-shrink-0 flex justify-between items-center">
        <a href="/" class="inline-flex items-center gap-2 text-zinc-500 hover:text-zinc-300 transition-colors font-medium text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Search
        </a>
    </nav>

    <!-- Main Content Flexbox -->
    <main class="relative z-10 w-full max-w-6xl mx-auto px-4 lg:px-6 pb-6 flex-grow flex flex-col lg:flex-row gap-6 lg:h-[calc(100vh-80px)] lg:overflow-hidden">
        
        <!-- LEFT PANEL: Character Lore (Hidden on mobile by default, maybe collapsible) -->
        <div class="w-full lg:w-1/3 flex flex-col lg:h-full">
            <!-- Glass Card -->
            <div class="bg-zinc-900/40 backdrop-blur-xl border border-zinc-800 shadow-sm rounded-3xl p-6 flex flex-col gap-6 relative lg:h-full overflow-hidden">
                
                <!-- Image & Name -->
                <div class="flex flex-col items-center flex-shrink-0 z-10 mt-2">
                    <div class="relative w-32 h-48 rounded-2xl overflow-hidden shadow-md mb-5 border border-zinc-800/50">
                        <img src="{{ $character->image_url ?? 'https://via.placeholder.com/300x400' }}" alt="{{ $character->name }}" class="w-full h-full object-cover">
                    </div>
                    <h1 class="text-2xl font-bold text-center text-zinc-100 mb-2">{{ $character->name }}</h1>
                    
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-zinc-800/50 border border-zinc-700/50 rounded-full text-xs font-medium text-zinc-400">
                        MAL ID: {{ $character->api_id }}
                    </div>
                </div>

                <!-- Lore Details -->
                <div class="flex-grow overflow-y-auto custom-scrollbar pr-2 text-zinc-400 text-sm leading-relaxed z-10 hidden lg:block border-t border-zinc-800/50 pt-5">
                    {!! nl2br(e($character->lore)) !!}
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Chatbox -->
        <div class="w-full lg:w-2/3 flex flex-col h-[600px] lg:h-full">
            <div class="bg-zinc-900/40 backdrop-blur-xl border border-zinc-800 shadow-sm rounded-3xl flex flex-col h-full overflow-hidden relative">
                
                <!-- Chat Header -->
                <div class="p-4 px-6 border-b border-zinc-800 bg-zinc-900/50 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <img src="{{ $character->image_url ?? 'https://via.placeholder.com/50' }}" class="w-10 h-10 rounded-full object-cover border border-zinc-700/50">
                            <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 rounded-full border-2 border-zinc-900"></div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-zinc-100 text-sm leading-tight line-clamp-1">{{ $character->name }}</h3>
                            <div class="flex items-center gap-2 mt-0.5">
                                <p class="text-[11px] text-zinc-500 font-medium">Persona Engine Active</p>
                                <span id="scenarioIndicator" class="hidden px-2 py-0.5 rounded text-[9px] font-bold bg-zinc-800 text-zinc-300 border border-zinc-700">Scenario Active</span>
                            </div>
                        </div>
                    </div>
                    <button onclick="openScenarioModal()" class="flex items-center gap-1.5 text-xs font-medium text-zinc-400 hover:text-zinc-200 bg-zinc-800/50 hover:bg-zinc-800 border border-zinc-700/50 px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                        <span>🎭</span> Scenario
                    </button>
                </div>

                <!-- Chat Messages Area -->
                <div class="flex-grow p-4 lg:p-6 overflow-y-auto custom-scrollbar flex flex-col gap-4" id="chatbox">
                    <!-- Initial Welcome Message from System -->
                    <div class="flex justify-center mb-2">
                        <span class="text-xs text-zinc-500 bg-zinc-900/80 px-4 py-2 rounded-full border border-zinc-800 shadow-sm font-medium">
                            Connection established with {{ $character->name }}
                        </span>
                    </div>
                </div>

                <!-- Chat Input Area -->
                <div class="p-4 px-6 border-t border-zinc-800 bg-zinc-900/50 flex-shrink-0">
                    <form id="chatForm" class="relative group">
                        <input type="text" id="chatInput" placeholder="Message {{ $character->name }}..." class="w-full bg-zinc-900 border border-zinc-800 rounded-2xl pl-5 pr-12 py-3.5 text-sm text-zinc-200 placeholder-zinc-600 focus:outline-none focus:border-zinc-600 focus:bg-zinc-800/50 transition-all shadow-sm">
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 flex items-center justify-center bg-zinc-800 rounded-xl text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700 transition-all disabled:opacity-50">
                            <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </main>

    <!-- Scenario Modal -->
    <div id="scenarioModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 invisible transition-all duration-300 p-4">
        <div class="bg-zinc-950 border border-zinc-800 shadow-2xl rounded-2xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300" id="scenarioModalContent">
            <h2 class="text-lg font-semibold text-zinc-100 mb-1 flex items-center gap-2"><span>🎭</span> Custom Scenario</h2>
            <p class="text-xs text-zinc-500 mb-5">Set a specific background event or environment for the AI to react to.</p>
            <textarea id="scenarioInput" rows="4" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-3 text-sm text-zinc-200 placeholder-zinc-600 focus:outline-none focus:border-zinc-700 transition-colors custom-scrollbar" placeholder="e.g. We are stuck in an elevator and the lights just went out..."></textarea>
            <div class="flex justify-end gap-3 mt-5">
                <button onclick="closeScenarioModal()" class="px-4 py-2 text-sm font-medium text-zinc-400 hover:text-zinc-200 transition-colors">Cancel</button>
                <button onclick="saveScenario()" class="px-5 py-2 text-sm font-medium bg-zinc-100 hover:bg-white text-zinc-900 rounded-lg shadow-sm transition-all">Save Scenario</button>
            </div>
        </div>
    </div>

    <script>
        let currentScenario = "";
        
        function openScenarioModal() {
            const modal = document.getElementById('scenarioModal');
            const content = document.getElementById('scenarioModalContent');
            document.getElementById('scenarioInput').value = currentScenario;
            modal.classList.remove('opacity-0', 'invisible');
            content.classList.remove('scale-95');
        }

        function closeScenarioModal() {
            const modal = document.getElementById('scenarioModal');
            const content = document.getElementById('scenarioModalContent');
            modal.classList.add('opacity-0', 'invisible');
            content.classList.add('scale-95');
        }

        function saveScenario() {
            currentScenario = document.getElementById('scenarioInput').value.trim();
            const indicator = document.getElementById('scenarioIndicator');
            if (currentScenario) {
                indicator.classList.remove('hidden');
            } else {
                indicator.classList.add('hidden');
            }
            closeScenarioModal();
        }

        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatbox = document.getElementById('chatbox');
        const submitBtn = chatForm.querySelector('button');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const apiId = "{{ $character->api_id }}";
        const characterImage = "{{ $character->image_url ?? 'https://via.placeholder.com/50' }}";

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (!message) return;

            appendUserMessage(message);
            chatInput.value = '';
            submitBtn.disabled = true;

            const loadingId = appendTypingIndicator();
            scrollToBottom();

            try {
                const response = await fetch(`/character/${apiId}/chat`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: message, scenario: currentScenario })
                });

                const data = await response.json();
                removeElement(loadingId);

                if (response.ok) {
                    appendBotMessage(data.reply, data.emotion);
                    updateEmotionTheme(data.emotion);
                    if (data.background_prompt) {
                        updateDynamicBackground(data.background_prompt);
                    }
                } else {
                    appendBotMessage('⚠️ ' + (data.error || 'Terjadi kesalahan sistem.'), 'neutral');
                }

            } catch (error) {
                removeElement(loadingId);
                appendBotMessage('⚠️ Koneksi terputus. Gagal menghubungi server.', 'neutral');
            } finally {
                submitBtn.disabled = false;
                scrollToBottom();
                chatInput.focus();
            }
        });

        function appendUserMessage(text) {
            const div = document.createElement('div');
            div.className = "flex gap-3 flex-row-reverse mb-2";
            div.innerHTML = `
                <div class="bg-zinc-800 text-zinc-200 px-5 py-3 rounded-2xl rounded-tr-none max-w-[85%] text-sm shadow-sm border border-zinc-700/50">
                    <p>${escapeHTML(text)}</p>
                </div>
            `;
            chatbox.appendChild(div);
        }

        function appendBotMessage(text, emotion = 'neutral') {
            const formattedText = escapeHTML(text).replace(/\n/g, '<br>');
            
            // Elegant styling map
            let emoji = '';
            let borderAccent = 'border-l-zinc-700';

            if (emotion === 'happy') { emoji = '😊'; borderAccent = 'border-l-yellow-500'; }
            else if (emotion === 'angry') { emoji = '😡'; borderAccent = 'border-l-red-500'; }
            else if (emotion === 'sad') { emoji = '😢'; borderAccent = 'border-l-blue-500'; }
            else if (emotion === 'shocked') { emoji = '😲'; borderAccent = 'border-l-orange-500'; }
            else if (emotion === 'smug') { emoji = '😏'; borderAccent = 'border-l-emerald-500'; }

            const emojiSpan = emoji ? `<span class="inline-block mr-1 opacity-80">${emoji}</span>` : '';

            const div = document.createElement('div');
            div.className = `flex gap-3 mb-4`;
            div.innerHTML = `
                <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0 border border-zinc-700/50 mt-1 shadow-sm">
                    <img src="${characterImage}" class="w-full h-full object-cover">
                </div>
                <div class="bg-zinc-900/80 border border-zinc-800 border-l-2 ${borderAccent} text-zinc-300 px-5 py-3.5 rounded-2xl rounded-tl-none max-w-[85%] text-sm shadow-sm leading-relaxed transition-colors duration-500">
                    <p>${emojiSpan}${formattedText}</p>
                </div>
            `;
            chatbox.appendChild(div);
        }

        let currentBgPrompt = "";
        function updateDynamicBackground(prompt) {
            if (prompt && prompt !== currentBgPrompt && prompt.trim() !== "") {
                currentBgPrompt = prompt;
                const encodedPrompt = encodeURIComponent(prompt + " masterpiece anime style scenery background environment");
                const url = `https://image.pollinations.ai/prompt/${encodedPrompt}?width=1920&height=1080&nologo=true`;
                
                const bgElement = document.getElementById('dynamicBg');
                if (bgElement) {
                    const img = new Image();
                    img.src = url;
                    img.onload = () => {
                        bgElement.style.backgroundImage = `url('${url}')`;
                        bgElement.style.backgroundSize = 'cover';
                        bgElement.style.backgroundPosition = 'center';
                        bgElement.style.opacity = '0.3';
                    };
                }
            }
        }

        function updateEmotionTheme(emotion) {
            const glow1 = document.getElementById('glow1');
            if(!glow1) return;

            // Base class for glow
            glow1.className = 'fixed top-0 left-0 w-[800px] h-[800px] rounded-full blur-[120px] pointer-events-none -translate-x-1/2 -translate-y-1/2 z-0 transition-colors duration-1000 ';

            if (emotion === 'happy') { glow1.classList.add('bg-yellow-500/5'); }
            else if (emotion === 'angry') { glow1.classList.add('bg-red-500/5'); }
            else if (emotion === 'sad') { glow1.classList.add('bg-blue-500/5'); }
            else if (emotion === 'shocked') { glow1.classList.add('bg-orange-500/5'); }
            else if (emotion === 'smug') { glow1.classList.add('bg-emerald-500/5'); }
            else { glow1.classList.add('bg-zinc-500/5'); }
        }

        function appendTypingIndicator() {
            const id = 'typing-' + Date.now();
            const div = document.createElement('div');
            div.id = id;
            div.className = "flex gap-3 mb-2";
            div.innerHTML = `
                <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0 border border-zinc-700/50 mt-1 shadow-sm">
                    <img src="${characterImage}" class="w-full h-full object-cover">
                </div>
                <div class="bg-zinc-900/80 border border-zinc-800 px-4 py-4 rounded-2xl rounded-tl-none flex items-center gap-1.5 shadow-sm">
                    <div class="w-1.5 h-1.5 bg-zinc-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                    <div class="w-1.5 h-1.5 bg-zinc-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                    <div class="w-1.5 h-1.5 bg-zinc-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                </div>
            `;
            chatbox.appendChild(div);
            return id;
        }

        function removeElement(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
        }

        function scrollToBottom() {
            chatbox.scrollTop = chatbox.scrollHeight;
        }

        function escapeHTML(str) {
            return str.replace(/[&<>'"]/g, tag => ({'&': '&amp;','<': '&lt;','>': '&gt;',"'": '&#39;','"': '&quot;'}[tag]));
        }
    </script>
</body>
</html>
