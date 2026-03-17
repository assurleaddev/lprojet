<!-- Admin Image Preview Modal -->
<div id="admin-image-modal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/90 p-4">
    <button id="admin-modal-close" class="absolute top-4 right-4 text-white hover:text-gray-300 p-2 transition-colors">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <button id="admin-modal-prev" class="absolute left-4 text-white hover:text-gray-300 p-2 rounded-full bg-black/50 transition-colors">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>

    <img id="admin-modal-image" src="" class="max-h-[85vh] max-w-[90vw] object-contain select-none shadow-2xl rounded-sm">

    <button id="admin-modal-next" class="absolute right-4 text-white hover:text-gray-300 p-2 rounded-full bg-black/50 transition-colors">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('admin-image-modal');
        const modalImage = document.getElementById('admin-modal-image');
        const closeBtn = document.getElementById('admin-modal-close');
        const prevBtn = document.getElementById('admin-modal-prev');
        const nextBtn = document.getElementById('admin-modal-next');

        let images = [];
        let currentIndex = 0;

        // Use event delegation to handle clicks on images that might be added dynamically by Alpine
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('media-preview-image')) {
                // Refresh images list to handle dynamic additions/removals
                const galleryImages = Array.from(document.querySelectorAll('.media-preview-image'));
                images = galleryImages.map(img => img.src);
                currentIndex = galleryImages.indexOf(e.target);
                
                if (currentIndex !== -1) {
                    openModal();
                }
            }
        });

        function openModal() {
            if (images.length === 0) return;
            modalImage.src = images[currentIndex];
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Hide navigation if only one image
            if (images.length <= 1) {
                prevBtn.classList.add('hidden');
                nextBtn.classList.add('hidden');
            } else {
                prevBtn.classList.remove('hidden');
                nextBtn.classList.remove('hidden');
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function showNext() {
            if (images.length === 0) return;
            currentIndex = (currentIndex + 1) % images.length;
            modalImage.src = images[currentIndex];
        }

        function showPrev() {
            if (images.length === 0) return;
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            modalImage.src = images[currentIndex];
        }

        closeBtn.addEventListener('click', closeModal);
        nextBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            showNext();
        });
        prevBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            showPrev();
        });

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
