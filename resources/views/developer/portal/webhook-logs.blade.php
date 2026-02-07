@extends('layouts.admin')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Breadcrumb & Title -->
    <div class="mb-8">
        <nav class="flex mb-4 text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('developer.portal.index') }}" class="inline-flex items-center hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        Developer Portal
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 md:ml-2 font-medium">Webhook Logs</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Delivery History</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Endpoint: <code class="bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded text-indigo-600 dark:text-indigo-400 font-mono">{{ $webhook->url }}</code></p>
    </div>

    <!-- Stats Row (Optional, for premium feel) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Deliveries</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $deliveries->total() }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Success Rate</p>
            @php
                $successCount = $deliveries->where('status', 'success')->count();
                $rate = $deliveries->count() > 0 ? round(($successCount / $deliveries->count()) * 100) : 100;
            @endphp
            <div class="flex items-baseline gap-2 mt-2">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $rate }}%</p>
                <span class="text-xs text-green-500 font-bold uppercase tracking-tight">Healthy</span>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active Subscription</p>
            <div class="mt-2 flex gap-1 flex-wrap">
                @foreach($webhook->events as $event)
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 uppercase tracking-tight">{{ $event }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50/50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Timestamp</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Event</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Code</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Attempts</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($deliveries as $delivery)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                            {{ $delivery->created_at->format('M d, H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-bold bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 uppercase tracking-wider border border-blue-100 dark:border-blue-800">
                                {{ $delivery->event_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($delivery->status === 'success')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                Success
                            </span>
                            @elseif($delivery->status === 'failed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                Failed
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                Retrying
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-mono {{ ($delivery->http_status_code >= 200 && $delivery->http_status_code < 300) ? 'text-green-600' : 'text-red-500' }}">
                            {{ $delivery->http_status_code ?? '---' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600 dark:text-gray-400 font-bold">
                            {{ $delivery->attempt_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" x-data="{ open: false }">
                            <button @click="open = true" class="text-indigo-600 hover:text-indigo-900 dark:hover:text-indigo-400 transition underline underline-offset-4 decoration-indigo-300">
                                View Data
                            </button>
                            
                            <!-- Detail Modal (Alpine) -->
                            <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
                                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden flex flex-col" @click.away="open = false">
                                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-700/50">
                                        <h3 class="font-bold text-gray-900 dark:text-white">Delivery Details</h3>
                                        <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div class="p-6 overflow-y-auto">
                                        <div class="space-y-6">
                                            <div>
                                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Request Payload</h4>
                                                <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs font-mono overflow-x-auto border border-gray-800 shadow-inner">{{ json_encode($delivery->payload, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Server Response</h4>
                                                <pre class="bg-gray-100 dark:bg-gray-900/50 text-gray-700 dark:text-gray-400 p-4 rounded-lg text-xs font-mono border border-gray-200 dark:border-gray-700">{{ $delivery->response_body ?: 'Empty response body' }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 text-right">
                                        <button @click="open = false" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm">Close</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400 italic">
                            No delivery logs found for this endpoint.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deliveries->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/30">
            {{ $deliveries->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
