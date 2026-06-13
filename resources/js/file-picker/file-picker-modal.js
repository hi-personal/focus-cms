// === TOC START ===
// 🔹 Table of Contents (Generated)
//
// file: resources/js/file-picker/file-picker-modal.js
//
// === ANCHOR ===
// 26#ANCHOR: [id="open-file-picker-modal"] File Picker Modal megnyitása
// 80#ANCHOR: Reset File Picker Cache (exported)
// 92#ANCHOR: Refresh File Picker File List
// 215#ANCHOR: Detect Gallery Shortcode
// 260#ANCHOR: Replace Shortcode Atts
// 299#ANCHOR: Refresh Album Files
// 511#ANCHOR: FilePickerModal: Egér és érintő műveletek (Kijelölés és előnézeti modal nyitás.)
//
// === NOTE ===
// 297#NOTE: Szép vagy
//
// === TOC END ===


import { parseJSON } from 'jquery';
import { setVar, getVar } from '../image-picker.js';


/**
 * File Picker Modal megnyitása
 * ANCHOR[id="open-file-picker-modal"] File Picker Modal megnyitása
 */
export function openFilePickerModal(eventSource) {
    $("#filePickerModal").removeClass('hidden');
    $("body").addClass('overflow-y-hidden');

    refreshFileList();
};

window.refreshFileList = function() {
    $.ajax({
        url: "/admin/file-picker",
        method: "GET",
        data: {
            _token: document.querySelector('meta[name="csrf-token"]').content,
            post_id: $("#post_id").data("id"),
        },
        success: function(response) {
            $("#fileList").html(response.html);
        },
        error: function(xhr, status, error) {
            console.error("Ajax hiba:", error);
            reject(error);
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    $("#closeFilePickerModal").on('click', function() {
        $("#filePickerModal").addClass('hidden');
        $('body').removeClass('overflow-y-hidden');
    });

    $("#filePickerModal").on("click", "#selectAllFile", function() {
        var table = $(this).closest('table');
        var checkboxes = table.find('.file-checkbox');
        var allChecked = checkboxes.length === checkboxes.filter(':checked').length;

        checkboxes.prop("checked", !allChecked);
    });

    $("#filePickerModal").on("click", "#deleteSelectedfiles", function() {
        $("#confirmModal").removeClass("hidden").data("caller", "deleteFiles");
        $("#confirmModalTitle").html('<p>Művelet megerősítése</p>');
        $("#confirmModalContent").html('<p>Biztosan törlöd a kijelölt fájlokat?</p>');
    });

    $("#filePickerModal").on("click", "#selectAllFile, .file-checkbox", function() {
        $("#deleteSelectedfiles").prop("disabled", $("#postFilesListTable").find('.file-checkbox:checked').length == 0);
    });

    $("#confirmModal .confirm-button").on("click", function() {
        if ($("#confirmModal").data("caller") == "deleteFiles") {
            var checkboxes = $("#postFilesListTable").find('.file-checkbox:checked');

            if (checkboxes) {
                var ids = [];

                $(checkboxes).each(function(){
                    ids.push($(this).closest('tr').data('id'));
                });

                $.ajax({
                    url: "/admin/file-picker-delete-files",
                    method: "POST",
                    data: {
                        _token: document.querySelector('meta[name="csrf-token"]').content,
                        post_id: $("#post_id").data("id"),
                        deleted_files: ids
                    },
                    success: function(response) {
                        refreshFileList();
                    },
                    error: function(xhr, status, error) {
                        console.error("Ajax xhr:" + xhr + " status: " + status + " hiba:", error);
                        reject(error);
                    }
                });
            }

            $("#confirmModal").addClass("hidden");
        }
    });
});