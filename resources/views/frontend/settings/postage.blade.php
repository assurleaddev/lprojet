@extends('layouts.app')

@section('title', 'Postage Settings')

@section('content')
    <div class="shell px-4 md:px-6 py-8">
        <h1 class="text-2xl font-bold mb-6">Settings</h1>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar -->
            <aside class="w-full md:w-64 flex-shrink-0">
                <nav class="space-y-1">
                    <a href="{{ route('settings.profile') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Profile details</a>
                    <a href="{{ route('settings.account') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Account settings</a>
                    <a href="{{ route('settings.postage') }}"
                        class="block px-3 py-2 text-sm font-medium rounded-md bg-gray-100 text-gray-900">Postage</a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Payments</a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Bundle
                        discounts</a>
                    <a href="{{ route('settings.notifications') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Notifications</a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Privacy
                        settings</a>
                    <a href="{{ route('settings.security') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Security</a>
                </nav>
            </aside>

            <!-- Content -->
            <div class="flex-1 bg-white border border-gray-200 rounded-lg p-6" x-data="addressManager()">
                @if (session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
                        <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('settings.postage.update') }}" method="POST">
                    @csrf
                    <!-- Your address -->
                    <div class="mb-8 pb-8 border-b border-gray-100">
                        <h3 class="text-base font-medium text-gray-900 mb-4">Your address</h3>

                        @if($addresses->isEmpty())
                            <button type="button" @click="openModal('add')"
                                class="w-full border border-gray-300 rounded-md py-3 px-4 flex items-center justify-between hover:bg-gray-50">
                                <span class="font-medium text-gray-700">Add your address</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                    </path>
                                </svg>
                            </button>
                        @else
                            @foreach($addresses as $address)
                                <div class="border border-gray-300 rounded-md p-4 mb-4 flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $address->full_name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $address->address_line_1 }}</p>
                                        @if($address->address_line_2)
                                        <p class="text-sm text-gray-600">{{ $address->address_line_2 }}</p> @endif
                                        <p class="text-sm text-gray-600">{{ $address->postcode }}, {{ $address->city }}</p>
                                        <p class="text-xs text-gray-400">{{ $address->country }}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <!-- Edit -->
                                        <button type="button" @click="openModal('edit', {{ json_encode($address) }})"
                                            class="text-gray-400 hover:text-brand p-1">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                </path>
                                            </svg>
                                        </button>
                                        <!-- Delete -->
                                        <button type="button"
                                            @click="confirmDelete('{{ route('settings.address.delete', $address->id) }}')"
                                            class="text-gray-400 hover:text-red-600 p-1">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                            <button type="button" @click="openModal('add')"
                                class="text-brand font-medium text-sm hover:underline">
                                + Add another address
                            </button>
                        @endif

                        <p class="text-xs text-gray-500 mt-2">Where couriers will collect or deliver orders, and what we'll
                            use to process returns.</p>
                    </div>

                    <!-- Info Box & Shipping Options (unchanged but re-included due to large chunk replacement) -->
                    <!-- Info Box -->
                    <div class="mb-8 bg-blue-50 border border-blue-100 rounded-md p-4 flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-blue-800">Disabling shipping options may reduce sales. If a member can only
                            buy from you with a disabled option, we may still offer it. <a href="#" class="underline">Learn
                                more about disabled options</a>.</p>
                    </div>

                    <!-- Shipping as a seller -->
                    <div class="mb-8">
                        <h3 class="text-base font-medium text-gray-900 mb-2">Shipping as a seller</h3>
                        <p class="text-sm text-gray-500 mb-6">Choose which options you'd like to use for each shipping type.
                        </p>

                        <!-- From your address (Home Pickup) -->
                        <div class="border border-gray-200 rounded-md mb-4" x-data="{ open: true }">
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-t-md">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                        </path>
                                    </svg>
                                    <div class="text-left">
                                        <span class="block font-medium text-gray-900">From your address</span>
                                        <span class="block text-xs text-gray-500">A courier collects the order from
                                            you.</span>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform"
                                    :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" class="border-t border-gray-200 divide-y divide-gray-100">
                                @foreach($shippingOptions->where('type', 'home_pickup') as $option)
                                    <div class="p-4 flex items-center justify-between">
                                        <div class="flex items-start gap-3">
                                            <div class="w-8 h-5 {{ $option->icon_class }} rounded-sm flex-shrink-0 mt-1"></div>
                                            <div>
                                                <span class="block font-medium text-gray-900">{{ $option->label }}</span>
                                                <span class="block text-xs text-gray-500">{!! $option->description !!}</span>
                                            </div>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="{{ $option->key }}" value="1" class="sr-only peer" {{ $user->getMeta($option->key) ? 'checked' : '' }}>
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand">
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- From a drop-off point -->
                        <div class="border border-gray-200 rounded-md" x-data="{ open: true }">
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-t-md">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <div class="text-left">
                                        <span class="block font-medium text-gray-900">From a drop-off point</span>
                                        <span class="block text-xs text-gray-500">You take the order to a location like a
                                            locker or parcel shop.</span>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform"
                                    :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open" class="border-t border-gray-200 divide-y divide-gray-100">
                                @foreach($shippingOptions->where('type', 'drop_off') as $option)
                                    <div class="p-4 flex items-center justify-between">
                                        <div class="flex items-start gap-3">
                                            <div class="w-8 h-5 {{ $option->icon_class }} rounded-sm flex-shrink-0 mt-1"></div>
                                            <div>
                                                <span class="block font-medium text-gray-900">{{ $option->label }}</span>
                                                <span class="block text-xs text-gray-500">{!! $option->description !!}</span>
                                            </div>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="{{ $option->key }}" value="1" class="sr-only peer" {{ $user->getMeta($option->key) ? 'checked' : '' }}>
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600">
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-brand text-white px-6 py-2 rounded font-medium hover:opacity-90">Save</button>
                    </div>
                </form>

                <!-- Add/Edit Address Modal -->
                <div x-cloak x-show="showAddressModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog"
                    aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="showAddressModal" class="fixed inset-0 transition-opacity" aria-hidden="true"
                            @click="closeModal" style="background-color: rgba(107, 114, 128, 0.75);"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div x-show="showAddressModal"
                            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-50">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900"
                                        x-text="isEditMode ? 'Edit address' : 'Add address'"></h3>
                                    <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <form :action="formAction" method="POST">
                                    @csrf
                                    <template x-if="isEditMode">
                                        <input type="hidden" name="_method" value="PUT">
                                    </template>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                            <input type="hidden" name="country" :value="formData.country">
                                            <div class="relative">
                                                <input type="text" x-model="countrySearch"
                                                    @focus="openCountryDropdown = true"
                                                    @click.away="openCountryDropdown = false"
                                                    class="w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:border-brand focus:ring focus:ring-red-100 focus:ring-opacity-50"
                                                    placeholder="Select a country">
                                                <div x-show="openCountryDropdown"
                                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                                    <template x-for="country in filteredCountriesList" :key="country">
                                                        <div @click="selectCountryOption(country)"
                                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                                                            x-text="country">
                                                        </div>
                                                    </template>
                                                    <div x-show="filteredCountriesList.length === 0"
                                                        class="px-4 py-2 text-gray-500 text-sm">No countries found</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                                            <input type="text" name="full_name" x-model="formData.full_name"
                                                class="w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:border-brand focus:ring focus:ring-red-100 focus:ring-opacity-50">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Address line
                                                1</label>
                                            <input type="text" name="address_line_1" x-model="formData.address_line_1"
                                                class="w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:border-brand focus:ring focus:ring-red-100 focus:ring-opacity-50">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Address line 2
                                                (optional)</label>
                                            <input type="text" name="address_line_2" x-model="formData.address_line_2"
                                                class="w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:border-brand focus:ring focus:ring-red-100 focus:ring-opacity-50">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                            <input type="hidden" name="city" :value="formData.city">
                                            <div class="relative">
                                                <input type="text" x-model="citySearch" @focus="openCityDropdown = true"
                                                    @click.away="openCityDropdown = false" :disabled="!formData.country"
                                                    class="w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:border-brand focus:ring focus:ring-red-100 focus:ring-opacity-50 disabled:bg-gray-100"
                                                    placeholder="Select a city">
                                                <div x-show="openCityDropdown && formData.country"
                                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                                    <template x-for="city in filteredCitiesList" :key="city">
                                                        <div @click="selectCityOption(city)"
                                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                                                            x-text="city">
                                                        </div>
                                                    </template>
                                                    <div x-show="isLoadingCities" class="px-4 py-2 text-gray-500 text-sm">
                                                        Loading cities...</div>
                                                    <div x-show="!isLoadingCities && filteredCitiesList.length === 0"
                                                        class="px-4 py-2 text-gray-500 text-sm">No cities found</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Postcode</label>
                                            <input type="text" name="postcode" x-model="formData.postcode"
                                                class="w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:border-brand focus:ring focus:ring-red-100 focus:ring-opacity-50">
                                        </div>
                                    </div>
                                    <div class="mt-5 sm:mt-6 flex gap-3">
                                        <button type="submit"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-brand text-base font-medium text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand sm:text-sm">
                                            Save address
                                        </button>
                                        <button type="button" @click="closeModal"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Form -->
                <form x-ref="deleteForm" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            <script>
                function addressManager() {
                    return {
                        showAddressModal: false,
                        isEditMode: false,
                        formAction: "{{ route('settings.address.store') }}",
                        formData: {
                            id: null,
                            country: '',
                            full_name: '',
                            address_line_1: '',
                            address_line_2: '',
                            city: '',
                            postcode: ''
                        },
                        // Country dropdown
                        countriesList: [],
                        countrySearch: '',
                        openCountryDropdown: false,

                        // City dropdown
                        citiesList: [],
                        citySearch: '',
                        openCityDropdown: false,
                        isLoadingCities: false,

                        async init() {
                            // Fetch countries from API
                            try {
                                const response = await fetch('https://countriesnow.space/api/v0.1/countries/iso');
                                const data = await response.json();
                                if (!data.error) {
                                    this.countriesList = data.data.map(c => c.name).sort();
                                }
                            } catch (e) {
                                console.error('Failed to load countries', e);
                            }

                            // Watch for country changes to fetch cities
                            this.$watch('formData.country', (newCountry, oldCountry) => {
                                if (newCountry && newCountry !== oldCountry) {
                                    this.fetchCities(newCountry);
                                    // Reset city when country changes
                                    if (oldCountry) {
                                        this.formData.city = '';
                                        this.citySearch = '';
                                    }
                                }
                            });
                        },

                        get filteredCountriesList() {
                            if (this.countrySearch === '') return this.countriesList;
                            return this.countriesList.filter(c => c.toLowerCase().includes(this.countrySearch.toLowerCase()));
                        },

                        get filteredCitiesList() {
                            if (this.citySearch === '') return this.citiesList;
                            return this.citiesList.filter(c => c.toLowerCase().includes(this.citySearch.toLowerCase()));
                        },

                        selectCountryOption(country) {
                            this.formData.country = country;
                            this.countrySearch = country;
                            this.openCountryDropdown = false;
                        },

                        selectCityOption(city) {
                            this.formData.city = city;
                            this.citySearch = city;
                            this.openCityDropdown = false;
                        },

                        async fetchCities(countryName) {
                            this.isLoadingCities = true;
                            this.citiesList = [];
                            try {
                                const response = await fetch('https://countriesnow.space/api/v0.1/countries/cities', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ country: countryName })
                                });
                                const data = await response.json();
                                if (!data.error) {
                                    this.citiesList = data.data.sort();
                                } else {
                                    this.citiesList = [];
                                }
                            } catch (e) {
                                console.error('Failed to load cities', e);
                                this.citiesList = [];
                            } finally {
                                this.isLoadingCities = false;
                            }
                        },

                        openModal(mode, address = null) {
                            this.isEditMode = mode === 'edit';
                            this.showAddressModal = true;

                            if (this.isEditMode && address) {
                                this.formData = { ...address };
                                this.countrySearch = address.country || '';
                                this.citySearch = address.city || '';
                                // Fetch cities for the selected country
                                if (address.country) {
                                    this.fetchCities(address.country);
                                }
                                // Bind update route
                                this.formAction = "{{ url('settings/address') }}/" + address.id;
                            } else {
                                // Reset
                                this.formData = {
                                    id: null,
                                    country: '',
                                    full_name: '',
                                    address_line_1: '',
                                    address_line_2: '',
                                    city: '',
                                    postcode: ''
                                };
                                this.countrySearch = '';
                                this.citySearch = '';
                                this.citiesList = [];
                                this.formAction = "{{ route('settings.address.store') }}";
                            }
                        },
                        closeModal() {
                            this.showAddressModal = false;
                            this.openCountryDropdown = false;
                            this.openCityDropdown = false;
                        },
                        confirmDelete(url) {
                            if (confirm('Are you sure you want to delete this address?')) {
                                this.$refs.deleteForm.action = url;
                                this.$refs.deleteForm.submit();
                            }
                        }
                    }
                }
            </script>
        </div>
    </div>
@endsection