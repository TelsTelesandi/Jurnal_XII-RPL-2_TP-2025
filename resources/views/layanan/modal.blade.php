<!-- Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg max-w-3xl w-full relative">
        <button id="closeModal" class="absolute top-2 right-2 text-gray-700 hover:text-black text-2xl">&times;</button>
        <h2 id="modalTitle" class="text-xl font-bold mb-4"></h2>
        <img id="modalImage" src="" alt="" class="w-full rounded-lg shadow">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.open-modal').forEach(item => {
        item.addEventListener('click', () => {
            document.getElementById('modalImage').src = item.getAttribute('data-img');
            document.getElementById('modalTitle').innerText = item.getAttribute('data-title');
            document.getElementById('imageModal').classList.remove('hidden');
            document.getElementById('imageModal').classList.add('flex');
        });
    });

    document.getElementById('closeModal').addEventListener('click', () => {
        document.getElementById('imageModal').classList.add('hidden');
        document.getElementById('imageModal').classList.remove('flex');
    });

    document.getElementById('imageModal').addEventListener('click', (e) => {
        if (e.target.id === 'imageModal') {
            document.getElementById('imageModal').classList.add('hidden');
            document.getElementById('imageModal').classList.remove('flex');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === "Escape") {
            document.getElementById('imageModal').classList.add('hidden');
            document.getElementById('imageModal').classList.remove('flex');
        }
    });
});
</script>
