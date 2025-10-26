<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Rate Limit Exceeded - CorpVitals24</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-red-50 via-white to-orange-50">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="max-w-lg w-full text-center">
                <!-- Icon -->
                <div class="mb-8">
                    <svg class="w-24 h-24 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <!-- Error Message -->
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    429 - Rate Limit Exceeded
                </h1>
                <p class="text-lg text-gray-600 mb-8">
                    Hai effettuato troppe richieste. Per favore, attendi un momento prima di riprovare.
                </p>

                <!-- Details -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8 text-left">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Dettagli</h2>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <span><strong>Codice errore:</strong> 429 Too Many Requests</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            <span><strong>Suggerimento:</strong> Attendi 1-2 minuti prima di riprovare</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <span><strong>Protezione:</strong> Questo limite protegge l'applicazione da abusi</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-center space-x-4">
                    <a href="{{ url()->previous() }}" class="inline-flex items-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg shadow-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Indietro
                    </a>
                    <a href="{{ route('welcome') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Home
                    </a>
                </div>

                <!-- Footer -->
                <p class="mt-8 text-sm text-gray-500">
                    Se il problema persiste, contatta il supporto: <a href="mailto:support@corpvitals24.test" class="text-indigo-600 hover:text-indigo-700 font-medium">support@corpvitals24.test</a>
                </p>
            </div>
        </div>
    </body>
</html>

