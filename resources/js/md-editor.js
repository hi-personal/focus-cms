window.currentGalleryDiv = null;

import { registerResizeEditor } from './resize-editor.js';
import './preview-post-content.js';
import './image-picker';
import * as imagePickerModal from './image-picker/image-picker-modal.js';
import * as filePickerModal from './file-picker/file-picker-modal.js';

window.editor = null;


document.addEventListener('DOMContentLoaded', function () {

    window.editor = $('#myeditor').length > 0 ? $('#myeditor') : $('<textarea>', { name: 'myeditor', id: 'myeditor' });

    // Kattintási esemény a .gallery-line elemekre
    window.editor.on('click', '.gallery-line', function () {
        // Távolítsd el a kijelölt állapotot az összes .gallery-line elemről
        $('.gallery-line').removeClass('selected');

        // Add hozzá a kijelölt állapotot a kattintott elemhez
        $(this).addClass('selected');
    });

    // Kattintás a szerkeszthető területen kívülre
    $(document).on('click', function (event) {
        if (!$(event.target).closest('.gallery-line').length) {
            // Ha a kattintás nem egy .gallery-line elemre történt, távolítsd el a kijelölt állapotot
            $('.gallery-line').removeClass('selected');
        }
    });

    registerResizeEditor(window.editor);

    $(window).on("keydown", function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'p') {
            e.preventDefault(); // FONTOS: itt kell lennie elsőként!
            console.log("Ctrl+P letiltva, kattintás aktiválva");
            $('.previewButton').trigger('click');
            return false;
        }
    });


    $('.insert-image-album').click(function(){
        var mode = $(this).data('mode');
        var inputId = $(this).data('input-id');

        imagePickerModal.openImagePickerModal( mode ?? 'insert', inputId ?? null);
    });

    $('.insert-file-album').click(function(){
        filePickerModal.openFilePickerModal('insert');
    });

    window.insertContent = function(textarea, content) {
        if (textarea instanceof jQuery) {
            textarea = textarea.get(0);
        }

        if (textarea && textarea.selectionStart !== undefined) {
            let start = textarea.selectionStart;
            let end = textarea.selectionEnd;
            let text = textarea.value;

            // Szúrjuk be az aktuális kurzorpozícióba a tartalmat
            textarea.value = text.slice(0, start) + content + text.slice(end);

            // Mozgatjuk a kurzort a beillesztett tartalom végére
            textarea.selectionStart = textarea.selectionEnd = start + content.length;

            // Kiváltjuk az input eseményt a frissítéshez
            textarea.dispatchEvent(new Event('input', { bubbles: true }));

            // Fókusz vissza a textarea-ra
            textarea.focus();
        }
    };

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' || event.keyCode === 27) {
            closeImagePickerAndPreviewModal();
        }
    });

    function closeImagePickerAndPreviewModal() {
        const imagePickerModal = $('#imagePickerModal');
        const imagePreviewModal = $('#imagePreviewModal');

        if (!imagePreviewModal.hasClass('hidden')) {
            imagePreviewModal.addClass('hidden');
        } else if (!imagePickerModal.hasClass('hidden')) {
            imagePickerModal.addClass('hidden');
            $('body').removeClass('overflow-y-hidden');
        }
    }

    $(".deletePostButton").on("click", function() {
        $("#confirmModal").removeClass("hidden").data("caller", "deletePost");
        $("#confirmModalTitle").html('<p>Művelet megerősítése</p>');
        $("#confirmModalContent").html('<p>Figyelem!<br>A bejegyzés törlésekor, a hozzá feltöltött képek és egyéb fájlok is törlődnek!</p><p>Biztosan törlöd a bejegyzést?</p>');
    })

    $("#confirmModal .close-button").on("click", function() {
        $("#confirmModal").addClass("hidden");
    });

    $("#confirmModal .confirm-button").on("click", function() {
        if ($("#confirmModal").data("caller") == "deletePost") {
            window.location = $(".deletePostButton").first().data("href");
        }
    });

    $('#imagePreviewModal').on('click', '#copyBtn', function() {
        const text = $('#original_url').val();

        navigator.clipboard.writeText(text).then(function() {
            alert('Vágólapra másolva!');
        }).catch(function(err) {
            alert('Hiba történt: ' + err);
        });
    });
});