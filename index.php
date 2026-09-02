<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
$v = time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Xadrez Clássico | 4U.IA.BR</title>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="manifest.json?v=<?php echo $v; ?>">
    <meta name="theme-color" content="#1a1612">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="apple-touch-icon" href="icon-192.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Cinzel+Decorative:wght@400;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body {
            font-family: 'Cormorant Garamond', Georgia, serif;
            background: radial-gradient(ellipse at center, #1e1814 0%, #0c0907 100%);
            min-height: 100vh;
            color: #d4c4a8;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 400 400' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            opacity: 0.03;
            pointer-events: none;
            z-index: 0;
        }
        .title-font { font-family: 'Cinzel Decorative', serif; letter-spacing: 0.12em; }
        .mono-font { font-family: 'Fira Code', monospace; }
        
        /* Board Frame */
        .board-frame {
            background: linear-gradient(145deg, #2e211b 0%, #1c1410 50%, #0d0a08 100%);
            padding: 8px;
            border-radius: 6px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.85), 0 10px 30px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,220,180,0.15);
            position: relative;
        }
        .board-frame::before {
            content: '';
            position: absolute;
            inset: 4px;
            border: 1px solid rgba(180,140,80,0.2);
            border-radius: 4px;
            pointer-events: none;
        }
        .board-container {
            background: linear-gradient(145deg, #443224 0%, #36261b 50%, #241912 100%);
            padding: 10px;
            border-radius: 3px;
            box-shadow: inset 0 2px 4px rgba(255,220,180,0.08), inset 0 -2px 4px rgba(0,0,0,0.5);
        }
        .chess-board {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            border: 3px solid #18110c;
            box-shadow: 0 0 0 1px rgba(180,140,80,0.25), inset 0 0 30px rgba(0,0,0,0.35);
        }
        .square {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            cursor: pointer;
            position: relative;
            transition: all 0.15s ease;
            user-select: none;
        }
        @media (max-width: 640px) {
            .square { width: 42px; height: 42px; font-size: 28px; }
            .board-container { padding: 4px; }
            .board-frame { padding: 4px; }
        }
        .square.light { background: linear-gradient(135deg, #e8dcc8 0%, #ddd0bc 50%, #d4c5af 100%); }
        .square.dark { background: linear-gradient(135deg, #8b7355 0%, #7a644a 50%, #6b5740 100%); }
        .square:hover { filter: brightness(1.08); }
        .square.selected { box-shadow: inset 0 0 0 3px rgba(212,175,55,0.95); filter: brightness(1.15); }
        .square.valid-move::after {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            background: radial-gradient(circle, rgba(120,100,60,0.6) 0%, rgba(120,100,60,0.3) 100%);
            border-radius: 50%;
            pointer-events: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .square.valid-capture::after {
            content: '';
            position: absolute;
            inset: 4px;
            border: 3px solid rgba(180,70,50,0.7);
            border-radius: 50%;
            pointer-events: none;
        }
        .square.check { animation: checkGlow 1.8s ease-in-out infinite; }
        @keyframes checkGlow {
            0%, 100% { box-shadow: inset 0 0 20px rgba(200,60,40,0.7); }
            50% { box-shadow: inset 0 0 35px rgba(230,80,60,0.9); }
        }
        .square.last-move { box-shadow: inset 0 0 0 2px rgba(212,175,55,0.6); }
        
        .piece { transition: transform 0.12s ease; user-select: none; line-height: 1; }
        .piece:hover { transform: scale(1.08); }
        .piece.white {
            color: #faf6f0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5), 0 0 1px rgba(0,0,0,0.3);
            filter: drop-shadow(2px 3px 3px rgba(0,0,0,0.35));
        }
        .piece.black {
            color: #1a1512;
            text-shadow: 1px 1px 0 rgba(80,70,60,0.3), -1px -1px 0 rgba(100,90,80,0.2);
            filter: drop-shadow(2px 3px 3px rgba(0,0,0,0.3));
        }
        
        /* Panels */
        .side-panel {
            background: linear-gradient(180deg, rgba(32,25,20,0.95) 0%, rgba(18,14,12,0.98) 100%);
            border: 1px solid rgba(180,140,80,0.18);
            border-radius: 6px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,220,180,0.08);
            backdrop-filter: blur(10px);
        }
        .panel-header {
            border-bottom: 1px solid rgba(180,140,80,0.12);
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        /* Move History Table */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .history-table th {
            text-align: left;
            padding: 4px 6px;
            color: rgba(180,140,80,0.6);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid rgba(180,140,80,0.1);
        }
        .history-table td {
            padding: 4px 6px;
            border-bottom: 1px solid rgba(180,140,80,0.05);
            font-family: 'Fira Code', monospace;
            font-size: 12px;
        }
        .history-table tr:hover {
            background: rgba(180,140,80,0.08);
        }
        .history-table tr.active-row {
            background: rgba(212,175,55,0.12);
        }
        
        .elegant-btn {
            background: linear-gradient(180deg, rgba(65,48,36,0.95) 0%, rgba(42,30,22,0.98) 100%);
            border: 1px solid rgba(180,140,80,0.25);
            color: #d4c4a8;
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.04em;
            padding: 7px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,220,180,0.1);
        }
        .elegant-btn:hover:not(:disabled) {
            background: linear-gradient(180deg, rgba(85,62,46,0.95) 0%, rgba(55,38,28,0.98) 100%);
            border-color: rgba(212,175,55,0.5);
            color: #f0e4d0;
            transform: translateY(-1px);
        }
        .elegant-btn:active:not(:disabled) {
            transform: translateY(0);
        }
        .elegant-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .action-icon-btn {
            background: rgba(45,34,26,0.8);
            border: 1px solid rgba(180,140,80,0.2);
            color: #c9b896;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 13px;
        }
        .action-icon-btn:hover {
            background: rgba(70,52,40,0.9);
            border-color: rgba(212,175,55,0.5);
            color: #fff;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(8,6,5,0.85);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: linear-gradient(180deg, #2a1f18 0%, #1a120e 100%);
            border: 1px solid rgba(180,140,80,0.3);
            border-radius: 6px;
            padding: 24px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.8);
        }

        /* Toast notification */
        #toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: rgba(20,15,12,0.95);
            border: 1px solid rgba(212,175,55,0.4);
            color: #d4c4a8;
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 13px;
            font-family: 'Fira Code', monospace;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 2000;
            pointer-events: none;
        }
        #toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen p-3 md:p-6 justify-between">

    <!-- Header -->
    <header class="max-w-6xl w-full mx-auto flex flex-wrap items-center justify-between gap-4 mb-4 z-10">
        <div class="flex items-center gap-3">
            <a href="https://4u.ia.br" class="text-2xl font-bold title-font text-amber-200/90 hover:text-amber-100 transition-colors flex items-center gap-2">
                <span>♚</span>
                <span>4U.IA.BR</span>
            </a>
            <span class="text-xs tracking-widest text-amber-500/60 uppercase border-l border-amber-500/30 pl-3 hidden sm:inline">
                Xadrez Clássico • Minimax Engine
            </span>
        </div>

        <!-- Quick Controls -->
        <div class="flex items-center gap-2">
            <button id="soundToggleBtn" onclick="toggleSound()" class="action-icon-btn flex items-center gap-1.5" title="Ativar/Desativar Som">
                <span id="soundIcon">🔊</span>
                <span class="text-xs hidden md:inline">Som</span>
            </button>
            <div class="flex items-center gap-1.5 bg-black/40 px-3 py-1.5 rounded border border-amber-900/30 text-xs text-amber-300/80">
                <span>Nível:</span>
                <select id="difficultySelect" onchange="changeDifficulty()" class="bg-transparent text-amber-200 outline-none cursor-pointer font-serif">
                    <option value="2" class="bg-zinc-900 text-amber-200">Fácil (2)</option>
                    <option value="3" class="bg-zinc-900 text-amber-200" selected>Médio (3)</option>
                    <option value="4" class="bg-zinc-900 text-amber-200">Mestre (4)</option>
                </select>
            </div>
        </div>
    </header>

    <!-- Main Game Arena -->
    <main class="max-w-6xl w-full mx-auto flex flex-col lg:flex-row items-start justify-center gap-6 flex-1 z-10">
        
        <!-- Left Panel: IA / Adversário & Placar -->
        <div class="w-full lg:w-64 flex flex-col gap-4 order-2 lg:order-1">
            <div class="side-panel p-4">
                <div class="panel-header flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-600/80 animate-pulse"></div>
                        <span class="text-base font-semibold text-amber-200/90">Máquina (IA)</span>
                    </div>
                    <span id="aiScoreBadge" class="text-xs px-2 py-0.5 rounded bg-black/40 text-amber-400 font-mono">0</span>
                </div>
                <div class="text-xs text-amber-500/60 mb-2">Peças capturadas:</div>
                <div id="aiCaptured" class="min-h-7 text-lg tracking-wide select-none text-zinc-100"></div>
            </div>

            <!-- Game Actions -->
            <div class="side-panel p-4 flex flex-col gap-2.5">
                <div class="text-xs tracking-wider uppercase text-amber-500/70 font-semibold mb-1">Controles</div>
                <button onclick="undoMove()" id="undoBtn" class="elegant-btn flex items-center justify-center gap-2 w-full" disabled>
                    <span>↩</span> Desfazer Jogada
                </button>
                <button onclick="resetGame()" class="elegant-btn flex items-center justify-center gap-2 w-full">
                    <span>🔄</span> Nova Partida
                </button>
            </div>

            <!-- FEN / PGN Exporters -->
            <div class="side-panel p-4 flex flex-col gap-2.5">
                <div class="text-xs tracking-wider uppercase text-amber-500/70 font-semibold mb-1">Exportar Partida</div>
                <button onclick="copyPGN()" class="action-icon-btn flex items-center justify-center gap-2 w-full">
                    <span>📋</span> Copiar PGN (Notação)
                </button>
                <button onclick="copyFEN()" class="action-icon-btn flex items-center justify-center gap-2 w-full">
                    <span>📋</span> Copiar FEN (Posição)
                </button>
            </div>
        </div>

        <!-- Center: Chess Board -->
        <div class="flex flex-col items-center order-1 lg:order-2">
            <div class="board-frame">
                <div class="board-container">
                    <!-- Top Letters Coordinates -->
                    <div class="flex justify-around mb-1 text-[11px] font-semibold text-amber-500/60 select-none">
                        <span>a</span><span>b</span><span>c</span><span>d</span><span>e</span><span>f</span><span>g</span><span>h</span>
                    </div>

                    <div class="flex items-center">
                        <!-- Left Numbers Coordinates -->
                        <div class="flex flex-col justify-around mr-1.5 text-[11px] font-semibold text-amber-500/60 select-none h-[480px] sm:h-[480px]">
                            <span>8</span><span>7</span><span>6</span><span>5</span><span>4</span><span>3</span><span>2</span><span>1</span>
                        </div>

                        <!-- Board Grid -->
                        <div id="chessBoard" class="chess-board"></div>

                        <!-- Right Numbers Coordinates -->
                        <div class="flex flex-col justify-around ml-1.5 text-[11px] font-semibold text-amber-500/60 select-none h-[480px] sm:h-[480px]">
                            <span>8</span><span>7</span><span>6</span><span>5</span><span>4</span><span>3</span><span>2</span><span>1</span>
                        </div>
                    </div>

                    <!-- Bottom Letters Coordinates -->
                    <div class="flex justify-around mt-1 text-[11px] font-semibold text-amber-500/60 select-none">
                        <span>a</span><span>b</span><span>c</span><span>d</span><span>e</span><span>f</span><span>g</span><span>h</span>
                    </div>
                </div>
            </div>

            <!-- Turn Status Bar -->
            <div id="statusBar" class="mt-3 px-4 py-2 bg-black/60 rounded-full border border-amber-900/30 text-xs tracking-wide text-amber-200/90 flex items-center gap-2">
                <span id="turnIndicatorDot" class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span id="statusText">Sua vez de jogar (Brancas)</span>
            </div>
        </div>

        <!-- Right Panel: Jogador Humano & Notação PGN -->
        <div class="w-full lg:w-72 flex flex-col gap-4 order-3">
            <div class="side-panel p-4">
                <div class="panel-header flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                        <span class="text-base font-semibold text-amber-200/90">Você (Brancas)</span>
                    </div>
                    <span id="playerScoreBadge" class="text-xs px-2 py-0.5 rounded bg-black/40 text-amber-400 font-mono">0</span>
                </div>
                <div class="text-xs text-amber-500/60 mb-2">Peças capturadas:</div>
                <div id="playerCaptured" class="min-h-7 text-lg tracking-wide select-none text-zinc-900"></div>
            </div>

            <!-- Tournament PGN Move History -->
            <div class="side-panel p-4 flex-1 flex flex-col min-h-[260px] max-h-[380px]">
                <div class="panel-header flex items-center justify-between">
                    <span class="text-xs tracking-wider uppercase text-amber-500/70 font-semibold">Notação PGN</span>
                    <span id="moveCountBadge" class="text-[10px] font-mono text-amber-400/60">0 Lances</span>
                </div>
                <div id="historyScroll" class="flex-1 overflow-y-auto pr-1 select-text">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th width="22%">#</th>
                                <th width="39%">Brancas</th>
                                <th width="39%">Pretas</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <!-- Injected dynamically -->
                        </tbody>
                    </table>
                    <div id="emptyHistoryText" class="text-center py-8 text-xs italic text-amber-500/40">
                        Nenhum lance efetuado ainda.
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Promotion Modal -->
    <div id="promotionModal" class="modal">
        <div class="modal-content text-center max-w-xs w-full">
            <h3 class="text-lg font-semibold mb-3 text-amber-200">Promover Peão</h3>
            <p class="text-xs text-amber-400/70 mb-4">Escolha a nova peça:</p>
            <div id="promotionOptions" class="flex justify-center gap-3 text-3xl"></div>
        </div>
    </div>

    <!-- Game Over Modal -->
    <div id="gameOverModal" class="modal">
        <div class="modal-content text-center max-w-sm w-full">
            <div class="text-3xl mb-2" id="gameOverIcon">🏆</div>
            <h2 id="gameOverTitle" class="title-font text-xl font-bold mb-2 text-amber-200"></h2>
            <p id="gameOverMessage" class="text-sm mb-6 text-amber-100/80"></p>
            <div class="flex gap-3 justify-center">
                <button onclick="resetGame(); closeGameOverModal();" class="elegant-btn px-6 py-2">
                    Nova Partida
                </button>
                <button onclick="copyPGN()" class="action-icon-btn px-4 py-2">
                    Copiar PGN
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast">Mensagem copiada</div>

    <!-- Footer Padrão 4U.IA.BR -->
    <footer class="max-w-6xl w-full mx-auto mt-6 pt-4 border-t border-amber-900/20 text-center text-xs text-amber-500/50 flex flex-wrap items-center justify-between gap-3 z-10">
        <div>
            © <?php echo date('Y'); ?> <b>4U.IA.BR</b> — Todos os direitos reservados.
        </div>
        <div class="flex gap-4">
            <a href="https://github.com/4u-Labs" target="_blank" class="hover:text-amber-300 transition-colors">🐙 GitHub 4u-Labs</a>
            <a href="https://4u.ia.br/politica.html" class="hover:text-amber-300 transition-colors">Privacidade</a>
            <a href="https://4u.ia.br/termos.html" class="hover:text-amber-300 transition-colors">Termos</a>
            <a href="https://4u.ia.br/suporte.html" class="hover:text-amber-300 transition-colors">Suporte</a>
        </div>
    </footer>

    <!-- Game Logic & Web Audio Synthesizer -->
    <script>
        // ==========================================
        // 1. SINTETIZADOR WEB AUDIO API PURO
        // ==========================================
        class ChessAudioSynthesizer {
            constructor() {
                this.ctx = null;
                this.isMuted = localStorage.getItem('chess_sound_muted') === 'true';
            }

            init() {
                if (!this.ctx) {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (AudioCtx) {
                        this.ctx = new AudioCtx();
                    }
                }
                if (this.ctx && this.ctx.state === 'suspended') {
                    this.ctx.resume();
                }
            }

            playMove() {
                if (this.isMuted) return;
                this.init();
                if (!this.ctx) return;
                
                // Realistic Wood Knock: sine wave pitch drop + soft white noise click
                const t = this.ctx.currentTime;
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(360, t);
                osc.frequency.exponentialRampToValueAtTime(110, t + 0.08);

                gain.gain.setValueAtTime(0.4, t);
                gain.gain.exponentialRampToValueAtTime(0.001, t + 0.08);

                osc.connect(gain);
                gain.connect(this.ctx.destination);
                osc.start(t);
                osc.stop(t + 0.08);

                this.createNoiseBurst(t, 0.03, 0.15, 1200);
            }

            playCapture() {
                if (this.isMuted) return;
                this.init();
                if (!this.ctx) return;

                const t = this.ctx.currentTime;
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();

                // Heavier impact thud
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(220, t);
                osc.frequency.exponentialRampToValueAtTime(60, t + 0.14);

                gain.gain.setValueAtTime(0.6, t);
                gain.gain.exponentialRampToValueAtTime(0.001, t + 0.14);

                osc.connect(gain);
                gain.connect(this.ctx.destination);
                osc.start(t);
                osc.stop(t + 0.14);

                this.createNoiseBurst(t, 0.06, 0.35, 1800);
            }

            playCheck() {
                if (this.isMuted) return;
                this.init();
                if (!this.ctx) return;

                const t = this.ctx.currentTime;
                // Dual chime bell alert (587Hz -> 880Hz / D5 -> A5)
                this.playTone(587.33, t, 0.12, 0.25, 'sine');
                this.playTone(880.00, t + 0.08, 0.2, 0.3, 'sine');
            }

            playCastle() {
                if (this.isMuted) return;
                this.init();
                if (!this.ctx) return;

                // Double wood slide click
                this.playMove();
                setTimeout(() => this.playMove(), 110);
            }

            playPromote() {
                if (this.isMuted) return;
                this.init();
                if (!this.ctx) return;

                const t = this.ctx.currentTime;
                // Triumphant ascending triad (C5 -> E5 -> G5)
                this.playTone(523.25, t, 0.12, 0.25, 'triangle');
                this.playTone(659.25, t + 0.09, 0.12, 0.25, 'triangle');
                this.playTone(783.99, t + 0.18, 0.25, 0.3, 'triangle');
            }

            playVictory() {
                if (this.isMuted) return;
                this.init();
                if (!this.ctx) return;

                const t = this.ctx.currentTime;
                this.playTone(523.25, t, 0.15, 0.3, 'sine');
                this.playTone(659.25, t + 0.12, 0.15, 0.3, 'sine');
                this.playTone(783.99, t + 0.24, 0.2, 0.35, 'sine');
                this.playTone(1046.50, t + 0.40, 0.45, 0.4, 'sine');
            }

            playDefeat() {
                if (this.isMuted) return;
                this.init();
                if (!this.ctx) return;

                const t = this.ctx.currentTime;
                this.playTone(440.00, t, 0.2, 0.3, 'sine');
                this.playTone(415.30, t + 0.18, 0.2, 0.3, 'sine');
                this.playTone(392.00, t + 0.36, 0.35, 0.3, 'sine');
                this.playTone(329.63, t + 0.55, 0.5, 0.25, 'sine');
            }

            playTone(freq, startTime, duration, vol, type = 'sine') {
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                osc.type = type;
                osc.frequency.setValueAtTime(freq, startTime);
                gain.gain.setValueAtTime(vol, startTime);
                gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
                osc.connect(gain);
                gain.connect(this.ctx.destination);
                osc.start(startTime);
                osc.stop(startTime + duration);
            }

            createNoiseBurst(startTime, duration, vol, cutoff) {
                const bufferSize = Math.floor(this.ctx.sampleRate * duration);
                const buffer = this.ctx.createBuffer(1, bufferSize, this.ctx.sampleRate);
                const data = buffer.getChannelData(0);
                for (let i = 0; i < bufferSize; i++) {
                    data[i] = Math.random() * 2 - 1;
                }
                const noise = this.ctx.createBufferSource();
                noise.buffer = buffer;

                const filter = this.ctx.createBiquadFilter();
                filter.type = 'lowpass';
                filter.frequency.setValueAtTime(cutoff, startTime);

                const gain = this.ctx.createGain();
                gain.gain.setValueAtTime(vol, startTime);
                gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);

                noise.connect(filter);
                filter.connect(gain);
                gain.connect(this.ctx.destination);

                noise.start(startTime);
                noise.stop(startTime + duration);
            }

            toggle() {
                this.isMuted = !this.isMuted;
                localStorage.setItem('chess_sound_muted', this.isMuted);
                return !this.isMuted;
            }
        }

        const chessAudio = new ChessAudioSynthesizer();

        function toggleSound() {
            const isSoundOn = chessAudio.toggle();
            document.getElementById('soundIcon').textContent = isSoundOn ? '🔊' : '🔇';
            showToast(isSoundOn ? 'Som ativado' : 'Som desativado');
        }

        if (chessAudio.isMuted) {
            document.getElementById('soundIcon').textContent = '🔇';
        }

        // ==========================================
        // 2. CONSTANTES E TABELAS DE AVALIAÇÃO
        // ==========================================
        const PIECES = {
            wK: '♔', wQ: '♕', wR: '♖', wB: '♗', wN: '♘', wP: '♙',
            bK: '♚', bQ: '♛', bR: '♜', bB: '♝', bN: '♞', bP: '♟'
        };

        const PIECE_VALUES = { P: 100, N: 320, B: 330, R: 500, Q: 900, K: 20000 };

        const POSITION_TABLES = {
            P: [
                [0,  0,  0,  0,  0,  0,  0,  0],
                [50, 50, 50, 50, 50, 50, 50, 50],
                [10, 10, 20, 30, 30, 20, 10, 10],
                [5,  5, 10, 25, 25, 10,  5,  5],
                [0,  0,  0, 20, 20,  0,  0,  0],
                [5, -5,-10,  0,  0,-10, -5,  5],
                [5, 10, 10,-20,-20, 10, 10,  5],
                [0,  0,  0,  0,  0,  0,  0,  0]
            ],
            N: [
                [-50,-40,-30,-30,-30,-30,-40,-50],
                [-40,-20,  0,  0,  0,  0,-20,-40],
                [-30,  0, 10, 15, 15, 10,  0,-30],
                [-30,  5, 15, 20, 20, 15,  5,-30],
                [-30,  0, 15, 20, 20, 15,  0,-30],
                [-30,  5, 10, 15, 15, 10,  5,-30],
                [-40,-20,  0,  5,  5,  0,-20,-40],
                [-50,-40,-30,-30,-30,-30,-40,-50]
            ],
            B: [
                [-20,-10,-10,-10,-10,-10,-10,-20],
                [-10,  0,  0,  0,  0,  0,  0,-10],
                [-10,  0,  5, 10, 10,  5,  0,-10],
                [-10,  5,  5, 10, 10,  5,  5,-10],
                [-10,  0, 10, 10, 10, 10,  0,-10],
                [-10, 10, 10, 10, 10, 10, 10,-10],
                [-10,  5,  0,  0,  0,  0,  5,-10],
                [-20,-10,-10,-10,-10,-10,-10,-20]
            ],
            R: [
                [0,  0,  0,  0,  0,  0,  0,  0],
                [5, 10, 10, 10, 10, 10, 10,  5],
                [-5,  0,  0,  0,  0,  0,  0, -5],
                [-5,  0,  0,  0,  0,  0,  0, -5],
                [-5,  0,  0,  0,  0,  0,  0, -5],
                [-5,  0,  0,  0,  0,  0,  0, -5],
                [-5,  0,  0,  0,  0,  0,  0, -5],
                [0,  0,  0,  5,  5,  0,  0,  0]
            ],
            Q: [
                [-20,-10,-10, -5, -5,-10,-10,-20],
                [-10,  0,  0,  0,  0,  0,  0,-10],
                [-10,  0,  5,  5,  5,  5,  0,-10],
                [-5,  0,  5,  5,  5,  5,  0, -5],
                [0,  0,  5,  5,  5,  5,  0, -5],
                [-10,  5,  5,  5,  5,  5,  0,-10],
                [-10,  0,  5,  0,  0,  0,  0,-10],
                [-20,-10,-10, -5, -5,-10,-10,-20]
            ],
            K: [
                [-30,-40,-40,-50,-50,-40,-40,-30],
                [-30,-40,-40,-50,-50,-40,-40,-30],
                [-30,-40,-40,-50,-50,-40,-40,-30],
                [-30,-40,-40,-50,-50,-40,-40,-30],
                [-20,-30,-30,-40,-40,-30,-30,-20],
                [-10,-20,-20,-20,-20,-20,-20,-10],
                [20, 20,  0,  0,  0,  0, 20, 20],
                [20, 30, 10,  0,  0, 10, 30, 20]
            ]
        };

        const INITIAL_BOARD = [
            ['bR','bN','bB','bQ','bK','bB','bN','bR'],
            ['bP','bP','bP','bP','bP','bP','bP','bP'],
            ['','','','','','','',''],
            ['','','','','','','',''],
            ['','','','','','','',''],
            ['','','','','','','',''],
            ['wP','wP','wP','wP','wP','wP','wP','wP'],
            ['wR','wN','wB','wQ','wK','wB','wN','wR']
        ];

        // ==========================================
        // 3. ESTADO DO JOGO
        // ==========================================
        let board = [];
        let currentPlayer = 'white';
        let selectedSquare = null;
        let validMoves = [];
        let moveHistory = [];
        let rawMovesList = [];
        let capturedPieces = { white: [], black: [] };
        let castlingRights = { wK: true, wQ: true, bK: true, bQ: true };
        let enPassantSquare = null;
        let lastMove = null;
        let gameOver = false;
        let moveStack = [];
        let isThinking = false;
        let searchDepth = 3;
        let halfMoveClock = 0;
        let fullMoveNumber = 1;
        let gameStartDate = new Date().toISOString().split('T')[0].replace(/-/g, '.');

        const HUMAN_COLOR = 'white';
        const AI_COLOR = 'black';

        function initGame() {
            board = INITIAL_BOARD.map(row => [...row]);
            currentPlayer = 'white';
            selectedSquare = null;
            validMoves = [];
            moveHistory = [];
            rawMovesList = [];
            capturedPieces = { white: [], black: [] };
            castlingRights = { wK: true, wQ: true, bK: true, bQ: true };
            enPassantSquare = null;
            lastMove = null;
            gameOver = false;
            moveStack = [];
            isThinking = false;
            halfMoveClock = 0;
            fullMoveNumber = 1;
            gameStartDate = new Date().toISOString().split('T')[0].replace(/-/g, '.');

            renderBoard();
            updateUI();
            updateHistoryTable();
        }

        function changeDifficulty() {
            searchDepth = parseInt(document.getElementById('difficultySelect').value);
            showToast('Dificuldade: Nível ' + searchDepth);
        }

        // ==========================================
        // 4. RENDERIZAÇÃO DO TABULEIRO
        // ==========================================
        function renderBoard() {
            const boardEl = document.getElementById('chessBoard');
            boardEl.innerHTML = '';

            const kingInCheck = isInCheck(currentPlayer);
            let checkedKingPos = null;
            if (kingInCheck) {
                checkedKingPos = findKing(currentPlayer);
            }

            for (let row = 0; row < 8; row++) {
                for (let col = 0; col < 8; col++) {
                    const square = document.createElement('div');
                    square.className = 'square ' + ((row + col) % 2 === 0 ? 'light' : 'dark');
                    square.dataset.row = row;
                    square.dataset.col = col;

                    if (selectedSquare && selectedSquare.row === row && selectedSquare.col === col) {
                        square.classList.add('selected');
                    }

                    if (lastMove) {
                        if ((lastMove.from.row === row && lastMove.from.col === col) ||
                            (lastMove.to.row === row && lastMove.to.col === col)) {
                            square.classList.add('last-move');
                        }
                    }

                    if (checkedKingPos && checkedKingPos.row === row && checkedKingPos.col === col) {
                        square.classList.add('check');
                    }

                    const isValidMove = validMoves.some(m => m.row === row && m.col === col);
                    if (isValidMove) {
                        if (board[row][col] || (validMoves.find(m => m.row === row && m.col === col)?.enPassant)) {
                            square.classList.add('valid-capture');
                        } else {
                            square.classList.add('valid-move');
                        }
                    }

                    const piece = board[row][col];
                    if (piece) {
                        const pieceEl = document.createElement('span');
                        pieceEl.className = 'piece ' + (piece[0] === 'w' ? 'white' : 'black');
                        pieceEl.textContent = PIECES[piece];
                        square.appendChild(pieceEl);
                    }

                    square.addEventListener('click', () => handleSquareClick(row, col));
                    boardEl.appendChild(square);
                }
            }
        }

        // ==========================================
        // 5. INTERAÇÃO & CLIQUE DE JOGADA
        // ==========================================
        function handleSquareClick(row, col) {
            if (gameOver || isThinking) return;
            if (currentPlayer !== HUMAN_COLOR) return;

            chessAudio.init();

            const clickedPiece = board[row][col];

            if (selectedSquare) {
                if (selectedSquare.row === row && selectedSquare.col === col) {
                    selectedSquare = null;
                    validMoves = [];
                    renderBoard();
                    return;
                }

                const move = validMoves.find(m => m.row === row && m.col === col);
                if (move) {
                    executePlayerMove(selectedSquare.row, selectedSquare.col, row, col, move);
                    selectedSquare = null;
                    validMoves = [];
                    return;
                }

                if (clickedPiece && clickedPiece[0] === 'w') {
                    selectedSquare = { row, col };
                    validMoves = getValidMoves(row, col);
                    renderBoard();
                    return;
                }

                selectedSquare = null;
                validMoves = [];
                renderBoard();
            } else {
                if (clickedPiece && clickedPiece[0] === 'w') {
                    selectedSquare = { row, col };
                    validMoves = getValidMoves(row, col);
                    renderBoard();
                }
            }
        }

        function executePlayerMove(fromRow, fromCol, toRow, toCol, moveData) {
            const piece = board[fromRow][fromCol];
            if (piece[1] === 'P' && (toRow === 0 || toRow === 7)) {
                showPromotionModal(fromRow, fromCol, toRow, toCol, moveData, 'white');
                return;
            }
            applyMoveAndTriggerAI(fromRow, fromCol, toRow, toCol, moveData, null);
        }

        function showPromotionModal(fromRow, fromCol, toRow, toCol, moveData, color) {
            const modal = document.getElementById('promotionModal');
            const options = document.getElementById('promotionOptions');
            options.innerHTML = '';

            const pieces = ['Q', 'R', 'B', 'N'];
            const prefix = color === 'white' ? 'w' : 'b';

            pieces.forEach(p => {
                const btn = document.createElement('button');
                btn.className = 'p-3 rounded hover:bg-amber-800/30 transition-all border border-amber-900/30';
                btn.innerHTML = `<span class="piece ${color}">${PIECES[prefix + p]}</span>`;
                btn.onclick = () => {
                    modal.classList.remove('active');
                    applyMoveAndTriggerAI(fromRow, fromCol, toRow, toCol, moveData, p);
                };
                options.appendChild(btn);
            });

            modal.classList.add('active');
        }

        function applyMoveAndTriggerAI(fromRow, fromCol, toRow, toCol, moveData, promoPiece) {
            makeMove(fromRow, fromCol, toRow, toCol, moveData, promoPiece);
            renderBoard();
            updateUI();
            updateHistoryTable();

            if (!gameOver && currentPlayer === AI_COLOR) {
                isThinking = true;
                document.getElementById('turnIndicatorDot').className = 'w-2 h-2 rounded-full bg-amber-400 animate-ping';
                document.getElementById('statusText').textContent = 'Máquina calculando lance...';

                setTimeout(() => {
                    makeAIMove();
                    isThinking = false;
                    renderBoard();
                    updateUI();
                    updateHistoryTable();
                }, 200);
            }
        }

        // ==========================================
        // 6. MOTOR DE MOVIMENTAÇÃO & REGRAS
        // ==========================================
        function makeMove(fromRow, fromCol, toRow, toCol, moveData, promotionPiece = null) {
            const piece = board[fromRow][fromCol];
            const captured = board[toRow][toCol];
            const color = piece[0] === 'w' ? 'white' : 'black';
            const prefix = piece[0];
            const isCapture = !!captured || (moveData && moveData.enPassant);
            const isCastle = moveData && !!moveData.castle;
            const isPromo = piece[1] === 'P' && (toRow === 0 || toRow === 7);

            // Compute SAN
            let sanNotation = computeSAN(fromRow, fromCol, toRow, toCol, piece, captured, moveData, promotionPiece);

            // Save undo state
            moveStack.push({
                board: board.map(r => [...r]),
                castlingRights: { ...castlingRights },
                enPassantSquare: enPassantSquare ? { ...enPassantSquare } : null,
                capturedPieces: { white: [...capturedPieces.white], black: [...capturedPieces.black] },
                lastMove: lastMove ? { ...lastMove } : null,
                currentPlayer,
                halfMoveClock,
                fullMoveNumber,
                moveHistory: JSON.parse(JSON.stringify(moveHistory)),
                rawMovesList: [...rawMovesList]
            });

            if (piece[1] === 'P' || captured) {
                halfMoveClock = 0;
            } else {
                halfMoveClock++;
            }

            // Board Transition
            if (isCastle) {
                const kingRow = color === 'white' ? 7 : 0;
                if (moveData.castle === 'K') {
                    board[kingRow][6] = piece;
                    board[kingRow][4] = '';
                    board[kingRow][5] = prefix + 'R';
                    board[kingRow][7] = '';
                } else {
                    board[kingRow][2] = piece;
                    board[kingRow][4] = '';
                    board[kingRow][3] = prefix + 'R';
                    board[kingRow][0] = '';
                }
                castlingRights[prefix + 'K'] = false;
                castlingRights[prefix + 'Q'] = false;
            } else if (moveData && moveData.enPassant) {
                const capturedRow = color === 'white' ? toRow + 1 : toRow - 1;
                const capturedPawn = board[capturedRow][toCol];
                capturedPieces[color].push(capturedPawn);
                board[capturedRow][toCol] = '';
                board[toRow][toCol] = piece;
                board[fromRow][fromCol] = '';
            } else {
                if (captured) {
                    capturedPieces[color].push(captured);
                }
                board[toRow][toCol] = piece;
                board[fromRow][fromCol] = '';
            }

            if (piece[1] === 'K') {
                castlingRights[prefix + 'K'] = false;
                castlingRights[prefix + 'Q'] = false;
            }
            if (piece[1] === 'R') {
                if (fromCol === 0) castlingRights[prefix + 'Q'] = false;
                if (fromCol === 7) castlingRights[prefix + 'K'] = false;
            }
            if (captured && captured[1] === 'R') {
                const enemyPrefix = captured[0];
                if (toCol === 0) castlingRights[enemyPrefix + 'Q'] = false;
                if (toCol === 7) castlingRights[enemyPrefix + 'K'] = false;
            }

            if (piece[1] === 'P' && Math.abs(fromRow - toRow) === 2) {
                enPassantSquare = { row: (fromRow + toRow) / 2, col: fromCol };
            } else {
                enPassantSquare = null;
            }

            if (isPromo) {
                const finalPiece = promotionPiece || 'Q';
                board[toRow][toCol] = prefix + finalPiece;
                if (!sanNotation.includes('=')) {
                    sanNotation += '=' + finalPiece;
                }
            }

            lastMove = { from: { row: fromRow, col: fromCol }, to: { row: toRow, col: toCol } };

            const opponentColor = color === 'white' ? 'black' : 'white';
            currentPlayer = opponentColor;

            if (color === 'black') {
                fullMoveNumber++;
            }

            const opponentInCheck = isInCheck(opponentColor);
            if (opponentInCheck) {
                if (isCheckmate(opponentColor)) {
                    sanNotation += '#';
                    gameOver = true;
                    setTimeout(() => {
                        chessAudio.playVictory();
                        showGameOver(color === 'white' ? 'Vitória por Xeque-mate! 🏆' : 'A Máquina Venceu ♟️', 'Partida concluída.');
                    }, 300);
                } else {
                    sanNotation += '+';
                    chessAudio.playCheck();
                }
            } else if (isStalemate(opponentColor)) {
                gameOver = true;
                setTimeout(() => {
                    chessAudio.playDraw();
                    showGameOver('Empate por Afogamento', 'Nenhum lance legal disponível.');
                }, 300);
            } else if (isInsufficientMaterial()) {
                gameOver = true;
                setTimeout(() => {
                    chessAudio.playDraw();
                    showGameOver('Empate', 'Material insuficiente para mate.');
                }, 300);
            } else if (halfMoveClock >= 100) {
                gameOver = true;
                setTimeout(() => {
                    chessAudio.playDraw();
                    showGameOver('Empate', 'Regra dos 50 movimentos.');
                }, 300);
            } else {
                if (isCastle) {
                    chessAudio.playCastle();
                } else if (isPromo) {
                    chessAudio.playPromote();
                } else if (isCapture) {
                    chessAudio.playCapture();
                } else {
                    chessAudio.playMove();
                }
            }

            rawMovesList.push(sanNotation);
            if (color === 'white') {
                moveHistory.push({ num: moveHistory.length + 1, white: sanNotation, black: '' });
            } else {
                if (moveHistory.length > 0) {
                    moveHistory[moveHistory.length - 1].black = sanNotation;
                } else {
                    moveHistory.push({ num: 1, white: '...', black: sanNotation });
                }
            }

            return true;
        }

        // ==========================================
        // 7. NOTAÇÃO ALGÉBRICA REAL (SAN)
        // ==========================================
        function computeSAN(fromRow, fromCol, toRow, toCol, piece, captured, moveData, promoPiece) {
            if (moveData && moveData.castle) {
                return moveData.castle === 'K' ? 'O-O' : 'O-O-O';
            }

            const pieceType = piece[1];
            const targetSquare = getSquareName(toRow, toCol);
            const isCapture = !!captured || (moveData && moveData.enPassant);

            if (pieceType === 'P') {
                let s = '';
                if (isCapture) {
                    s = getFileFromCol(fromCol) + 'x' + targetSquare;
                } else {
                    s = targetSquare;
                }
                if (promoPiece) {
                    s += '=' + promoPiece;
                }
                return s;
            }

            let disambiguation = getDisambiguation(fromRow, fromCol, toRow, toCol, piece);
            let captureChar = isCapture ? 'x' : '';
            return pieceType + disambiguation + captureChar + targetSquare;
        }

        function getDisambiguation(fromRow, fromCol, toRow, toCol, piece) {
            const pieceType = piece[1];
            let samePieces = [];

            for (let r = 0; r < 8; r++) {
                for (let c = 0; c < 8; c++) {
                    if (r === fromRow && c === fromCol) continue;
                    const p = board[r][c];
                    if (p && p[0] === piece[0] && p[1] === pieceType) {
                        const moves = getValidMoves(r, c);
                        if (moves.some(m => m.row === toRow && m.col === toCol)) {
                            samePieces.push({ row: r, col: c });
                        }
                    }
                }
            }

            if (samePieces.length === 0) return '';

            const sameCol = samePieces.some(p => p.col === fromCol);
            const sameRow = samePieces.some(p => p.row === fromRow);

            if (!sameCol) {
                return getFileFromCol(fromCol);
            } else if (!sameRow) {
                return (8 - fromRow).toString();
            } else {
                return getFileFromCol(fromCol) + (8 - fromRow).toString();
            }
        }

        // ==========================================
        // 8. GERADORES DE PGN E FEN
        // ==========================================
        function generatePGN() {
            let result = '*';
            if (gameOver) {
                if (isCheckmate('black')) result = '1-0';
                else if (isCheckmate('white')) result = '0-1';
                else result = '1/2-1/2';
            }

            let pgn = `[Event "4U.IA.BR Xadrez Clássico"]\n`;
            pgn += `[Site "https://4u.ia.br/app/xadrez"]\n`;
            pgn += `[Date "${gameStartDate}"]\n`;
            pgn += `[Round "1"]\n`;
            pgn += `[White "Humano"]\n`;
            pgn += `[Black "IA Minimax"]\n`;
            pgn += `[Result "${result}"]\n\n`;

            let moveStrings = [];
            moveHistory.forEach(turn => {
                let str = `${turn.num}. ${turn.white}`;
                if (turn.black) {
                    str += ` ${turn.black}`;
                }
                moveStrings.push(str);
            });

            pgn += moveStrings.join(' ') + (result !== '*' ? ` ${result}` : '');
            return pgn;
        }

        function generateFEN() {
            let fen = '';
            for (let r = 0; r < 8; r++) {
                let empty = 0;
                for (let c = 0; c < 8; c++) {
                    const p = board[r][c];
                    if (!p) {
                        empty++;
                    } else {
                        if (empty > 0) {
                            fen += empty;
                            empty = 0;
                        }
                        const letter = p[1];
                        fen += p[0] === 'w' ? letter.toUpperCase() : letter.toLowerCase();
                    }
                }
                if (empty > 0) fen += empty;
                if (r < 7) fen += '/';
            }

            fen += ' ' + (currentPlayer === 'white' ? 'w' : 'b');

            let castleStr = '';
            if (castlingRights.wK) castleStr += 'K';
            if (castlingRights.wQ) castleStr += 'Q';
            if (castlingRights.bK) castleStr += 'k';
            if (castlingRights.bQ) castleStr += 'q';
            fen += ' ' + (castleStr || '-');

            if (enPassantSquare) {
                fen += ' ' + getSquareName(enPassantSquare.row, enPassantSquare.col);
            } else {
                fen += ' -';
            }

            fen += ` ${halfMoveClock} ${fullMoveNumber}`;
            return fen;
        }

        function copyPGN() {
            const pgn = generatePGN();
            navigator.clipboard.writeText(pgn).then(() => {
                showToast('PGN copiado com sucesso!');
            }).catch(() => {
                showToast('Erro ao copiar PGN');
            });
        }

        function copyFEN() {
            const fen = generateFEN();
            navigator.clipboard.writeText(fen).then(() => {
                showToast('FEN copiado com sucesso!');
            }).catch(() => {
                showToast('Erro ao copiar FEN');
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2200);
        }

        // ==========================================
        // 9. REGRAS DE MOVIMENTAÇÃO
        // ==========================================
        function getValidMoves(row, col) {
            const piece = board[row][col];
            if (!piece) return [];
            const color = piece[0] === 'w' ? 'white' : 'black';
            const type = piece[1];
            let pseudo = [];

            switch (type) {
                case 'P': pseudo = getPawnMoves(row, col, color); break;
                case 'R': pseudo = getRookMoves(row, col, color); break;
                case 'N': pseudo = getKnightMoves(row, col, color); break;
                case 'B': pseudo = getBishopMoves(row, col, color); break;
                case 'Q': pseudo = getQueenMoves(row, col, color); break;
                case 'K': pseudo = getKingMoves(row, col, color); break;
            }

            return pseudo.filter(m => !moveLeavesKingInCheck(row, col, m.row, m.col, color, m));
        }

        function moveLeavesKingInCheck(fromRow, fromCol, toRow, toCol, color, move) {
            const piece = board[fromRow][fromCol];
            const destPiece = board[toRow][toCol];
            let epRow = -1, epCol = -1, epPiece = '';

            board[fromRow][fromCol] = '';
            board[toRow][toCol] = piece;

            if (move && move.enPassant) {
                epRow = color === 'white' ? toRow + 1 : toRow - 1;
                epCol = toCol;
                epPiece = board[epRow][epCol];
                board[epRow][epCol] = '';
            }

            const inCheck = isInCheck(color);

            board[fromRow][fromCol] = piece;
            board[toRow][toCol] = destPiece;
            if (move && move.enPassant) {
                board[epRow][epCol] = epPiece;
            }

            return inCheck;
        }

        function getPawnMoves(row, col, color) {
            const moves = [];
            const dir = color === 'white' ? -1 : 1;
            const startRow = color === 'white' ? 6 : 1;

            if (isValidSquare(row + dir, col) && !board[row + dir][col]) {
                moves.push({ row: row + dir, col });
                if (row === startRow && !board[row + 2 * dir][col]) {
                    moves.push({ row: row + 2 * dir, col });
                }
            }

            [-1, 1].forEach(dc => {
                const tr = row + dir, tc = col + dc;
                if (isValidSquare(tr, tc)) {
                    const target = board[tr][tc];
                    if (target && target[0] !== color[0]) {
                        moves.push({ row: tr, col: tc });
                    }
                    if (enPassantSquare && enPassantSquare.row === tr && enPassantSquare.col === tc) {
                        moves.push({ row: tr, col: tc, enPassant: true });
                    }
                }
            });

            return moves;
        }

        function getRookMoves(row, col, color) {
            return getSlidingMoves(row, col, color, [[-1,0],[1,0],[0,-1],[0,1]]);
        }

        function getBishopMoves(row, col, color) {
            return getSlidingMoves(row, col, color, [[-1,-1],[-1,1],[1,-1],[1,1]]);
        }

        function getQueenMoves(row, col, color) {
            return getSlidingMoves(row, col, color, [[-1,0],[1,0],[0,-1],[0,1],[-1,-1],[-1,1],[1,-1],[1,1]]);
        }

        function getSlidingMoves(row, col, color, dirs) {
            const moves = [];
            dirs.forEach(([dr, dc]) => {
                let r = row + dr, c = col + dc;
                while (isValidSquare(r, c)) {
                    const target = board[r][c];
                    if (!target) {
                        moves.push({ row: r, col: c });
                    } else {
                        if (target[0] !== color[0]) moves.push({ row: r, col: c });
                        break;
                    }
                    r += dr; c += dc;
                }
            });
            return moves;
        }

        function getKnightMoves(row, col, color) {
            const moves = [];
            const deltas = [[-2,-1],[-2,1],[-1,-2],[-1,2],[1,-2],[1,2],[2,-1],[2,1]];
            deltas.forEach(([dr, dc]) => {
                const r = row + dr, c = col + dc;
                if (isValidSquare(r, c)) {
                    const target = board[r][c];
                    if (!target || target[0] !== color[0]) {
                        moves.push({ row: r, col: c });
                    }
                }
            });
            return moves;
        }

        function getKingMoves(row, col, color) {
            const moves = [];
            const deltas = [[-1,-1],[-1,0],[-1,1],[0,-1],[0,1],[1,-1],[1,0],[1,1]];
            deltas.forEach(([dr, dc]) => {
                const r = row + dr, c = col + dc;
                if (isValidSquare(r, c)) {
                    const target = board[r][c];
                    if (!target || target[0] !== color[0]) {
                        moves.push({ row: r, col: c });
                    }
                }
            });

            const prefix = color === 'white' ? 'w' : 'b';
            const kRow = color === 'white' ? 7 : 0;
            if (row === kRow && col === 4 && !isInCheck(color)) {
                if (castlingRights[prefix + 'K'] && !board[kRow][5] && !board[kRow][6] && board[kRow][7] === prefix + 'R') {
                    if (!isSquareAttacked(kRow, 5, color === 'white' ? 'black' : 'white') &&
                        !isSquareAttacked(kRow, 6, color === 'white' ? 'black' : 'white')) {
                        moves.push({ row: kRow, col: 6, castle: 'K' });
                    }
                }
                if (castlingRights[prefix + 'Q'] && !board[kRow][1] && !board[kRow][2] && !board[kRow][3] && board[kRow][0] === prefix + 'R') {
                    if (!isSquareAttacked(kRow, 3, color === 'white' ? 'black' : 'white') &&
                        !isSquareAttacked(kRow, 2, color === 'white' ? 'black' : 'white')) {
                        moves.push({ row: kRow, col: 2, castle: 'Q' });
                    }
                }
            }

            return moves;
        }

        function isValidSquare(r, c) { return r >= 0 && r < 8 && c >= 0 && c < 8; }

        function findKing(color) {
            const target = color === 'white' ? 'wK' : 'bK';
            for (let r = 0; r < 8; r++) {
                for (let c = 0; c < 8; c++) {
                    if (board[r][c] === target) return { row: r, col: c };
                }
            }
            return null;
        }

        function isInCheck(color) {
            const king = findKing(color);
            if (!king) return false;
            return isSquareAttacked(king.row, king.col, color === 'white' ? 'black' : 'white');
        }

        function isSquareAttacked(row, col, byColor) {
            const pDir = byColor === 'white' ? 1 : -1;
            if (isValidSquare(row + pDir, col - 1) && board[row + pDir][col - 1] === (byColor === 'white' ? 'wP' : 'bP')) return true;
            if (isValidSquare(row + pDir, col + 1) && board[row + pDir][col + 1] === (byColor === 'white' ? 'wP' : 'bP')) return true;

            const nDeltas = [[-2,-1],[-2,1],[-1,-2],[-1,2],[1,-2],[1,2],[2,-1],[2,1]];
            for (let [dr, dc] of nDeltas) {
                const r = row + dr, c = col + dc;
                if (isValidSquare(r, c) && board[r][c] === (byColor === 'white' ? 'wN' : 'bN')) return true;
            }

            const kDeltas = [[-1,-1],[-1,0],[-1,1],[0,-1],[0,1],[1,-1],[1,0],[1,1]];
            for (let [dr, dc] of kDeltas) {
                const r = row + dr, c = col + dc;
                if (isValidSquare(r, c) && board[r][c] === (byColor === 'white' ? 'wK' : 'bK')) return true;
            }

            const orthogonal = [[-1,0],[1,0],[0,-1],[0,1]];
            const diagonal = [[-1,-1],[-1,1],[1,-1],[1,1]];
            const pR = byColor === 'white' ? 'wR' : 'bR';
            const pB = byColor === 'white' ? 'wB' : 'bB';
            const pQ = byColor === 'white' ? 'wQ' : 'bQ';

            for (let [dr, dc] of orthogonal) {
                let r = row + dr, c = col + dc;
                while (isValidSquare(r, c)) {
                    if (board[r][c]) {
                        if (board[r][c] === pR || board[r][c] === pQ) return true;
                        break;
                    }
                    r += dr; c += dc;
                }
            }

            for (let [dr, dc] of diagonal) {
                let r = row + dr, c = col + dc;
                while (isValidSquare(r, c)) {
                    if (board[r][c]) {
                        if (board[r][c] === pB || board[r][c] === pQ) return true;
                        break;
                    }
                    r += dr; c += dc;
                }
            }

            return false;
        }

        function getAllValidMoves(color) {
            const moves = [];
            for (let r = 0; r < 8; r++) {
                for (let c = 0; c < 8; c++) {
                    const p = board[r][c];
                    if (p && ((color === 'white' && p[0] === 'w') || (color === 'black' && p[0] === 'b'))) {
                        const vm = getValidMoves(r, c);
                        vm.forEach(m => moves.push({ fromRow: r, fromCol: c, toRow: m.row, toCol: m.col, moveData: m }));
                    }
                }
            }
            return moves;
        }

        function isCheckmate(color) { return isInCheck(color) && getAllValidMoves(color).length === 0; }
        function isStalemate(color) { return !isInCheck(color) && getAllValidMoves(color).length === 0; }
        function isInsufficientMaterial() {
            let pieces = [];
            for (let r = 0; r < 8; r++) {
                for (let c = 0; c < 8; c++) {
                    if (board[r][c]) pieces.push(board[r][c]);
                }
            }
            if (pieces.length === 2) return true;
            if (pieces.length === 3 && pieces.some(p => p[1] === 'B' || p[1] === 'N')) return true;
            return false;
        }

        // ==========================================
        // 10. IA MINIMAX COM ALPHA-BETA
        // ==========================================
        function makeAIMove() {
            const bestMove = findBestMove();
            if (bestMove) {
                makeMove(bestMove.fromRow, bestMove.fromCol, bestMove.toRow, bestMove.toCol, bestMove.moveData, 'Q');
            }
        }

        function findBestMove() {
            const moves = getAllValidMoves(AI_COLOR);
            if (moves.length === 0) return null;

            let bestScore = -Infinity;
            let bestMove = moves[0];

            for (let move of moves) {
                const state = saveEngineState();
                executeEngineMove(move);

                const score = minimax(searchDepth - 1, -Infinity, Infinity, false);
                restoreEngineState(state);

                if (score > bestScore) {
                    bestScore = score;
                    bestMove = move;
                }
            }

            return bestMove;
        }

        function minimax(depth, alpha, beta, isMaximizing) {
            if (depth === 0) return evaluateBoard();

            const currentColor = isMaximizing ? AI_COLOR : HUMAN_COLOR;
            const moves = getAllValidMoves(currentColor);

            if (moves.length === 0) {
                if (isInCheck(currentColor)) {
                    return isMaximizing ? -20000 + (searchDepth - depth) : 20000 - (searchDepth - depth);
                }
                return 0;
            }

            if (isMaximizing) {
                let maxEval = -Infinity;
                for (let move of moves) {
                    const state = saveEngineState();
                    executeEngineMove(move);
                    const ev = minimax(depth - 1, alpha, beta, false);
                    restoreEngineState(state);
                    maxEval = Math.max(maxEval, ev);
                    alpha = Math.max(alpha, ev);
                    if (beta <= alpha) break;
                }
                return maxEval;
            } else {
                let minEval = Infinity;
                for (let move of moves) {
                    const state = saveEngineState();
                    executeEngineMove(move);
                    const ev = minimax(depth - 1, alpha, beta, true);
                    restoreEngineState(state);
                    minEval = Math.min(minEval, ev);
                    beta = Math.min(beta, ev);
                    if (beta <= alpha) break;
                }
                return minEval;
            }
        }

        function evaluateBoard() {
            let total = 0;
            for (let r = 0; r < 8; r++) {
                for (let c = 0; c < 8; c++) {
                    const p = board[r][c];
                    if (p) {
                        const val = PIECE_VALUES[p[1]];
                        const table = POSITION_TABLES[p[1]];
                        let posVal = 0;
                        if (table) {
                            posVal = p[0] === 'w' ? table[r][c] : table[7 - r][c];
                        }
                        const score = val + posVal;
                        total += p[0] === 'b' ? score : -score;
                    }
                }
            }
            return total;
        }

        function saveEngineState() {
            return {
                board: board.map(r => [...r]),
                castlingRights: { ...castlingRights },
                enPassantSquare: enPassantSquare ? { ...enPassantSquare } : null
            };
        }

        function restoreEngineState(s) {
            board = s.board.map(r => [...r]);
            castlingRights = { ...s.castlingRights };
            enPassantSquare = s.enPassantSquare ? { ...s.enPassantSquare } : null;
        }

        function executeEngineMove(m) {
            const p = board[m.fromRow][m.fromCol];
            board[m.fromRow][m.fromCol] = '';
            board[m.toRow][m.toCol] = p;

            if (m.moveData && m.moveData.enPassant) {
                const capRow = p[0] === 'w' ? m.toRow + 1 : m.toRow - 1;
                board[capRow][m.toCol] = '';
            }
            if (p[1] === 'P' && (m.toRow === 0 || m.toRow === 7)) {
                board[m.toRow][m.toCol] = p[0] + 'Q';
            }
        }

        // ==========================================
        // 11. UI & CONTROLES AUXILIARES
        // ==========================================
        function updateUI() {
            document.getElementById('aiCaptured').textContent = capturedPieces.black.map(p => PIECES[p]).join(' ');
            document.getElementById('playerCaptured').textContent = capturedPieces.white.map(p => PIECES[p]).join(' ');

            let whiteMat = capturedPieces.white.reduce((acc, p) => acc + (PIECE_VALUES[p[1]] || 0), 0);
            let blackMat = capturedPieces.black.reduce((acc, p) => acc + (PIECE_VALUES[p[1]] || 0), 0);
            document.getElementById('playerScoreBadge').textContent = whiteMat > blackMat ? `+${(whiteMat - blackMat)/100}` : '0';
            document.getElementById('aiScoreBadge').textContent = blackMat > whiteMat ? `+${(blackMat - whiteMat)/100}` : '0';

            const statusEl = document.getElementById('statusText');
            const dotEl = document.getElementById('turnIndicatorDot');
            if (gameOver) {
                dotEl.className = 'w-2 h-2 rounded-full bg-red-500';
                statusEl.textContent = 'Partida Encerrada';
            } else if (currentPlayer === 'white') {
                dotEl.className = 'w-2 h-2 rounded-full bg-emerald-400';
                statusEl.textContent = 'Sua vez de jogar (Brancas)';
            } else {
                dotEl.className = 'w-2 h-2 rounded-full bg-amber-400';
                statusEl.textContent = 'Vez da Máquina (Pretas)';
            }

            document.getElementById('undoBtn').disabled = moveStack.length === 0 || isThinking || gameOver;
        }

        function updateHistoryTable() {
            const tbody = document.getElementById('historyTableBody');
            const emptyText = document.getElementById('emptyHistoryText');
            const badge = document.getElementById('moveCountBadge');
            const scroll = document.getElementById('historyScroll');

            tbody.innerHTML = '';
            badge.textContent = `${moveHistory.length} Lances`;

            if (moveHistory.length === 0) {
                emptyText.style.display = 'block';
                return;
            }

            emptyText.style.display = 'none';

            moveHistory.forEach((turn, idx) => {
                const tr = document.createElement('tr');
                if (idx === moveHistory.length - 1) tr.classList.add('active-row');
                tr.innerHTML = `
                    <td class="text-amber-500/60">${turn.num}.</td>
                    <td class="text-amber-100 font-semibold">${turn.white}</td>
                    <td class="text-amber-300/80">${turn.black || ''}</td>
                `;
                tbody.appendChild(tr);
            });

            scroll.scrollTop = scroll.scrollHeight;
        }

        function undoMove() {
            if (moveStack.length < 2 || isThinking) return;
            moveStack.pop();
            const prevState = moveStack.pop();
            if (prevState) {
                board = prevState.board.map(r => [...r]);
                castlingRights = { ...prevState.castlingRights };
                enPassantSquare = prevState.enPassantSquare ? { ...prevState.enPassantSquare } : null;
                capturedPieces = { white: [...prevState.capturedPieces.white], black: [...prevState.capturedPieces.black] };
                lastMove = prevState.lastMove ? { ...prevState.lastMove } : null;
                currentPlayer = prevState.currentPlayer;
                halfMoveClock = prevState.halfMoveClock;
                fullMoveNumber = prevState.fullMoveNumber;
                moveHistory = prevState.moveHistory;
                rawMovesList = prevState.rawMovesList;
                gameOver = false;
                selectedSquare = null;
                validMoves = [];
                renderBoard();
                updateUI();
                updateHistoryTable();
                showToast('Jogada desfeita');
            }
        }

        function resetGame() {
            initGame();
            showToast('Nova partida iniciada');
        }

        function showGameOver(title, message) {
            document.getElementById('gameOverTitle').textContent = title;
            document.getElementById('gameOverMessage').textContent = message;
            document.getElementById('gameOverModal').classList.add('active');
        }

        function closeGameOverModal() {
            document.getElementById('gameOverModal').classList.remove('active');
        }

        function getSquareName(row, col) {
            return getFileFromCol(col) + (8 - row);
        }

        function getFileFromCol(col) {
            return String.fromCharCode(97 + col);
        }

        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('./sw.js').catch(err => console.log('SW fail:', err));
            });
        }

        // Init Game on Load
        window.addEventListener('DOMContentLoaded', () => {
            initGame();
        });
    </script>
</body>
</html>
