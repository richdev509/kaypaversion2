<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                📋 Détails de l'activité #{{ $log->id }}
            </h2>
            <a href="{{ route('activity-logs.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">

                <!-- En-tête -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">
                        {{ $log->description }}
                    </h3>
                    <p class="text-sm text-blue-100 mt-1">
                        {{ $log->created_at->format('d/m/Y à H:i:s') }}
                    </p>
                </div>

                <!-- Corps -->
                <div class="p-6 space-y-6">

                    <!-- Informations principales -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Type d'action -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                ⚡ Type d'action
                            </label>
                            @php
                                $colors = [
                                    'create' => 'bg-green-100 text-green-800',
                                    'update' => 'bg-blue-100 text-blue-800',
                                    'delete' => 'bg-red-100 text-red-800',
                                    'login' => 'bg-purple-100 text-purple-800',
                                    'logout' => 'bg-gray-100 text-gray-800',
                                    'access' => 'bg-yellow-100 text-yellow-800',
                                ];
                                $color = $colors[$log->action_type] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-block px-3 py-2 text-sm font-semibold rounded-lg {{ $color }}">
                                {{ ucfirst($log->action_type) }}
                            </span>
                        </div>

                        <!-- Utilisateur -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                👤 Utilisateur
                            </label>
                            @if($log->user)
                                <div class="text-gray-900 dark:text-gray-100">
                                    <div class="font-semibold">{{ $log->user->name }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $log->user->email }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Rôle: {{ ucfirst($log->user->role) }}</div>
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400 italic">Utilisateur supprimé</p>
                            @endif
                        </div>

                        <!-- Modèle concerné -->
                        @if($log->model_type)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                📦 Modèle concerné
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                <div class="font-semibold">{{ $log->model_type }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">ID: {{ $log->model_id }}</div>
                            </div>
                        </div>
                        @endif

                        <!-- IP -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                🌐 Adresse IP
                            </label>
                            <p class="text-gray-900 dark:text-gray-100 font-mono">{{ $log->ip_address ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- User Agent -->
                    @if($log->user_agent)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            💻 Navigateur/Appareil
                        </label>
                        <p class="text-sm text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 p-3 rounded-lg font-mono break-all">
                            {{ $log->user_agent }}
                        </p>
                    </div>
                    @endif

                    <!-- Raison -->
                    @if($log->reason)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            📝 Raison
                        </label>
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <p class="text-gray-900 dark:text-gray-100">{{ $log->reason }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Changements -->
                    @if($log->changes && count($log->changes) > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            🔄 Changements effectués
                        </label>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            @if(isset($log->changes['before']) && isset($log->changes['after']))
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Avant -->
                                    <div>
                                        <h4 class="text-sm font-semibold text-red-600 dark:text-red-400 mb-2">❌ Avant</h4>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-red-200 dark:border-red-800">
                                            <pre class="text-xs text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ json_encode($log->changes['before'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </div>

                                    <!-- Après -->
                                    <div>
                                        <h4 class="text-sm font-semibold text-green-600 dark:text-green-400 mb-2">✅ Après</h4>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-green-200 dark:border-green-800">
                                            <pre class="text-xs text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ json_encode($log->changes['after'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <pre class="text-xs text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Description complète -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            📄 Description complète
                        </label>
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <p class="text-gray-900 dark:text-gray-100">{{ $log->description }}</p>
                        </div>
                    </div>

                    <!-- Métadonnées -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Créé le:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-semibold ml-2">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Il y a:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-semibold ml-2">
                                    {{ $log->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">ID Log:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-semibold ml-2 font-mono">
                                    #{{ $log->id }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
