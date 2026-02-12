@extends('layouts.app')

@section('content')
    <main class="w-full">
        {{-- Hero Section (no changes) --}}
        <!-- Hero -->
        @guest
            <section class="relative mb-8 h-[350px] md:h-[450px] bg-cover bg-center"
                style="background-image:url('{{ asset('images/home/hero.png') }}');">
                <div class="shell px-4 md:px-6 h-full">
                    <div class="absolute top-1/2 -translate-y-1/2 bg-white p-6 md:p-8 rounded-lg shadow-lg w-[90%] max-w-sm">
                        <h1 class="text-[28px] md:text-[32px] leading-tight font-extrabold text-gray-800 mb-5">Ready to
                            declutter
                            your wardrobe?</h1>
                        <a href="#" class="block w-full mb-3 py-3 text-center text-white font-bold rounded"
                            style="background:var(--brand)">Sell now</a>
                        <a href="#" class="block w-full text-center hover:underline font-bold" style="color: var(--brand)">Learn
                            how it
                            works</a>
                    </div>
                </div>
            </section>
        @endguest

        {{-- Product Grid Section with Infinite Scroll --}}
        <section class="shell px-4 md:px-6 py-6 md:py-8">
            {{-- Add an ID to the grid container for our JavaScript to target --}}
            <div class="grid-container" id="product-grid">
                {{-- Include the partial view with the initial set of products --}}
                @include('layouts.partials._product_grid_items', ['products' => $products])
            </div>

            {{-- Add a loading indicator for when new products are being fetched --}}
            <div id="loading-indicator" style="display: none;" class="col-span-full text-center py-8">
                <p class="text-gray-600 font-semibold">Loading more products...</p>
            </div>
        </section>
    </main>
    <template id="skeleton-template">
        @include('layouts.partials._product_skeleton')
    </template>
@endsection

@section('after_body')

    <script>
        document.addEventListener('DOMContentLoaded', () => {


            // // ✨ --- LAZY LOADING SCRIPT --- ✨

            let page = 2; // The next page to be loaded
            let loading = false; // A flag to prevent multiple AJAX requests at once
            let noMoreProducts = false; // A flag to stop fetching when all products are loaded

            const loadingIndicator = document.getElementById('loading-indicator');
            const productGrid = document.getElementById('product-grid');

            // This function handles the actual fetching of new products
            // This function handles the actual fetching of new products
            const loadMoreProducts = () => {
                if (loading || noMoreProducts) return;
                loading = true;

                const skeletonTemplate = document.getElementById('skeleton-template');
                const productGrid = document.getElementById('product-grid');

                // 1. Create and show 5 skeleton placeholders immediately
                const skeletons = [];
                for (let i = 0; i < 5; i++) {
                    const skeletonNode = skeletonTemplate.content.cloneNode(true);
                    productGrid.appendChild(skeletonNode);
                    skeletons.push(productGrid.lastElementChild);
                }

                console.log('Fetching page', page);

                fetch(`?page=${page}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.text())
                    .then(html => {
                        if (html.trim().length === 0) {
                            noMoreProducts = true;
                            // Optional: Hide loading indicator or show "No more products"
                            return;
                        }

                        // 3. Append the real product data
                        productGrid.insertAdjacentHTML('beforeend', html);
                        page++;

                        // Re-run the image observer if it existed (removed to prevent error)
                        // if (typeof observeLazyImages === 'function') observeLazyImages();
                    })
                    .catch(error => console.error('Error loading more products:', error))
                    .finally(() => {
                        // 2. ALWAYS remove the skeleton placeholders
                        skeletons.forEach(skeleton => skeleton.remove());
                        loading = false;
                    });
            };

            // Listen for scroll events on the window
            window.addEventListener('scroll', () => {
                const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
                const scrolled = window.scrollY;

                // Check if the user has scrolled to 500px from the bottom
                if (scrollableHeight - scrolled <= 500) {
                    loadMoreProducts();
                }
            });

            // Handle Like Button Click using Event Delegation
            document.addEventListener('click', (e) => {
                const button = e.target.closest('.fav-badge');
                if (!button) return;

                e.preventDefault();
                e.stopPropagation();

                const url = button.dataset.url;
                if (!url) return;

                const svg = button.querySelector('svg');
                const countSpan = button.querySelector('span');

                // Optimistic UI update
                const isLiked = svg.classList.contains('!text-red-500');
                if (isLiked) {
                    svg.classList.remove('!text-red-500', '!fill-current', '!stroke-current');
                    let count = parseInt(countSpan.textContent) || 0;
                    countSpan.textContent = Math.max(0, count - 1);
                } else {
                    svg.classList.add('!text-red-500', '!fill-current', '!stroke-current');
                    let count = parseInt(countSpan.textContent) || 0;
                    countSpan.textContent = count + 1;
                }

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        if (response.status === 401) {
                            Livewire.dispatch('open-login-popup');
                            // Revert optimistic update since action failed
                            if (isLiked) {
                                svg.classList.add('!text-red-500', '!fill-current', '!stroke-current');
                                let count = parseInt(countSpan.textContent) || 0;
                                countSpan.textContent = count + 1;
                            } else {
                                svg.classList.remove('!text-red-500', '!fill-current', '!stroke-current');
                                let count = parseInt(countSpan.textContent) || 0;
                                countSpan.textContent = Math.max(0, count - 1);
                            }
                            return;
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data) {
                            // Update with actual server state if needed
                            if (data.liked) {
                                svg.classList.add('!text-red-500', '!fill-current', '!stroke-current');
                            } else {
                                svg.classList.remove('!text-red-500', '!fill-current', '!stroke-current');
                            }
                            countSpan.textContent = data.count;
                        }
                    })
                    .catch(error => {
                        console.error('Error toggling favorite:', error);
                        // Revert optimistic update on error
                        if (isLiked) {
                            svg.classList.add('!text-red-500', '!fill-current', '!stroke-current');
                            let count = parseInt(countSpan.textContent) || 0;
                            countSpan.textContent = count + 1;
                        } else {
                            svg.classList.remove('!text-red-500', '!fill-current', '!stroke-current');
                            let count = parseInt(countSpan.textContent) || 0;
                            countSpan.textContent = Math.max(0, count - 1);
                        }
                    });
            });
        });
    </script>
@endsection