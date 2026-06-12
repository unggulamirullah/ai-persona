<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $char1->name }} VS {{ $char2->name }} - Clash Arena</title>
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    
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
    <div class="fixed top-0 left-0 w-[600px] h-[600px] bg-red-500/5 rounded-full blur-[120px] pointer-events-none -translate-x-1/2 -translate-y-1/2 z-0"></div>
    <div class="fixed bottom-0 right-0 w-[600px] h-[600px] bg-blue-500/5 rounded-full blur-[120px] pointer-events-none translate-x-1/2 translate-y-1/2 z-0"></div>
    
    <!-- Navigation -->
    <nav class="relative z-10 w-full p-4 lg:px-6 lg:py-4 flex-shrink-0 flex justify-between items-center">
        <a href="/clash" class="inline-flex items-center gap-2 text-zinc-500 hover:text-zinc-300 transition-colors font-medium text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Leave Arena
        </a>
        <div class="text-zinc-700 font-black text-xl italic opacity-50 tracking-widest">ARENA</div>
    </nav>

    <!-- Main Content Flexbox (3 Columns on Desktop) -->
    <main class="relative z-10 w-full max-w-[1400px] mx-auto px-4 lg:px-6 pb-6 flex-grow flex flex-col lg:flex-row gap-6 lg:h-[calc(100vh-70px)] lg:overflow-hidden">
        
        <!-- LEFT PANEL: Character 1 -->
        <div class="w-full lg:w-1/4 flex flex-col lg:h-full">
            <div class="bg-zinc-900/40 backdrop-blur-xl border border-zinc-800 shadow-sm rounded-3xl p-5 flex flex-col h-full relative overflow-hidden">
                <div class="flex flex-col items-center flex-shrink-0 mt-2">
                    <div class="relative w-28 h-28 lg:w-32 lg:h-40 rounded-2xl overflow-hidden mb-4 border border-zinc-700/50 shadow-sm">
                        <img src="{{ $char1->image_url ?? 'https://via.placeholder.com/300x400' }}" class="w-full h-full object-cover">
                    </div>
                    <h2 class="text-xl font-bold text-center text-zinc-100 mb-1">{{ $char1->name }}</h2>
                    <span class="text-[10px] font-bold text-red-400/80 tracking-widest uppercase mb-4">Fighter 1</span>
                </div>
                <div class="flex-grow overflow-y-auto custom-scrollbar pr-2 text-zinc-400 text-xs leading-relaxed hidden lg:block border-t border-zinc-800/50 pt-4 mt-2">
                    {!! nl2br(e($char1->lore)) !!}
                </div>
            </div>
        </div>

        <!-- CENTER PANEL: Chatbox -->
        <div class="w-full lg:w-2/4 flex flex-col h-[600px] lg:h-full">
            <div class="bg-zinc-900/40 backdrop-blur-xl border border-zinc-800 shadow-sm rounded-3xl flex flex-col h-full overflow-hidden relative">
                
                <!-- Arena Header -->
                <div class="p-4 border-b border-zinc-800 bg-zinc-900/50 flex items-center justify-between flex-shrink-0 relative">
                    <div class="flex flex-col items-center w-full">
                        <h3 class="font-black text-zinc-200 tracking-widest text-sm">CLASH ARENA</h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <p class="text-[11px] text-zinc-500 font-medium">Neural Group Connection Active</p>
                            <span id="scenarioIndicator" class="hidden px-2 py-0.5 rounded text-[9px] font-bold bg-zinc-800 text-zinc-300 border border-zinc-700">Scenario Active</span>
                        </div>
                    </div>
                    <button onclick="openScenarioModal()" class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-1.5 text-xs font-medium text-zinc-400 hover:text-zinc-200 bg-zinc-800/50 hover:bg-zinc-800 border border-zinc-700/50 px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                        <span>🎭</span> Scenario
                    </button>
                </div>

                <!-- Messages -->
                <div class="flex-grow p-4 lg:p-6 overflow-y-auto custom-scrollbar flex flex-col gap-5" id="chatbox">
                    <div class="flex justify-center mb-2">
                        <span class="text-xs text-zinc-500 bg-zinc-900/80 px-4 py-2 rounded-full border border-zinc-800 shadow-sm font-medium">
                            Arena rules initialized. Speak to engage both fighters.
                        </span>
                    </div>
                </div>

                <!-- Input -->
                <div class="p-4 px-6 border-t border-zinc-800 bg-zinc-900/50 flex-shrink-0">
                    <form id="chatForm" class="relative">
                        <input type="text" id="chatInput" placeholder="Speak to them both..." class="w-full bg-zinc-900 border border-zinc-800 rounded-2xl pl-5 pr-12 py-3.5 text-sm text-zinc-200 placeholder-zinc-600 focus:outline-none focus:border-zinc-600 focus:bg-zinc-800/50 transition-all shadow-sm">
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 flex items-center justify-center bg-zinc-800 rounded-xl text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700 transition-all disabled:opacity-50">
                            <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </form>
                </div>

            </div>
        </div>

        <!-- RIGHT PANEL: Character 2 -->
        <div class="w-full lg:w-1/4 flex flex-col lg:h-full">
            <div class="bg-zinc-900/40 backdrop-blur-xl border border-zinc-800 shadow-sm rounded-3xl p-5 flex flex-col h-full relative overflow-hidden">
                <div class="flex flex-col items-center flex-shrink-0 mt-2">
                    <div class="relative w-28 h-28 lg:w-32 lg:h-40 rounded-2xl overflow-hidden mb-4 border border-zinc-700/50 shadow-sm">
                        <img src="{{ $char2->image_url ?? 'https://via.placeholder.com/300x400' }}" class="w-full h-full object-cover">
                    </div>
                    <h2 class="text-xl font-bold text-center text-zinc-100 mb-1">{{ $char2->name }}</h2>
                    <span class="text-[10px] font-bold text-blue-400/80 tracking-widest uppercase mb-4">Fighter 2</span>
                </div>
                <div class="flex-grow overflow-y-auto custom-scrollbar pl-2 text-zinc-400 text-xs leading-relaxed hidden lg:block border-t border-zinc-800/50 pt-4 mt-2">
                    {!! nl2br(e($char2->lore)) !!}
                </div>
            </div>
        </div>
    </main>

    <!-- Scenario Modal -->
    <div id="scenarioModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 invisible transition-all duration-300 p-4">
        <div class="bg-zinc-950 border border-zinc-800 shadow-2xl rounded-2xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300" id="scenarioModalContent">
            <h2 class="text-lg font-semibold text-zinc-100 mb-1 flex items-center gap-2"><span>🎭</span> Arena Scenario</h2>
            <p class="text-xs text-zinc-500 mb-5">Set a specific environment or situation for both fighters to react to.</p>
            <textarea id="scenarioInput" rows="4" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-3 text-sm text-zinc-200 placeholder-zinc-600 focus:outline-none focus:border-zinc-700 transition-colors custom-scrollbar" placeholder="e.g. You are both standing on a sinking ship..."></textarea>
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
        
        const c1Id = "{{ $char1->api_id }}";
        const c2Id = "{{ $char2->api_id }}";
        
        const c1Name = "{{ $char1->name }}";
        const c2Name = "{{ $char2->name }}";
        
        const img1 = "{{ $char1->image_url ?? 'https://via.placeholder.com/50' }}";
        const img2 = "{{ $char2->image_url ?? 'https://via.placeholder.com/50' }}";

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
                const response = await fetch(`/clash/${c1Id}/${c2Id}/chat`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ message: message, scenario: currentScenario })
                });

                const data = await response.json();
                removeElement(loadingId);

                if (response.ok) {
                    if (!data.reply1 && !data.reply2) {
                        appendSystemMessage('⚠️ Error: LLM returned an empty or invalid format.');
                    } else {
                        if (data.reply1) appendCharMessage(1, data.reply1, data.emotion1);
                        setTimeout(() => {
                            if (data.reply2) appendCharMessage(2, data.reply2, data.emotion2);
                            scrollToBottom();
                        }, 800); // slight delay for visual effect
                        if (data.background_prompt) {
                            updateDynamicBackground(data.background_prompt);
                        }
                    }
                } else {
                    appendSystemMessage('⚠️ ' + (data.error || 'System error.'));
                }

            } catch (error) {
                removeElement(loadingId);
                appendSystemMessage('⚠️ Connection lost.');
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

        function appendCharMessage(charNum, text, emotion = 'neutral') {
            const isC1 = charNum === 1;
            const img = isC1 ? img1 : img2;
            const name = isC1 ? c1Name : c2Name;
            
            // Layout specific
            const flexDir = isC1 ? 'flex-row' : 'flex-row-reverse';
            const radius = isC1 ? 'rounded-tl-none' : 'rounded-tr-none';
            const borderAccent = isC1 ? 'border-l-2 border-l-red-500/70' : 'border-r-2 border-r-blue-500/70';
            
            // Emoji map
            let emoji = '';
            if (emotion === 'happy') emoji = '😊';
            else if (emotion === 'angry') emoji = '😡';
            else if (emotion === 'sad') emoji = '😢';
            else if (emotion === 'shocked') emoji = '😲';
            else if (emotion === 'smug') emoji = '😏';
            const emojiSpan = emoji ? `<span class="inline-block mr-1 opacity-80">${emoji}</span>` : '';

            const formattedText = escapeHTML(text).replace(/\n/g, '<br>');

            const div = document.createElement('div');
            div.className = `flex gap-3 mb-2 ${flexDir}`;
            div.innerHTML = `
                <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0 border border-zinc-700/50 mt-1 shadow-sm">
                    <img src="${img}" class="w-full h-full object-cover">
                </div>
                <div class="flex flex-col ${isC1 ? 'items-start' : 'items-end'} max-w-[85%]">
                    <span class="text-[10px] text-zinc-500 font-semibold mb-1 mx-1 line-clamp-1">${name}</span>
                    <div class="bg-zinc-900/80 border border-zinc-800 ${borderAccent} text-zinc-300 px-5 py-3.5 rounded-2xl ${radius} text-sm shadow-sm leading-relaxed">
                        <p>${emojiSpan}${formattedText}</p>
                    </div>
                </div>
            `;
            chatbox.appendChild(div);
        }

        function appendSystemMessage(text) {
            const div = document.createElement('div');
            div.className = "flex justify-center mb-2";
            div.innerHTML = `
                <span class="text-xs text-red-400 bg-red-950/30 px-4 py-2 rounded-full border border-red-900/50 shadow-sm font-medium">
                    ${escapeHTML(text)}
                </span>
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

        function appendTypingIndicator() {
            const id = 'typing-' + Date.now();
            const div = document.createElement('div');
            div.id = id;
            div.className = "flex justify-center mb-2";
            div.innerHTML = `
                <div class="bg-zinc-900/80 border border-zinc-800 px-5 py-3 rounded-2xl flex items-center gap-1.5 shadow-sm">
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
