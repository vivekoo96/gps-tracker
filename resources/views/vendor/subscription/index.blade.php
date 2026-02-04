<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Subscription Management') }}
        </h2>
    </x-slot>

    @push('scripts')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @endpush

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Current Plan -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Current Subscription</h3>
                    <div class="flex items-center justify-between bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg border border-indigo-200 dark:border-indigo-800">
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-bold">Plan Name</p>
                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $currentPlan->name ?? 'Free Trial' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-bold">Status</p>
                            <p class="text-lg font-bold {{ $vendor->status === 'active' ? 'text-green-500' : 'text-red-500' }}">
                                {{ ucfirst($vendor->status) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-bold">Expires On</p>
                            <p class="text-lg font-mono">
                                {{ $vendor->subscription_expires_at ? $vendor->subscription_expires_at->format('d M, Y') : 'Lifetime' }}
                            </p>
                        </div>
                        @if($vendor->subscription_expires_at && $vendor->subscription_expires_at->diffInDays(now()) < 7)
                            <div class="bg-red-100 text-red-800 px-3 py-1 rounded text-sm font-bold">Expiring Soon</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Available Plans -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($availablePlans as $plan)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-700 overflow-hidden relative transform hover:-translate-y-1 transition duration-300">
                         @if($currentPlan && $currentPlan->id === $plan->id)
                            <div class="absolute top-0 right-0 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">Current</div>
                        @endif
                        <div class="p-6">
                            <h4 class="text-xl font-bold text-white mb-2">{{ $plan->name }}</h4>
                            <p class="text-gray-400 text-sm mb-4 min-h-[40px]">{{ $plan->description }}</p>
                            <div class="flex items-baseline mb-6">
                                <span class="text-3xl font-bold text-white">₹{{ $plan->price }}</span>
                                <span class="text-gray-500 ml-1">/ {{ $plan->duration_days }} days</span>
                            </div>
                            
                            <ul class="text-sm text-gray-400 mb-6 space-y-2">
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Up to {{ $plan->max_devices }} Devices
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Up to {{ $plan->max_users }} Users
                                </li>
                                @if(is_array($plan->features))
                                    @foreach($plan->features as $feature)
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                @endif
                            </ul>

                            <button onclick="initiatePayment({{ $plan->id }})" 
                                class="w-full py-2 px-4 rounded-lg font-bold transition {{ $currentPlan && $currentPlan->id === $plan->id ? 'bg-gray-700 text-gray-500 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/50' }}"
                                {{ $currentPlan && $currentPlan->id === $plan->id ? 'disabled' : '' }}>
                                {{ $currentPlan && $currentPlan->id === $plan->id ? 'Active Plan' : 'Upgrade Now' }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Transaction History -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-white mb-4">Payment History</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Plan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Transaction ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @forelse($transactions as $txn)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $txn->created_at->format('d M Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $txn->plan->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">₹{{ $txn->amount }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-400">{{ $txn->razorpay_payment_id ?? 'Pending' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $txn->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($txn->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $transactions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Razorpay Logic -->
    <script>
        function initiatePayment(planId) {
            fetch("{{ route('vendor.subscription.upgrade') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ plan_id: planId })
            })
            .then(response => response.json())
            .then(data => {
                if(data.error) {
                    alert(data.error);
                    return;
                }

                var options = {
                    "key": data.key,
                    "amount": data.amount,
                    "currency": data.currency,
                    "name": "{{ config('app.name') }}",
                    "description": "Subscription Upgrade",
                    "order_id": data.order_id,
                    "handler": function (response){
                        verifyPayment(response);
                    },
                    "prefill": {
                        "name": "{{ auth()->user()->name }}",
                        "email": "{{ auth()->user()->email }}",
                        "contact": "{{ auth()->user()->vendor->phone }}"
                    },
                    "theme": {
                        "color": "#4f46e5"
                    }
                };
                var rzp1 = new Razorpay(options);
                rzp1.open();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Something went wrong!');
            });
        }

        function verifyPayment(response) {
            fetch("{{ route('vendor.subscription.verify') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_signature: response.razorpay_signature
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Payment Verification Failed');
                }
            });
        }
    </script>
</x-app-layout>
