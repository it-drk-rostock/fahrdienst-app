<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fahrzeugverwaltung Test Upload</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body { font-family: 'Figtree', sans-serif; }
        .bg-grid-pattern {
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 20px 20px;
        }
    </style>
</head>
<body class="antialiased bg-gray-50 text-gray-800 relative min-h-screen">

    <div class="absolute inset-0 z-0 bg-grid-pattern opacity-50 pointer-events-none"></div>
    <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-blue-100/50 to-transparent z-0 pointer-events-none"></div>

    <div class="relative z-10 flex flex-col min-h-screen">

        <div class="flex-1 flex flex-col items-center justify-center px-4 pt-20 pb-12 text-center max-w-5xl mx-auto w-full">
            <div class="inline-block mb-4 px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-bold text-xs uppercase tracking-widest border border-blue-200 shadow-sm">
                V 1.0 Beta
            </div>

            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-6 text-gray-900">
                Fahrzeugverwaltung <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Test Upload</span>
            </h1>

            <p class="text-lg md:text-xl text-gray-600 mb-10 max-w-2xl leading-relaxed">
                Das maßgeschneiderte, intelligente Flotten- und Werkstatt-Management.
                Entwickelt nach den exakten Anforderungen unseres Pflichtenhefts für lückenlose Transparenz.
            </p>

            @auth
                <a href="{{ url('/dashboard') }}" class="px-8 py-4 bg-blue-600 text-white rounded-lg font-bold text-lg shadow-lg hover:bg-blue-700 hover:shadow-xl transition transform hover:-translate-y-1">
                    System starten
                </a>
            @else
                <a href="#" class="px-8 py-4 bg-blue-600 text-white rounded-lg font-bold tracking-widest text-lg shadow-lg hover:bg-black hover:shadow-xl transition transform hover:-translate-y-1">
                    Jetzt Anmelden
                </a>
            @endauth
        </div>

        <div class="max-w-7xl mx-auto px-4 py-12 w-full">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-200 hover:shadow-xl transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -mr-10 -mt-10 transition-transform group-hover:scale-110 z-0"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-2xl mb-6 shadow-sm">📊</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Intelligentes Dashboard</h3>
                        <ul class="space-y-3 text-gray-600 text-sm">
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Ampelsystem für fällige Fristen (Grün, Orange, Rot)</li>
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> "FEHLT"-Warnung bei fehlenden Pflicht-Daten</li>
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Prozentuale Messung der Datenqualität (Doku-Status)</li>
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Smarte Filter (Bereiche, Nur Mängel, Fällige Prüfungen)</li>
                        </ul>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-200 hover:shadow-xl transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-bl-full -mr-10 -mt-10 transition-transform group-hover:scale-110 z-0"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-2xl mb-6 shadow-sm">🗂️</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Digitale Fahrzeugakte</h3>
                        <ul class="space-y-3 text-gray-600 text-sm">
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Dynamische Kriterien: Automatische Erkennung von BOKraft, Lift-UVV & E-Kabeln (DGUV)</li>
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Historie: Gruppierung behobener Mängel nach Rechnungs-Lauf</li>
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Automatische Mangel-Generierung bei nicht bestandener TÜV/HU</li>
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Reifenmanagement (Profil & Druck) mit Alarm-Funktion</li>
                        </ul>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-200 hover:shadow-xl transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-purple-50 rounded-bl-full -mr-10 -mt-10 transition-transform group-hover:scale-110 z-0"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center text-2xl mb-6 shadow-sm">📅</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Smarter Werkstatt-Kalender</h3>
                        <ul class="space-y-3 text-gray-600 text-sm">
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Nahtlose Dispo: Direkt aus der Akte in die Kalender-Planung</li>
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Kollisionswarnung: Erkennt Doppelbuchungen für das gleiche Auto</li>
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Logistik-Kette: Präzise Planung von Hin-/Rückfahrt & Leihwagen</li>
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Schnellerfassung im exakten 15-Minuten-Takt</li>
                        </ul>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-200 hover:shadow-xl transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-rose-50 rounded-bl-full -mr-10 -mt-10 transition-transform group-hover:scale-110 z-0"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center text-2xl mb-6 shadow-sm">⚠️</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Erweitertes Mängel-System</h3>
                        <ul class="space-y-3 text-gray-600 text-sm">
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> "Ping-Pong"-Workflow: Mängel per Klick einem Auftrag zuweisen oder zurückstellen</li>
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> 4-Stufen-System (Leicht bis Kritisch/VU)</li>
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Multi-Bilder-Upload zur Dokumentation der Schäden</li>
                            <li class="flex items-start"><span class="text-green-500 mr-2 font-bold">✓</span> Massen-Erfassung von Mängeln direkt aus Prüfberichten</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

        <footer class="mt-auto py-6 text-center text-gray-400 text-xs border-t border-gray-200 bg-white">
            <p>System-Status: <strong>Demo</strong> | FMS Modul: Werkstatt & Akte</p>
        </footer>

    </div>
</body>
</html>
