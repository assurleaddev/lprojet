@extends('layouts.app')

@section('content')
    <main class="w-full">
        {{-- Hero Section (no changes) --}}
        <!-- Hero -->
        <section class="relative mb-8 h-[350px] md:h-[450px] bg-cover bg-center"
            style="background-image:url('{{ asset('images/home/hero.png') }}');">
            <div class="shell px-4 md:px-6 h-full">
                <div class="absolute top-1/2 -translate-y-1/2 bg-white p-6 md:p-8 rounded-lg shadow-lg w-[90%] max-w-sm">
                    <h1 class="text-[28px] md:text-[32px] leading-tight font-extrabold text-gray-800 mb-5">Ready to declutter
                        your wardrobe?</h1>
                    <a href="#" class="block w-full mb-3 py-3 text-center text-white font-bold rounded"
                        style="background:var(--brand)">Sell now</a>
                    <a href="#" class="block w-full text-center text-teal-600 hover:underline font-bold">Learn how it
                        works</a>
                </div>
            </div>
        </section>

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
            const navLinks = document.querySelectorAll('.nav-link[data-menu]');
            const header = document.getElementById('main-header');
            const menus = document.querySelectorAll('.megamenu');
            let hideTimer;

            const show = (id) => {
                clearTimeout(hideTimer);
                menus.forEach(m => m.style.display = 'none');
                navLinks.forEach(l => l.classList.remove('active'));
                if (!id) return;
                const menu = document.getElementById(`${id}-megamenu`);
                const link = document.querySelector(`.nav-link[data-menu="${id}"]`);
                if (menu) menu.style.display = 'block';
                if (link) link.classList.add('active');
            };

            const scheduleHide = () => {
                hideTimer = setTimeout(() => show(null), 150);
            };

            navLinks.forEach(link => {
                link.addEventListener('mouseenter', () => show(link.getAttribute('data-menu')));
            });

            // Keep open when hovering the header or the menu zone
            header.addEventListener('mouseleave', scheduleHide);
            menus.forEach(menu => {
                menu.addEventListener('mouseenter', () => clearTimeout(hideTimer));
                menu.addEventListener('mouseleave', scheduleHide);
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // // This is your existing menu script, keep it.
            // const navLinks = document.querySelectorAll('.nav-link[data-menu]');
            // // ... rest of your menu script ...
            // // ...
            // // Keep open when hovering the header or the menu zone
            // header.addEventListener('mouseleave', scheduleHide);
            // menus.forEach(menu => {
            //     menu.addEventListener('mouseenter', () => clearTimeout(hideTimer));
            //     menu.addEventListener('mouseleave', scheduleHide);
            // });

            // // ✨ --- LAZY LOADING SCRIPT --- ✨

            let page = 2; // The next page to be loaded
            let loading = false; // A flag to prevent multiple AJAX requests at once
            let noMoreProducts = false; // A flag to stop fetching when all products are loaded

            const loadingIndicator = document.getElementById('loading-indicator');
            const productGrid = document.getElementById('product-grid');

            // This function handles the actual fetching of new products
            const loadMoreProducts = () => {
                if (loading || noMoreProducts) return;
                loading = true;

                const skeletonTemplate = document.getElementById('skeleton-template');
                const productGrid = document.getElementById('product-grid');

                // 1. Create and show 5 skeleton placeholders immediately
                const skeletons = [];
                for (let i = 0; i < 5; i++) {
                    // Clone the template's content
                    const skeletonNode = skeletonTemplate.content.cloneNode(true);
                    productGrid.appendChild(skeletonNode);
                    // Keep a reference to the newly added skeleton element to remove it later
                    skeletons.push(productGrid.lastElementChild);
                }

                fetch(`?page=${page}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        if (html.trim().length === 0) {
                            noMoreProducts = true;
                            return; // Stop here if no more products
                        }

                        // 3. Append the real product data
                        productGrid.insertAdjacentHTML('beforeend', html);
                        page++;

                        // Re-run the image observer for the new real images
                        observeLazyImages();
                    })
                    .catch(error => console.error('Error loading more products:', error))
                    .finally(() => {
                        // 2. ALWAYS remove the skeleton placeholders, even if there was an error
                        skeletons.forEach(skeleton => skeleton.remove());
                        loading = false;
                    });
            };

            // Listen for scroll events on the window
            window.addEventListener('scroll', () => {
                // Check if the user has scrolled to 500px from the bottom of the page
                if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
                    loadMoreProducts();
                }
            });
        });
    </script>
@endsection
