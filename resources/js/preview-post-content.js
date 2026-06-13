//Preview post content functions
document.addEventListener('DOMContentLoaded', function () {    // Modal megnyitása
    $('.previewButton').on('click', function() {
        const title = $('#title').val();
        const content = $('#myeditor').val();

        $('#previewModal').removeClass('hidden');
        $("body").addClass("overflow-hidden");

        // Betöltési animáció hozzáadása
        $('#previewContent').html(`
            <div class="flex flex-col items-center justify-center py-10">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-600"></i>
                <p class="mt-4 text-gray-700 text-lg font-semibold">Előnézet generálása...</p>
            </div>
        `);

        var postId = $("#post_id").data("id");
        var post_type_name = $("#post_type_name").data("post-type-name");

        $.ajax({
            url: `/admin/post-type/${post_type_name}s/preview/save-temp`,
            method: 'POST',
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').content,
                content: JSON.stringify({ content }),
                id: postId
            },
            success: function(token) {
                $('#previewContent').html(`
                    <iframe
                        src="/admin/post-type/${post_type_name}s/preview?token=${token}&id=${postId}"
                        class="w-full h-full border-none"
                    ></iframe>
                `);
            },
            error: function() {
                $('#previewContent').html(`
                    <div class="text-red-600 text-center py-6">
                        <i class="fas fa-exclamation-triangle text-3xl"></i>
                        <p class="mt-2 font-semibold">Hiba történt az előnézet betöltése közben.</p>
                    </div>
                `);
            }
        });
    });


    // Bezáró gomb eseménykezelője
    $('.closeModal').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#previewModal').addClass('hidden');
        $("body").removeClass("overflow-hidden");
        window.toggleBodyOverflow();
    });

    // Háttér kattintás eseménykezelője
    $('#previewModal').click(function(e) {
        if ($(e.target).is('#previewModal')) {
            $('#previewModal').addClass('hidden');
            $("body").removeClass("overflow-hidden");
        }
    });

    //Előnézeti modal bezárása ESC gomb megnyomásakor
    $(document).on('keydown', function(event) {
        if (event.keyCode === 27) {
            setTimeout(() => {
                $('#previewModal').addClass('hidden');
                $("body").removeClass("overflow-hidden");
            }, 40);
        }
    });
});


