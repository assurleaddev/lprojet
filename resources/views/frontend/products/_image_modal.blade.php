<!-- Image Preview Modal -->
<div id="image-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center"
    style="background-color: rgba(0, 0, 0, 0.9);">
    <button id="modal-close" class="absolute top-4 right-4 text-white hover:text-gray-300 p-2">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <button id="modal-prev" class="absolute left-4 text-white hover:text-gray-300 p-2 rounded-full"
        style="background-color: rgba(0, 0, 0, 0.5);">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>

    <img id="modal-image" src="" class="max-h-[90vh] max-w-[90vw] object-contain select-none">

    <button id="modal-next" class="absolute right-4 text-white hover:text-gray-300 p-2 rounded-full"
        style="background-color: rgba(0, 0, 0, 0.5);">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Image Modal Logic ---
        const modal = document.getElementById('image-modal');
        const modalImage = document.getElementById('modal-image');
        const closeBtn = document.getElementById('modal-close');
        const prevBtn = document.getElementById('modal-prev');
        const nextBtn = document.getElementById('modal-next');

        let images = [];
        let currentIndex = 0;

        // Collect all gallery images
        const galleryImages = document.querySelectorAll('.product-gallery-image');
        galleryImages.forEach((img, index) => {
            images.push(img.src);
            img.dataset.index = index;

            img.addEventListener('click', function () {
                currentIndex = index;
                openModal();
            });
        });

        function openModal() {
            modalImage.src = images[currentIndex];
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function showNext() {
            currentIndex = (currentIndex + 1) % images.length;
            modalImage.src = images[currentIndex];
        }

        function showPrev() {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            modalImage.src = images[currentIndex];
        }

        closeBtn.addEventListener('click', closeModal);
        nextBtn.addEventListener('click', showNext);
        prevBtn.addEventListener('click', showPrev);

        // Close on background click
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function (e) {
            if (modal.classList.contains('hidden')) return;

            if (e.key === 'Escape') closeModal();
            if (e.key === 'ArrowRight') showNext();
            if (e.key === 'ArrowLeft') showPrev();
        });
    });
</script>