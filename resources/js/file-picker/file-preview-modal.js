import { setVar, getVar } from '../image-picker.js';

let touchCount = 0;

document.addEventListener("DOMContentLoaded", function () {
    setVar('currentPreviewFileId', 0);

    $("#filePickerModal").on("click", ".selectorCol", function(e) {
        console.log("click:selectorCol");
        var fileId = $(this).closest('tr').data('id');
        setVar('currentPreviewFileId', fileId);
        console.log("openFilePreviewModal, id: " + fileId);
        loadFileDetails(fileId);
        openFilePreviewModal(fileId);
    });

    $("#filePreviewModal").on("click", "#tabFileDetails", function(e) {
        var fileId = getVar('currentPreviewFileId');
        console.log("loadFileDetails, id: " + fileId);
    });

    $("#filePreviewModal").on("submit", "#fileDetailsForm", function(e) {
        saveFileDetails(e);
        refreshFileList();
    });

    $("#filePreviewModal").on("click", "#copyFileUrlToClipboard", function(e) {
        var url = $("#previewFileUrl").val();
        copyToClipboard(url);
        $("#filePickerModal").addClass('hidden');
        $('#filePreviewModal').addClass('hidden');
    });

    $("#filePreviewModal").on("click", "#insertFileUrlToEditor", function(e) {
        var url = $("#previewFileUrl").val();
        $("#filePickerModal").addClass('hidden');
        $('#filePreviewModal').addClass('hidden');
        insertFileData(url);
    });

    $("#filePreviewModal").on("click", "#insertFilSHToEditor", function(e) {
        var currentFileId = getVar('currentPreviewFileId');
        var url = $("#previewFileUrl").val();
        var sh = '[file id(' + currentFileId + ')';

        //if ($("#inserFileSCTitle").is(":checked")) {
            sh += ' title(@title@ext)';
        //}
        if ($("#inserFileSCDL").is(":checked")) {
            sh += ' dl-link';
        }
        if ($("#inserFileSCOpen").is(":checked")) {
            sh += ' open-link';
        }

        sh += ' ]';

        $("#filePickerModal").addClass('hidden');
        $('#filePreviewModal').addClass('hidden');
        insertFileData(sh);
    });
});

// Beillesztés gomb kattintásának kezelése
window.insertFileData = function(url) {

    insertContent(
        window.editor,
        url
    );

    /*
    var currentPreviewImageId = getVar('currentPreviewImageId');
    var currentPreviewImageSize = getVar('currentPreviewImageSize');

    if (window.editor && currentPreviewImageId) {
        if (asLink == true) {
            insertContent(
                window.editor,
                `[image id="${currentPreviewImageId}" size="${currentPreviewImageSize}" class="rounded-md shadow-sm](link)`
            );
        } else {
            insertContent(
                window.editor,
                `[image id="${currentPreviewImageId}" size="${currentPreviewImageSize}" class="rounded-md shadow-sm]`
            );
        }

        closeImagePreviewModal();

        $("#imagePickerModal").addClass('hidden');
        $('body').removeClass('overflow-y-hidden');
    }
        */
};



// Előnézeti modal megnyitása kép ID alapján
window.openFilePreviewModal = function(fileId) {
    $('#filePreviewModal')
        .removeClass('hidden')
        .attr('data-file-id', fileId);
};

// Előnézeti modal bezárása
window.closeFilePreviewModal = function() {
    $('#filePreviewModal').addClass('hidden');
}

function loadFileDetails(fileId) {
    $.ajax({
        url: `/admin/file-picker/${fileId}/details`,
        method: 'GET',
        success: function(response) {
            $('#previewFileUrl').val(response.url);
            $('#previewFileTitle').val(response.title);
            $('#previewFileName').val(response.name);
            $('#previewFileMimeType').val(response.mime_type);
            $('#previewFileFileExtension').val(response.file_file_extension);
            $('#previewFileFileName').val(response.file_file_name + "." + response.file_file_extension);
            $('#previewFileSize').val(response.file_size);
            $('#previewFileAltText').html(response.alt_text);
            $('#previewFileDescription').html(response.description);

            $('#fileDetailsForm input[name="title"]').val(response.title);
            $('#fileDetailsForm input[name="name"]').val(response.name);
            $('#fileDetailsForm input[name="mime_type"]').val(response.mime_type);
            $('#fileDetailsForm input[name="file_size"]').val(response.file_size);
            $('#fileDetailsForm input[name="alt_text"]').val(response.alt_text);
            $('#fileDetailsForm textarea[name="description"]').val(response.description);
        },
        error: function() {
            alert('Hiba történt az adatok betöltése közben.');
        }
    });
}


// Kép adatainak mentése
function saveFileDetails(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const currentFileId = getVar('currentPreviewFileId');

    console.log("currentFileId: " + currentFileId);

    $.ajax({
        url: `/admin/file-picker/${currentFileId}/update`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        success: function(response) {
            $('#previewFileFeedback').removeClass('hidden').text(response.message).addClass('bg-green-100 text-green-700');
            loadFileDetails(currentFileId);
            //refreshFileList();
        },
        error: function(xhr) {
            $('#previewFileFeedback').removeClass('hidden').text(xhr.responseJSON.message || 'Hiba történt a mentés során.').addClass('bg-red-100 text-red-700');
        }
    });
};


function copyToClipboard(data) {
    // Létrehozunk egy ideiglenes input elemet
    const tempInput = document.createElement("input");
    tempInput.value = data;
    document.body.appendChild(tempInput);

    // Kijelöljük a tartalmat
    tempInput.select();
    tempInput.setSelectionRange(0, 99999); // Mobilos támogatás

    try {
        // Modern Clipboard API
        if(navigator.clipboard) {
            navigator.clipboard.writeText(data)
                .then(() => alert('Másolva a vágólapra!'))
                .catch(err => {
                    console.error('Clipboard API hiba:', err);
                    // Fallback ha a Clipboard API nem működik
                    document.execCommand('copy');
                    alert('Másolva (fallback módszer)!');
                });
        } else {
            // Régebbi böngészők támogatása
            document.execCommand('copy');
            alert('URL másolva a vágólapra.');
        }
    } catch (err) {
        console.error('Másolási hiba:', err);
        alert('Másolás sikertelen!');
    } finally {
        // Mindig eltávolítjuk az ideiglenes inputot
        document.body.removeChild(tempInput);
    }
}
