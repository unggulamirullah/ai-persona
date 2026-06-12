<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Persona</title>
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
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
<body class="min-h-screen flex flex-col items-center justify-center relative overflow-x-hidden p-6">
    
    <!-- Subtle Spotlight -->
    <div class="fixed top-0 left-1/2 w-[800px] h-[400px] bg-zinc-800/10 rounded-full blur-[100px] pointer-events-none -translate-x-1/2 -translate-y-1/2 z-0"></div>

    <main class="relative z-10 w-full max-w-3xl flex flex-col items-center mt-[-10vh]">
        
        <h1 class="text-4xl lg:text-5xl font-bold mb-3 text-center tracking-tight text-zinc-100">
            AI Persona
        </h1>
        <p class="text-zinc-500 mb-8 text-center text-lg font-medium tracking-wide">Connect with the soul of your favorite characters.</p>

        <a href="/clash" class="mb-12 inline-flex items-center gap-2 px-6 py-2.5 bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 hover:border-zinc-700 rounded-full text-zinc-300 font-medium transition-all group shadow-sm text-sm">
            <span class="text-base grayscale opacity-80 group-hover:grayscale-0 transition-all">⚔️</span> 
            Enter Clash Arena 
            <svg class="w-4 h-4 text-zinc-500 group-hover:translate-x-1 group-hover:text-zinc-300 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
        </a>

        <!-- Search Container -->
        <div class="relative w-full max-w-2xl mx-auto">
            <div class="relative flex items-center bg-zinc-900/40 backdrop-blur-xl border border-zinc-800 rounded-2xl p-1 shadow-sm focus-within:border-zinc-700 focus-within:bg-zinc-900/80 transition-all">
                <svg class="w-5 h-5 text-zinc-500 ml-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" id="searchInput" 
                    class="w-full bg-transparent border-none text-zinc-200 px-2 py-3 text-base focus:outline-none focus:ring-0 placeholder-zinc-600" 
                    placeholder="Search any character..." autocomplete="off">
            </div>

            <!-- Loading Indicator -->
            <div id="loading" class="absolute right-4 top-1/2 -translate-y-1/2 hidden">
                <svg class="animate-spin h-5 w-5 text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- Search Results Dropdown -->
            <div id="searchResults" class="absolute mt-2 w-full bg-zinc-900/90 backdrop-blur-xl border border-zinc-800 rounded-2xl shadow-xl max-h-96 overflow-y-auto custom-scrollbar hidden z-50">
                <!-- Results injected here -->
            </div>
        </div>
    </main>

    <footer class="absolute bottom-6 text-zinc-600 text-xs text-center w-full font-medium">
        Powered by Jikan API & Groq LLM
    </footer>

    <script>
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        const loading = document.getElementById('loading');
        let timeout = null;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(timeout);

            if (query.length < 3) {
                searchResults.classList.add('hidden');
                return;
            }

            loading.classList.remove('hidden');

            timeout = setTimeout(() => {
                fetch(`/api/search?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        loading.classList.add('hidden');
                        searchResults.innerHTML = '';
                        
                        const characters = data.data || [];
                        
                        if (characters.length === 0) {
                            searchResults.innerHTML = `
                                <div class="p-6 text-center text-zinc-500">
                                    No characters found.
                                </div>
                            `;
                        } else {
                            characters.forEach(char => {
                                const img = char.images?.jpg?.image_url || 'https://via.placeholder.com/50';
                                const name = char.name;
                                const id = char.mal_id;
                                const about = char.about ? char.about.substring(0, 80) + '...' : 'No description available.';

                                const div = document.createElement('div');
                                div.className = "flex items-center gap-4 p-4 hover:bg-zinc-800/50 cursor-pointer border-b border-zinc-800/50 transition-colors last:border-0";
                                div.innerHTML = `
                                    <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0 border border-zinc-700/50">
                                        <img src="${img}" alt="${name}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-zinc-200">${name}</span>
                                        <span class="text-xs text-zinc-500 mt-0.5 line-clamp-1">${about}</span>
                                    </div>
                                `;
                                div.addEventListener('click', () => {
                                    window.location.href = `/character/${id}`;
                                });
                                searchResults.appendChild(div);
                            });
                        }
                        searchResults.classList.remove('hidden');
                    })
                    .catch(error => {
                        loading.classList.add('hidden');
                        console.error('Error:', error);
                    });
            }, 400); // 400ms debounce
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
