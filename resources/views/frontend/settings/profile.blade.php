@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="shell px-4 md:px-6 py-8">
        <h1 class="text-2xl font-bold mb-6">Settings</h1>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar -->
            <aside class="w-full md:w-64 flex-shrink-0">
                <nav class="space-y-1">
                    <a href="{{ route('settings.profile') }}"
                        class="block px-3 py-2 text-sm font-medium rounded-md bg-gray-100 text-gray-900">Profile details</a>
                    <a href="{{ route('settings.account') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Account
                        settings</a>
                    <a href="{{ route('settings.postage') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Postage</a>
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
            <div class="flex-1 bg-white border border-gray-200 rounded-lg p-6">
                @if (session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div x-show="successMessage" x-transition
                    class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded"
                    style="display: none;">
                    <span x-text="successMessage"></span>
                </div>

                <form @submit.prevent="submitForm" enctype="multipart/form-data"
                    x-data="profileForm('{{ old('country', $user->getMeta('country')) }}', '{{ old('city', $user->getMeta('city')) }}')">
                    @csrf

                    <!-- Your photo -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-900">Your photo</h3>
                        </div>
                        <div class="flex items-center gap-4">
                            <img :src="avatarPreview || '{{ $user->avatar_url }}'" alt="{{ $user->username }}"
                                class="w-12 h-12 rounded-full object-cover">
                            <label
                                class="cursor-pointer bg-white border border-brand text-brand px-4 py-2 rounded text-sm font-medium hover:bg-gray-50">
                                Choose photo
                                <input type="file" name="avatar" class="hidden" accept="image/*"
                                    @change="handleAvatarChange">
                            </label>
                        </div>
                    </div>

                    <!-- Username -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-900">Username</h3>
                        </div>
                        <div class="flex items-center gap-4 w-1/2">
                            <input type="text" name="username" value="{{ old('username', $user->username) }}"
                                class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-brand focus:border-brand">
                        </div>
                    </div>

                    <!-- About you -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex flex-col md:flex-row md:justify-between">
                        <div class="mb-4 md:mb-0">
                            <h3 class="text-base font-medium text-gray-900">About you</h3>
                        </div>
                        <div class="w-full md:w-1/2">
                            <textarea name="about" rows="4"
                                class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-brand focus:border-brand"
                                placeholder="Tell us more about yourself and your style">{{ old('about', $user->getMeta('about')) }}</textarea>
                        </div>
                    </div>

                    <!-- My location -->
                    <div class="mb-8 pb-8 border-b border-gray-100">
                        <h3 class="text-sm text-gray-500 mb-4 uppercase tracking-wide">My location</h3>

                        <div class="flex items-center justify-between mb-4">
                            <label class="text-base font-medium text-gray-900">Country</label>
                            <div class="w-1/2 relative">
                                <input type="hidden" name="country" :value="selectedCountry">
                                <div class="relative">
                                    <input type="text" x-model="searchCountry" @focus="openCountry = true"
                                        @click.away="openCountry = false"
                                        class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-brand focus:border-brand"
                                        placeholder="Select a country">
                                    <div x-show="openCountry"
                                        class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                        <template x-for="country in filteredCountries" :key="country">
                                            <div @click="selectCountry(country)"
                                                class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm" x-text="country">
                                            </div>
                                        </template>
                                        <div x-show="filteredCountries.length === 0"
                                            class="px-4 py-2 text-gray-500 text-sm">No countries found</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <label class="text-base font-medium text-gray-900">Town/City</label>
                            <div class="w-1/2 relative">
                                <input type="hidden" name="city" :value="selectedCity">
                                <div class="relative">
                                    <input type="text" x-model="searchCity" @focus="openCity = true"
                                        @click.away="openCity = false" :disabled="!selectedCountry"
                                        class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-brand focus:border-brand disabled:bg-gray-100"
                                        placeholder="Select a city">
                                    <div x-show="openCity && selectedCountry"
                                        class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                        <template x-for="city in filteredCities" :key="city">
                                            <div @click="selectCity(city)"
                                                class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm" x-text="city">
                                            </div>
                                        </template>
                                        <div x-show="isLoadingCities" class="px-4 py-2 text-gray-500 text-sm">Loading
                                            cities...</div>
                                        <div x-show="!isLoadingCities && filteredCities.length === 0"
                                            class="px-4 py-2 text-gray-500 text-sm">No cities found</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="text-base font-medium text-gray-900">Show city in profile</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="show_city" value="1" class="sr-only peer" {{ old('show_city', $user->getMeta('show_city')) ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand">
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Language -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-900">Language</h3>
                        </div>
                        <div class="w-1/2">
                            <select name="language"
                                class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-brand focus:border-brand">
                                <option value="en" {{ old('language', $user->getMeta('language')) == 'en' ? 'selected' : '' }}>English, UK (English)</option>
                                <option value="fr" {{ old('language', $user->getMeta('language')) == 'fr' ? 'selected' : '' }}>French (Français)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-brand text-white px-6 py-2 rounded font-medium hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="isLoading" x-text="isLoading ? 'Updating...' : 'Update profile'">
                        </button>
                    </div>

                    <!-- Cropper Modal -->
                    <div x-show="showCropper" style="display: none;"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black opacity-75 p-4">
                        <div class="bg-white rounded-lg p-4 w-full max-w-lg">
                            <h3 class="text-lg font-bold mb-4">Crop Profile Picture</h3>

                            <div class="mb-4">
                                <div class="h-96 w-full bg-gray-100 rounded overflow-hidden">
                                    <img x-ref="cropImage" class="max-w-full" style="display: block; max-width: 100%;">
                                </div>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button type="button" @click="closeCropper"
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
                                    Cancel
                                </button>
                                <button type="button" @click="cropAndClose"
                                    class="px-4 py-2 text-white bg-brand rounded hover:opacity-90">
                                    Crop & Save
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
    <style>
        .cropper-container {
            max-height: 500px;
            width: 100%;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('profileForm', (initialCountry, initialCity) => ({
                countries: [],
                cities: [],
                selectedCountry: initialCountry || '',
                selectedCity: initialCity || '',
                searchCountry: initialCountry || '',
                searchCity: initialCity || '',
                openCountry: false,
                openCity: false,
                isLoadingCities: false,
                isLoading: false,
                successMessage: '',
                avatarPreview: null,

                // Cropper Variables
                showCropper: false,
                cropperInstance: null,
                croppedBlob: null,

                async init() {
                    // Fetch countries logic
                    try {
                        const response = await fetch('https://countriesnow.space/api/v0.1/countries/iso');
                        const data = await response.json();
                        if (!data.error) {
                            this.countries = data.data.map(c => c.name).sort();
                            if (this.selectedCountry) this.fetchCities(this.selectedCountry);
                        }
                    } catch (e) {
                        console.error('Failed to load countries', e);
                    }

                    this.$watch('searchCountry', (value) => {
                        if (value !== this.selectedCountry) this.selectedCountry = value;
                    });
                },

                // Filter logic
                get filteredCountries() {
                    if (this.searchCountry === '') return this.countries;
                    return this.countries.filter(c => c.toLowerCase().includes(this.searchCountry.toLowerCase()));
                },
                get filteredCities() {
                    if (this.searchCity === '') return this.cities;
                    return this.cities.filter(c => c.toLowerCase().includes(this.searchCity.toLowerCase()));
                },

                // Select logic
                selectCountry(name) {
                    this.selectedCountry = name;
                    this.searchCountry = name;
                    this.openCountry = false;
                    this.selectedCity = '';
                    this.searchCity = '';
                    this.cities = [];
                    this.fetchCities(name);
                },
                async fetchCities(countryName) {
                    this.isLoadingCities = true;
                    try {
                        const response = await fetch('https://countriesnow.space/api/v0.1/countries/cities', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ country: countryName })
                        });
                        const data = await response.json();
                        if (!data.error) this.cities = data.data.sort();
                        else this.cities = [];
                    } catch (e) { console.error('Failed to load cities', e); } finally { this.isLoadingCities = false; }
                },
                selectCity(name) {
                    this.selectedCity = name;
                    this.searchCity = name;
                    this.openCity = false;
                },

                // Avatar Logic
                handleAvatarChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.$refs.cropImage.src = e.target.result;
                            this.showCropper = true;

                            // Destroy old instance if exists
                            if (this.cropperInstance) {
                                this.cropperInstance.destroy();
                            }

                            // Initialize Cropper
                            this.$nextTick(() => {
                                this.cropperInstance = new Cropper(this.$refs.cropImage, {
                                    aspectRatio: 1,
                                    viewMode: 1,
                                    autoCropArea: 1,
                                });
                            });
                        };
                        reader.readAsDataURL(file);
                    }
                    // Reset input to allow re-selecting same file
                    event.target.value = '';
                },

                cropAndClose() {
                    if (this.cropperInstance) {
                        this.cropperInstance.getCroppedCanvas().toBlob((blob) => {
                            this.croppedBlob = blob;
                            this.avatarPreview = URL.createObjectURL(blob);
                            this.closeCropper();
                        }, 'image/jpeg');
                    }
                },

                closeCropper() {
                    this.showCropper = false;
                    if (this.cropperInstance) {
                        this.cropperInstance.destroy();
                        this.cropperInstance = null;
                    }
                },

                // Submit Logic
                async submitForm() {
                    this.isLoading = true;
                    this.successMessage = '';

                    const form = this.$el;
                    const formData = new FormData(form);

                    // Append cropped image if exists
                    if (this.croppedBlob) {
                        formData.set('avatar', this.croppedBlob, 'avatar.jpg');
                    }

                    try {
                        const response = await fetch("{{ route('settings.profile.update') }}", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.successMessage = data.message || 'Profile updated successfully.';
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        } else {
                            if (data.errors) {
                                let msg = 'Validation Error:\n';
                                for (const key in data.errors) msg += data.errors[key].join('\n') + '\n';
                                alert(msg);
                            } else {
                                alert(data.message || 'Something went wrong.');
                            }
                        }
                    } catch (error) {
                        console.error('Error submitting form:', error);
                        alert('An unexpected error occurred.');
                    } finally {
                        this.isLoading = false;
                    }
                }
            }));
        });
    </script>
@endpush