<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clash Arena Setup</title>
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
<body class="min-h-screen flex flex-col items-center pt-[10vh] relative overflow-x-hidden p-6">
    <!-- Subtle Ambient Background -->
    <div class="fixed top-1/2 left-1/4 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-zinc-800/10 rounded-full blur-[120px] pointer-events-none z-0"></div>
    <div class="fixed top-1/2 right-1/4 translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-zinc-800/10 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <nav class="relative z-10 w-full px-8 flex-shrink-0 absolute top-6 left-0">
        <a href="/" class="inline-flex items-center gap-2 text-zinc-500 hover:text-zinc-300 transition-colors font-medium text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Home
        </a>
    </nav>

    <main class="relative z-10 w-full max-w-5xl px-6 flex flex-col items-center mt-4">
        <h1 class="text-3xl lg:text-4xl font-bold mb-3 text-center tracking-tight text-zinc-100">
            Clash Arena
        </h1>
        <p class="text-zinc-500 mb-12 text-center text-sm lg:text-base max-w-xl font-medium">Select two characters to throw them into the arena and watch them interact with you and each other.</p>

        <div class="flex flex-col md:flex-row gap-8 w-full justify-center items-stretch">
            
            <!-- Character 1 Selection -->
            <div class="w-full md:w-1/2 flex flex-col gap-4">
                <div class="bg-zinc-900/40 backdrop-blur-xl border border-zinc-800 rounded-3xl p-6 shadow-sm flex flex-col items-center h-[300px] relative overflow-visible">
                    <h2 class="text-sm font-semibold text-zinc-400 mb-6 uppercase tracking-widest mt-2">Fighter 1</h2>
                    
                    <!-- Selected State 1 -->
                    <div id="selected1" class="hidden flex-col items-center w-full h-full justify-center mt-[-10px]">
                        <img id="img1" src="" class="w-24 h-24 rounded-full object-cover border border-zinc-700/50 mb-4 shadow-sm">
                        <h3 id="name1" class="font-bold text-lg text-zinc-100 text-center line-clamp-1"></h3>
                        <button onclick="resetSelection(1)" class="text-xs text-zinc-500 hover:text-zinc-300 mt-3 bg-zinc-800/50 hover:bg-zinc-800 px-3 py-1.5 rounded-lg transition-all">Change Fighter</button>
                    </div>

                    <!-- Search Input 1 -->
                    <div id="searchContainer1" class="relative w-full mt-4">
                        <input type="text" id="searchInput1" class="w-full bg-zinc-900/80 border border-zinc-800 text-zinc-200 px-4 py-3 rounded-xl focus:border-red-500/30 outline-none transition-all placeholder-zinc-600 text-sm" placeholder="Search first character...">
                        <div id="results1" class="absolute top-full mt-2 w-full bg-zinc-900/95 backdrop-blur-xl border border-zinc-800 rounded-xl shadow-xl max-h-48 overflow-y-auto custom-scrollbar hidden z-50"></div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center font-black text-2xl text-zinc-700 italic px-2">VS</div>

            <!-- Character 2 Selection -->
            <div class="w-full md:w-1/2 flex flex-col gap-4">
                <div class="bg-zinc-900/40 backdrop-blur-xl border border-zinc-800 rounded-3xl p-6 shadow-sm flex flex-col items-center h-[300px] relative overflow-visible">
                    <h2 class="text-sm font-semibold text-zinc-400 mb-6 uppercase tracking-widest mt-2">Fighter 2</h2>
                    
                    <!-- Selected State 2 -->
                    <div id="selected2" class="hidden flex-col items-center w-full h-full justify-center mt-[-10px]">
                        <img id="img2" src="" class="w-24 h-24 rounded-full object-cover border border-zinc-700/50 mb-4 shadow-sm">
                        <h3 id="name2" class="font-bold text-lg text-zinc-100 text-center line-clamp-1"></h3>
                        <button onclick="resetSelection(2)" class="text-xs text-zinc-500 hover:text-zinc-300 mt-3 bg-zinc-800/50 hover:bg-zinc-800 px-3 py-1.5 rounded-lg transition-all">Change Fighter</button>
                    </div>

                    <!-- Search Input 2 -->
                    <div id="searchContainer2" class="relative w-full mt-4">
                        <input type="text" id="searchInput2" class="w-full bg-zinc-900/80 border border-zinc-800 text-zinc-200 px-4 py-3 rounded-xl focus:border-blue-500/30 outline-none transition-all placeholder-zinc-600 text-sm" placeholder="Search second character...">
                        <div id="results2" class="absolute top-full mt-2 w-full bg-zinc-900/95 backdrop-blur-xl border border-zinc-800 rounded-xl shadow-xl max-h-48 overflow-y-auto custom-scrollbar hidden z-50"></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Start Button -->
        <button id="startBtn" onclick="startClash()" disabled class="mt-12 px-10 py-3.5 bg-zinc-100 hover:bg-white rounded-xl font-bold text-sm text-zinc-900 shadow-sm transition-all disabled:opacity-30 disabled:cursor-not-allowed hover:-translate-y-0.5">
            ENTER THE ARENA
        </button>

    </main>

    <script>
        let char1Id = null;
        let char2Id = null;
        let timeout1, timeout2;

        const inputs = [null, document.getElementById('searchInput1'), document.getElementById('searchInput2')];
        const results = [null, document.getElementById('results1'), document.getElementById('results2')];
        const containers = [null, document.getElementById('searchContainer1'), document.getElementById('searchContainer2')];
        const selected = [null, document.getElementById('selected1'), document.getElementById('selected2')];
        
        function initSearch(playerNum) {
            inputs[playerNum].addEventListener('input', (e) => {
                const query = e.target.value.trim();
                clearTimeout(playerNum === 1 ? timeout1 : timeout2);
                
                if (query.length < 3) {
                    results[playerNum].classList.add('hidden');
                    return;
                }

                const timeout = setTimeout(() => {
                    fetch(`/api/search?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            const characters = data.data || [];
                            results[playerNum].innerHTML = '';
                            if(characters.length === 0) {
                                results[playerNum].innerHTML = `<div class="p-4 text-center text-zinc-500 text-xs">No results</div>`;
                            } else {
                                characters.forEach(char => {
                                    const img = char.images?.jpg?.image_url || 'https://via.placeholder.com/50';
                                    const name = char.name;
                                    const id = char.mal_id;

                                    const div = document.createElement('div');
                                    div.className = "flex items-center gap-3 p-3 hover:bg-zinc-800/50 border-b border-zinc-800/50 cursor-pointer transition-colors last:border-0";
                                    div.innerHTML = `
                                        <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0 border border-zinc-700/50">
                                            <img src="${img}" class="w-full h-full object-cover">
                                        </div>
                                        <span class="text-xs font-medium text-zinc-200 line-clamp-1">${name}</span>
                                    `;
                                    div.onclick = () => selectCharacter(playerNum, id, name, img);
                                    results[playerNum].appendChild(div);
                                });
                            }
                            results[playerNum].classList.remove('hidden');
                        });
                }, 300);

                if (playerNum === 1) timeout1 = timeout; else timeout2 = timeout;
            });
        }

        initSearch(1);
        initSearch(2);

        function selectCharacter(playerNum, id, name, imgUrl) {
            if (playerNum === 1) char1Id = id; else char2Id = id;
            
            containers[playerNum].classList.add('hidden');
            selected[playerNum].classList.remove('hidden');
            selected[playerNum].classList.add('flex');
            
            document.getElementById(`name${playerNum}`).innerText = name;
            document.getElementById(`img${playerNum}`).src = imgUrl;

            checkReady();
        }

        function resetSelection(playerNum) {
            if (playerNum === 1) char1Id = null; else char2Id = null;
            
            selected[playerNum].classList.add('hidden');
            selected[playerNum].classList.remove('flex');
            containers[playerNum].classList.remove('hidden');
            inputs[playerNum].value = '';
            results[playerNum].classList.add('hidden');

            checkReady();
        }

        function checkReady() {
            const btn = document.getElementById('startBtn');
            if (char1Id && char2Id) {
                if (char1Id === char2Id) {
                    alert("Characters must be different!");
                    btn.disabled = true;
                } else {
                    btn.disabled = false;
                }
            } else {
                btn.disabled = true;
            }
        }

        function startClash() {
            if (char1Id && char2Id) {
                const btn = document.getElementById('startBtn');
                btn.innerText = "PREPARING...";
                btn.disabled = true;
                window.location.href = `/clash/${char1Id}/${char2Id}`;
            }
        }

        document.addEventListener('click', (e) => {
            if (!containers[1].contains(e.target)) results[1].classList.add('hidden');
            if (!containers[2].contains(e.target)) results[2].classList.add('hidden');
        });
    </script>
</body>
</html>
