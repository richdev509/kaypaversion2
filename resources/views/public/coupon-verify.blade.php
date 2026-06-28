<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Coupon — KAYPA</title>
    <link rel="icon" type="image/png" href="{{ asset('kaypa.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-lg space-y-6">

        {{-- Logo / Header --}}
        <div class="text-center">
            <img src="{{ asset('kaypa.png') }}" alt="KAYPA" class="mx-auto h-14 mb-3">
            <h1 class="text-2xl font-bold text-white">Programme Scolaire KAYPA</h1>
            <p class="text-indigo-200 text-sm mt-1">Vérification de bon d'achat partenaire</p>
        </div>

        {{-- Résultat après vérification --}}
        @php $result = session('verification_result'); @endphp

        @if($result)
            @if($result['valid'])
            {{-- Coupon valide --}}
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <div class="bg-green-500 px-6 py-4 flex items-center gap-3">
                    <svg class="w-8 h-8 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-bold text-white text-lg">Coupon valide</p>
                        <p class="text-green-100 text-sm">{{ $result['message'] }}</p>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    @php $enrollment = $result['enrollment']; @endphp
                    <dl class="text-sm space-y-2">
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="text-gray-500">Code coupon</dt>
                            <dd class="font-mono font-bold text-gray-900 tracking-wider">{{ $enrollment->coupon_code }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="text-gray-500">Valeur du coupon</dt>
                            <dd class="font-bold text-2xl text-green-600">{{ number_format($enrollment->coupon_value, 0) }} GDS</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="text-gray-500">Programme</dt>
                            <dd class="text-gray-900">{{ $enrollment->program->name }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="text-gray-500">Valide jusqu'au</dt>
                            <dd class="text-gray-900">{{ $enrollment->program->date_fin->format('d/m/Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Titulaire</dt>
                            <dd class="text-gray-900">{{ $enrollment->client?->first_name }} {{ mb_substr($enrollment->client?->last_name ?? '', 0, 1) }}.</dd>
                        </div>
                    </dl>

                    {{-- Formulaire utilisation --}}
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-sm font-medium text-gray-700 mb-3">Entrez votre code partenaire pour valider la transaction :</p>

                        @if(session('use_error'))
                            <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                                {{ session('use_error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('coupon.use') }}">
                            @csrf
                            <input type="hidden" name="coupon_code" value="{{ $enrollment->coupon_code }}">
                            <div class="flex gap-2">
                                <input type="text" name="code_parrain" required maxlength="20"
                                    placeholder="Code partenaire (ex: AFF123456)"
                                    class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 uppercase"
                                    value="{{ old('code_parrain') }}">
                                <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                    Valider
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @else
            {{-- Coupon invalide --}}
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <div class="bg-red-500 px-6 py-4 flex items-center gap-3">
                    <svg class="w-8 h-8 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-bold text-white text-lg">Coupon non valide</p>
                        <p class="text-red-100 text-sm">{{ $result['message'] }}</p>
                    </div>
                </div>
            </div>
            @endif
        @endif

        {{-- Résultat après utilisation --}}
        @if(session('use_success'))
        @php $used = session('use_success'); @endphp
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="bg-green-600 px-6 py-4">
                <p class="font-bold text-white text-lg">Transaction enregistrée !</p>
                <p class="text-green-100 text-sm">Le coupon a été marqué comme utilisé.</p>
            </div>
            <div class="p-6 text-sm text-gray-600">
                <p>Coupon <span class="font-mono font-bold text-gray-900">{{ $used->coupon_code }}</span> utilisé le {{ $used->used_at?->format('d/m/Y à H:i') }}.</p>
                <p class="mt-1">Valeur : <strong>{{ number_format($used->coupon_value, 0) }} GDS</strong></p>
            </div>
        </div>
        @endif

        {{-- Formulaire de recherche --}}
        <div class="bg-white rounded-lg shadow-xl p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Vérifier un coupon</h2>

            @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('coupon.verify') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code du coupon</label>
                        <input type="text" name="coupon_code" required maxlength="20"
                            placeholder="SCOL-XXXXXXXX"
                            value="{{ old('coupon_code') }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 uppercase font-mono tracking-wider"
                            style="text-transform: uppercase;">
                    </div>
                    <button type="submit"
                        class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                        Vérifier le coupon
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-indigo-200 text-xs">
            <a href="{{ route('home') }}" class="hover:text-white underline">← Retour à l'accueil KAYPA</a>
        </p>

    </div>
</body>
</html>
