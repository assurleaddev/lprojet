<div>
    <div class="shell px-4 md:px-6 py-8">
        <h1 class="text-2xl font-bold mb-6">Settings</h1>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar -->
            @include('frontend.settings.partials._sidebar')

            <!-- Content -->
            <div class="flex-1 bg-white border border-gray-200 rounded-lg p-6">
                <div class="mb-6 pb-5 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-1">Bundle discounts</h2>
                    <p class="text-sm text-gray-500">Encourage buyers to purchase multiple items by offering
                        automatic discounts.</p>
                </div>

                <!-- Tier Cards -->
                <div class="space-y-4">

                    {{-- Tier: 2 items --}}
                    <div
                        class="flex items-center justify-between p-4 rounded-xl border {{ $tier2Enabled ? 'border-red-200 bg-red-50/40' : 'border-gray-200 bg-gray-50' }} transition-colors">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 rounded-full flex items-center justify-center {{ $tier2Enabled ? 'bg-red-100 text-red-600' : 'bg-gray-200 text-gray-500' }}">
                                <span class="text-sm font-bold">2+</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">2 or more items</p>
                                <p class="text-xs text-gray-500">Discount when buyer bundles 2+ items</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <select wire:model.live="tier2Percentage"
                                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-semibold focus:ring-1 focus:ring-red-500 focus:border-red-500 {{ !$tier2Enabled ? 'opacity-40 pointer-events-none' : '' }}">
                                @foreach($percentageOptions as $pct)
                                    <option value="{{ $pct }}">{{ $pct }}% off</option>
                                @endforeach
                            </select>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="tier2Enabled" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500">
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Tier: 3 items --}}
                    <div
                        class="flex items-center justify-between p-4 rounded-xl border {{ $tier3Enabled ? 'border-red-200 bg-red-50/40' : 'border-gray-200 bg-gray-50' }} transition-colors">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 rounded-full flex items-center justify-center {{ $tier3Enabled ? 'bg-red-100 text-red-600' : 'bg-gray-200 text-gray-500' }}">
                                <span class="text-sm font-bold">3+</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">3 or more items</p>
                                <p class="text-xs text-gray-500">Discount when buyer bundles 3+ items</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <select wire:model.live="tier3Percentage"
                                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-semibold focus:ring-1 focus:ring-red-500 focus:border-red-500 {{ !$tier3Enabled ? 'opacity-40 pointer-events-none' : '' }}">
                                @foreach($percentageOptions as $pct)
                                    <option value="{{ $pct }}">{{ $pct }}% off</option>
                                @endforeach
                            </select>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="tier3Enabled" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500">
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Tier: 5 items --}}
                    <div
                        class="flex items-center justify-between p-4 rounded-xl border {{ $tier5Enabled ? 'border-red-200 bg-red-50/40' : 'border-gray-200 bg-gray-50' }} transition-colors">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 rounded-full flex items-center justify-center {{ $tier5Enabled ? 'bg-red-100 text-red-600' : 'bg-gray-200 text-gray-500' }}">
                                <span class="text-sm font-bold">5+</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">5 or more items</p>
                                <p class="text-xs text-gray-500">Discount when buyer bundles 5+ items</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <select wire:model.live="tier5Percentage"
                                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-semibold focus:ring-1 focus:ring-red-500 focus:border-red-500 {{ !$tier5Enabled ? 'opacity-40 pointer-events-none' : '' }}">
                                @foreach($percentageOptions as $pct)
                                    <option value="{{ $pct }}">{{ $pct }}% off</option>
                                @endforeach
                            </select>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="tier5Enabled" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="mt-6">
                    <button wire:click="save"
                        class="w-full sm:w-auto px-8 py-2.5 text-white text-sm font-bold rounded-lg shadow-sm hover:opacity-90 transition"
                        style="background-color: var(--brand)">
                        <span wire:loading wire:target="save" class="animate-pulse">Saving...</span>
                        <span wire:loading.remove wire:target="save">Save changes</span>
                    </button>
                </div>

                {{-- Info Box --}}
                <div class="mt-8 p-4 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mt-0.5 shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-xs text-blue-800 space-y-1">
                        <p class="font-bold">How it works:</p>
                        <ul class="list-disc list-inside">
                            <li>Discounts are automatically applied when a buyer creates a bundle request.</li>
                            <li>The highest applicable tier based on item count is chosen.</li>
                            <li>A bundle badge will appear on your listings to attract buyers.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>