@extends('layouts.admin')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900 min-h-screen" x-data="{ showGenerateKey: false, showAddWebhook: false }">
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Developer Portal</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manage your API keys, webhooks, and explore documentation for third-party integrations.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('developer.portal.docs') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Documentation
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-r shadow-sm flex items-center justify-between" x-data="{ show: true }" x-show="show">
        <div class="flex items-center">
            <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <span class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</span>
        </div>
        <button @click="show = false" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- API Keys Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/50 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    API Keys
                </h3>
                <button @click="$dispatch('open-modal', 'generate-key')" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    New Key
                </button>
            </div>
            <div class="p-6 flex-grow">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Key</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Used</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($apiKeys as $key)
                            <tr>
                                <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $key->name }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm font-mono text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 rounded px-2 py-1 mx-1">{{ $key->key }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-right text-sm">
                                    <form action="{{ route('developer.portal.revoke-key', $key->id) }}" method="POST" onsubmit="return confirm('Revoke this key? It cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:text-red-900 dark:hover:text-red-400 font-semibold transition duration-150">Revoke</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400 italic">No API keys found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Webhooks Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/50 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Webhooks
                </h3>
                <button @click="$dispatch('open-modal', 'add-webhook')" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Add Webhook
                </button>
            </div>
            <div class="p-6 flex-grow">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Endpoint</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Events</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($webhooks as $webhook)
                            <tr>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white max-w-[120px] truncate" title="{{ $webhook->url }}">
                                        {{ $webhook->url }}
                                    </div>
                                </td>
                                <td class="px-3 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($webhook->events as $event)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 uppercase tracking-tight">{{ $event }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $webhook->active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $webhook->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('developer.portal.webhook-logs', $webhook->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:hover:text-indigo-400 transition">Logs</a>
                                    <button class="text-red-600 hover:text-red-900 dark:hover:text-red-400 transition">Delete</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400 italic">No webhooks configured.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Integrations Footer -->
    <div class="mt-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-xl overflow-hidden p-10 text-center text-white">
        <h2 class="text-2xl font-bold mb-4">Ready for Deep Integration?</h2>
        <p class="text-indigo-100 mb-8 max-w-2xl mx-auto">Download our official SDKs or explore our interactive API documentation to start building enterprise-grade applications on top of our GPS infrastructure.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('developer.portal.sdk-download', 'php') }}" class="bg-white/10 backdrop-blur-md rounded-xl p-4 min-w-[150px] border border-white/20 hover:bg-white/20 transition cursor-pointer no-underline text-white block">
                <span class="block text-2xl font-bold mb-1">PHP</span>
                <span class="text-xs text-indigo-100 uppercase tracking-widest">Composer SDK</span>
            </a>
            <a href="{{ route('developer.portal.sdk-download', 'javascript') }}" class="bg-white/10 backdrop-blur-md rounded-xl p-4 min-w-[150px] border border-white/20 hover:bg-white/20 transition cursor-pointer no-underline text-white block">
                <span class="block text-2xl font-bold mb-1">JS</span>
                <span class="text-xs text-indigo-100 uppercase tracking-widest">NPM Library</span>
            </a>
            <a href="{{ route('developer.portal.sdk-download', 'python') }}" class="bg-white/10 backdrop-blur-md rounded-xl p-4 min-w-[150px] border border-white/20 hover:bg-white/20 transition cursor-pointer no-underline text-white block">
                <span class="block text-2xl font-bold mb-1">Python</span>
                <span class="text-xs text-indigo-100 uppercase tracking-widest">PyPI Package</span>
            </a>
        </div>
    </div>

    <!-- X-Modal Component Integration -->
    <x-modal name="generate-key" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Generate New API Key</h2>
            <form action="{{ route('developer.portal.generate-key') }}" method="POST">
                @csrf
                <div>
                    <x-input-label for="key_name" value="Application Name" />
                    <x-text-input id="key_name" name="name" type="text" class="mt-1 block w-full" placeholder="e.g., My CRM Integration" required />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                    <x-primary-button>Generate Key</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    <x-modal name="add-webhook" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Add Webhook Endpoint</h2>
            <form action="{{ route('developer.portal.store-webhook') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <x-input-label for="webhook_url" value="Webhook URL" />
                        <x-text-input id="webhook_url" name="url" type="url" class="mt-1 block w-full" placeholder="https://your-app.com/api/webhooks" required />
                    </div>
                    <div>
                        <span class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-2">Subscribe to Events</span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['device.online', 'device.offline', 'geofence.entered', 'geofence.exited', 'alert.created'] as $event)
                            <label class="inline-flex items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <input type="checkbox" name="events[]" value="{{ $event }}" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" checked>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400 font-semibold uppercase tracking-tighter">{{ str_replace('.', ' ', $event) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                    <x-primary-button>Add Endpoint</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</div>
@endsection
