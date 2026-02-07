@extends('layouts.admin')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Table of Contents (Sticky Sidebar) -->
            <aside class="lg:w-1/4">
                <div class="sticky top-24 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                        <h6 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest flex items-center">
                            <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Explore API
                        </h6>
                    </div>
                    <nav class="p-2 space-y-1">
                        <a href="#intro" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all group">
                            Introduction
                        </a>
                        <div class="px-4 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-4">Core Concepts</div>
                        <a href="#authentication" class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-indigo-600 transition-all">Authentication</a>
                        <a href="#architecture" class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-indigo-600 transition-all">Architecture</a>
                        <a href="#filtering" class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-indigo-600 transition-all">Filtering & Sorting</a>
                        
                        <div class="px-4 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-4">Resources</div>
                        <a href="#devices" class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-indigo-600 transition-all">Devices</a>
                        <a href="#gps-data" class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-indigo-600 transition-all">GPS Data</a>
                        <a href="#alerts" class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-indigo-600 transition-all">Violations & Alerts</a>
                        
                        <div class="px-4 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-4">Push</div>
                        <a href="#webhooks" class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-indigo-600 transition-all">Webhooks SDK</a>
                    </nav>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="lg:w-3/4 pb-12">
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-8 md:p-12 prose dark:prose-invert max-w-none">
                        
                        <section id="intro" class="mb-16 scroll-mt-24">
                            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-6">Introduction</h1>
                            <p class="text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                                The **GPS Tracker API v2** is designed for high-frequency telemetry data access. It follows RESTful principles, using standard HTTP methods and JSON for all communication.
                            </p>
                            <div class="mt-8 p-6 bg-indigo-500/5 rounded-2xl border border-indigo-100 dark:border-indigo-900/30">
                                <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-1 block">Base URL</span>
                                <code class="text-indigo-700 dark:text-indigo-300 font-mono text-lg">{{ url('/') }}/api/v2</code>
                            </div>
                        </section>

                        <section id="authentication" class="mb-16 scroll-mt-24">
                            <h2 class="text-2xl font-bold flex items-center">
                                <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center mr-3 text-sm">Auth</span>
                                Authentication
                            </h2>
                            <p>Every request MUST include your organization's API credentials in the headers. These can be managed in the [Developer Portal]({{ route('developer.portal.index') }}).</p>
                            
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="text-left font-bold py-2">Header</th>
                                        <th class="text-left font-bold py-2">Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="py-3 font-mono text-indigo-600">X-API-Key</td>
                                        <td class="py-3">Your unique API key used to identify your organization.</td>
                                    </tr>
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="py-3 font-mono text-indigo-600">X-API-Secret</td>
                                        <td class="py-3">Used for authentication. Keep this token strictly confidential.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        <section id="architecture" class="mb-16 scroll-mt-24">
                            <h2 class="text-2xl font-bold flex items-center">
                                <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center mr-3 text-sm">Flow</span>
                                System Architecture
                            </h2>
                            <p>Our platform utilizes a decoupled, event-driven architecture to ensure high availability and scalability.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 my-8">
                                <div class="p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Ingestion</h4>
                                    <p class="text-sm m-0">ReactPHP-powered TCP server handling thousands of device connections concurrently.</p>
                                </div>
                                <div class="p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Processing</h4>
                                    <p class="text-sm m-0">Real-time geofencing and violation detection services analyzing every coordinate.</p>
                                </div>
                                <div class="p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Delivery</h4>
                                    <p class="text-sm m-0">RESTful API v2 and asynchronous Webhooks for third-party system integration.</p>
                                </div>
                            </div>
                        </section>

                        <section id="filtering" class="mb-16 scroll-mt-24">
                            <h2 class="text-2xl font-bold">Filtering & Sorting</h2>
                            <p>We use a standardized query syntax for all resource collections.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-6">
                                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                                    <h5 class="text-xs font-bold uppercase tracking-widest mb-2">Filtering</h5>
                                    <code class="text-xs font-mono text-pink-600">?filter[status]=active</code>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                                    <h5 class="text-xs font-bold uppercase tracking-widest mb-2">Sorting</h5>
                                    <code class="text-xs font-mono text-pink-600">?sort=-created_at</code>
                                </div>
                            </div>
                        </section>

                        <hr class="my-16 border-gray-100 dark:border-gray-700" />

                        <section id="devices" class="mb-16 scroll-mt-24">
                            <h2 class="text-3xl font-bold mb-8">Devices</h2>
                            
                            <div class="space-y-12">
                                <!-- GET /devices -->
                                <div>
                                    <div class="flex items-center gap-3 mb-4">
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold">GET</span>
                                        <h3 class="m-0 text-xl font-bold">List Devices</h3>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-400">Retrieve a list of all devices assigned to your workspace.</p>
                                    <div class="bg-gray-900 rounded-2xl overflow-hidden shadow-lg mt-4">
                                        <div class="px-4 py-2 bg-gray-800 flex items-center justify-between text-[10px] uppercase font-bold text-gray-400 tracking-widest">
                                            <span>Response Schema</span>
                                        </div>
                                        <pre class="p-6 m-0 text-gray-300 text-xs"><code>{
  "data": [
    {
      "id": 1,
      "imei": "358204000...",
      "name": "Heavy Truck A1",
      "status": "active",
      "last_seen": "2024-03-15T14:20:01Z"
    }
  ],
  "links": { "first": "...", "last": "..." },
  "meta": { "total": 12, "per_page": 20 }
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section id="gps-data" class="mb-16 scroll-mt-24">
                            <h2 class="text-3xl font-bold mb-8">GPS Data</h2>
                            
                            <div class="bg-gray-50 dark:bg-gray-900/50 p-8 rounded-3xl border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3 mb-6">
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold">GET</span>
                                    <h3 class="m-0 text-xl font-bold">Historical Route</h3>
                                    <span class="font-mono text-sm text-gray-500">/gps-data/{id}/history</span>
                                </div>
                                <p class="mb-6">Returns up to 500 GPS points for the specified device and timeframe.</p>
                                
                                <h4 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Query Parameters</h4>
                                <div class="space-y-3 mb-8">
                                    @foreach([
                                        ['name' => 'from', 'type' => 'iso8601', 'desc' => 'Filter points after this timestamp.'],
                                        ['name' => 'to', 'type' => 'iso8601', 'desc' => 'Filter points before this timestamp.'],
                                        ['name' => 'limit', 'type' => 'int', 'desc' => 'Max items per page (default 50, max 500).']
                                    ] as $param)
                                    <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                                        <div class="font-mono text-indigo-600 font-bold min-w-[80px]">{{ $param['name'] }}</div>
                                        <div>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter block mb-1">{{ $param['type'] }}</span>
                                            <p class="text-sm m-0 text-gray-600 dark:text-gray-400">{{ $param['desc'] }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </section>

                        <section id="alerts" class="mb-16 scroll-mt-24">
                            <h2 class="text-3xl font-bold mb-8">Violations & Alerts</h2>
                            <p>Real-time infractions triggered by device behavior (speeding, harsh braking, etc.)</p>
                            <div class="bg-indigo-50 dark:bg-indigo-900/10 p-6 rounded-2xl border border-indigo-100 dark:border-indigo-900/30">
                                <h5 class="text-indigo-800 dark:text-indigo-300 font-bold text-sm mb-2">Pro Tip: Use Webhooks!</h5>
                                <p class="text-sm m-0">Instead of polling the <code class="bg-white/50 dark:bg-gray-800 px-1 rounded">/violations</code> endpoint, we highly recommend subscribing to the <code class="bg-white/50 dark:bg-gray-800 px-1 rounded text-pink-600">alert.created</code> webhook event for real-time reactions.</p>
                            </div>
                        </section>

                        <section id="webhooks" class="mb-16 scroll-mt-24">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-16 h-1 bg-emerald-500 rounded-full"></div>
                                <h2 class="text-3xl font-bold m-0 tracking-tight">Webhooks</h2>
                            </div>
                            <p>We deliver events via HTTP POST requests to your configured endpoint with a JSON body.</p>
                            
                            <h4 class="text-lg font-bold mt-10 mb-4">Verification Example (PHP)</h4>
                            <div class="bg-gray-900 rounded-2xl p-6 shadow-2xl">
                                <pre class="m-0 text-green-400 font-mono text-xs leading-relaxed"><code>$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_GPS_SIGNATURE'];
$secret = 'your_webhook_secret';

$expected = hash_hmac('sha256', $payload, $secret);

if (hash_equals($expected, $signature)) {
    // Authenticated! Process event: json_decode($payload)
}</code></pre>
                            </div>
                        </section>

                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<style>
    html { scroll-behavior: smooth; }
    .prose code { 
        padding: 0.2em 0.4em;
        background-color: rgba(99, 102, 241, 0.05);
        border-radius: 4px;
        font-weight: 500;
    }
    .dark .prose code { background-color: rgba(99, 102, 241, 0.1); }
</style>
@endsection
