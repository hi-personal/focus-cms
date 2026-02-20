import { setVar, getVar } from '../image-picker.js';
import { resetImagePickerCache } from './image-picker-modal.js';

let touchCount = 0;
let currentPreviewImageId = 0;
let currentPreviewImageSize = 0;

document.addEventListener("DOMContentLoaded", function () {
    $("#imagePreviewModal").on("submit", "#imageDetailsForm", function(e) {
        saveImageDetails(e);
        //resetImagePickerCache();
        loadImageDetails(getVar('currentPreviewImageId'));
    });
});

// Előnézeti modal megnyitása kép ID alapján
window.openImagePreviewModal = function(imageId, event) {
    setVar('currentPreviewImageId', imageId);
    setVar('currentPreviewImageSize', 'medium');

    var imageData = getVar('modalImageSizes')[imageId];

    $('#imagePreviewModal')
        .removeClass('hidden')
        .attr('data-media-id', imageId);

    loadImageDetails(imageId);

    $('#previewImage').attr('src', imageData.medium ?? imageData.original);

    generateSizeButtons(imageId);
};

// Előnézeti modal bezárása
window.closeImagePreviewModal = function() {
    $('#imagePreviewModal').addClass('hidden');
}

function loadImageDetails(imageId) {
    $.ajax({
        url: `/admin/image-picker/${imageId}/details`,
        method: 'GET',
        success: function(response) {
            $('#imageDetailsForm textarea[name="original_url"]').text(response.original_url);
            $('#imageDetailsForm input[name="title"]').val(response.title);
            $('#imageDetailsForm input[name="name"]').val(response.name);
            $('#imageDetailsForm input[name="mime_type"]').val(response.mime_type);
            $('#imageDetailsForm input[name="file_size"]').val(response.file_size);
            $('#imageDetailsForm input[name="alt_text"]').val(response.alt_text);
            $('#imageDetailsForm textarea[name="description"]').val(response.description);
        },
        error: function() {
            alert('Hiba történt az adatok betöltése közben.');
        }
    });
}

function generateSizeButtons(imageId) {
    const sizeButtonsContainer = $('#sizeButtons');
    sizeButtonsContainer.empty(); // Távolítsd el a korábbi gombokat

    const imageSizes = getVar('modalImageSizes')[imageId];

    // Generált méretek gombjai
    $.each(imageSizes, function(sizeKey, url) {
        const text = sizeKey.substring(0, 1).toUpperCase() + sizeKey.substring(1);;
        sizeButtonsContainer.append(`
            <button
                class="bg-blue-500 text-white px-3 py-1 rounded"
                onclick="updatePreviewImage('${url}', '${sizeKey}')"
            >
                ${text}
            </button>
        `);
    });

    $('#previewImageId').text('#(' + imageId + ')');

    if (getVar('imagePickerMode') == 'insert-input') {
        sizeButtonsContainer.append(`
            <button
                id="selectCurrentSizeButton"
                class="bg-green-500 text-white px-4 py-2 rounded"
                onclick="selectCurrentPreviewImage()"
            >
                Kiválasztás
            </button>
        `);
    } else {
        sizeButtonsContainer.append(`
            <button
                id="insertCurrentSizeButton"
                class="bg-green-500 text-white px-4 py-2 rounded"
                onclick="insertCurrentPreviewImage(false)"
            >Beillesztés</button>

            <button
                id="insertCurrentSizeButton"
                class="bg-green-500 text-white px-4 py-2 rounded"
                onclick="insertCurrentPreviewImage('link')"
            >Beillesztés hivatkozásként</button>

            <button
                id="insertCurrentSizeButton"
                class="bg-green-500 text-white px-4 py-2 rounded"
                onclick="insertCurrentPreviewImage('viewer')"
            >Beillesztés képnézővel</button>
        `);
    }
}



// Előnézeti kép frissítése
window.updatePreviewImage = function(url, size) {
    $('#previewImage').attr('src', url);
    setVar('currentPreviewImageSize', size);
};

// Beillesztés gomb kattintásának kezelése
window.insertCurrentPreviewImage = function(asLink) {
    var currentPreviewImageId = getVar('currentPreviewImageId');
    var currentPreviewImageSize = getVar('currentPreviewImageSize');

    if (window.editor && currentPreviewImageId) {
        if (asLink == 'link') {
            insertContent(
                window.editor,
                `[image id(${currentPreviewImageId}) size(${currentPreviewImageSize}) link(@original)]`
            );
        } else if (asLink == 'viewer') {
            insertContent(
                window.editor,
                `[image id(${currentPreviewImageId}) size(${currentPreviewImageSize}) link(@original) mode(viewer)]`
            );
        } else {
            insertContent(
                window.editor,
                `[image id(${currentPreviewImageId}) size(${currentPreviewImageSize})]`
            );
        }

        closeImagePreviewModal();

        $("#imagePickerModal").addClass('hidden');
        $('body').removeClass('overflow-y-hidden');
    }
};

window.selectCurrentPreviewImage = function() {
    var currentPreviewImageId = getVar('currentPreviewImageId');
    var currentPreviewImageSize = getVar('currentPreviewImageSize');
    var targetId = getVar('imagePickerTargetId');
    var value = `${currentPreviewImageId}@${currentPreviewImageSize}`;

    $(targetId).val(value);

    closeImagePreviewModal();

    $("#imagePickerModal").addClass('hidden');
    $('body').removeClass('overflow-y-hidden');
};


// Kép adatainak mentése
function saveImageDetails(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const currentImageId = getVar('currentPreviewImageId');

    console.log("currentPreviewImageId: " + currentImageId);

    $.ajax({
        url: `/admin/image-picker/${currentImageId}/update`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        success: function(response) {
            $('#previewFeedback').removeClass('hidden').text(response.message).addClass('bg-green-100 text-green-700');
            setTimeout(()=>{
                $('#previewFeedback').addClass('hidden');
            }, 2000);
        },
        error: function(xhr) {
            $('#previewFeedback').removeClass('hidden').text(xhr.responseJSON.message || 'Hiba történt a mentés során.').addClass('bg-red-100 text-red-700');
            setTimeout(()=>{
                $('#previewFeedback').addClass('hidden');
            }, 4000);
        }
    });
};