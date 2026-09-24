// === TOC START ===
// 🔹 Table of Contents (Generated)
//
// file: resources/js/image-picker/image-picker-modal.js
//
// === ANCHOR ===
// 26#ANCHOR: [id="open-image-picker-modal"] Image Picker Modal megnyitása
// 80#ANCHOR: Reset Image Picker Cache (exported)
// 92#ANCHOR: Refresh Image Picker Image List
// 215#ANCHOR: Detect Gallery Shortcode
// 260#ANCHOR: Replace Shortcode Atts
// 299#ANCHOR: Refresh Album Images
// 511#ANCHOR: ImagePickerModal: Egér és érintő műveletek (Kijelölés és előnézeti modal nyitás.)
//
// === NOTE ===
// 297#NOTE: Szép vagy
//
// === TOC END ===


import { setVar, getVar } from '../image-picker.js';
import { updateImageClasses } from './update-classes.js';

document.addEventListener("DOMContentLoaded", function () {
    const editor = document.getElementById('myeditor');

    $('#tabImages').on('click', function(){
        $('#manageAlbumImagesGrid .image-checkbox-checked').removeClass('image-checkbox-checked');
    });

    $("#closeModal").on('click', function() {
        $("#imagePickerModal").addClass('hidden');
        $('body').removeClass('overflow-y-hidden');
    });

    if ($(editor).length > 0) {
        let touchTimer_2 = null;

        // 🔹 PC-n: Ctrl + Bal klikk → Modal nyitás
        $(editor).on("click", function (event) {
            if (event.ctrlKey && event.button === 0) {
                console.log("window.editor:Click:OpenImagePickerModal");
                openImagePickerModal("editor");
                $(window.editor).blur(); // 🔹 Kurzor eltüntetése
            }
        });


        // 🔹 Mobil érintéskezelés (javított passive event-ekkel)
        editor.addEventListener('touchstart', function(e) {
            if (e.touches.length === 2) {
                touchTimer_2 = setTimeout(function() {
                    console.log("Touch:Editor:Cick:OpenModal");
                    openImagePickerModal("editor");
                    setTimeout(() => window.editor.blur(), 1000);
                }, 1000);
            }
        }, { passive: true });

        editor.addEventListener('touchmove', function(e) {
            if (e.touches.length < 2) clearTimeout(touchTimer_2);
        }, { passive: true });

        editor.addEventListener('touchend', function(e) {
            if (e.touches.length < 2) clearTimeout(touchTimer_2);
        }, { passive: true });
    }

    // Balra mozgatás
    $('#moveSelectedImageLeft').on('click', function () {
        const selected = $('.image-checkbox-checked').closest('.selectorImageDiv'); // Kijelölt elemek
        if (selected.length === 0) return; // Ha nincs kijelölt elem, kilép

        // Az első kijelölt elem előtti elem
        const firstSelected = selected.first();
        const prev = firstSelected.prev('.selectorImageDiv');

        if (prev.length > 0) {
            selected.detach().insertBefore(prev); // Az összes kijelölt elemet mozgatjuk
        }

        // Frissítjük a sorrendet
        $(".sortable-grid").sortable("refresh");
        triggerSortableUpdate();
    });

    // Jobbra mozgatás
    $('#moveSelectedImageRight').on('click', function () {
        const selected = $('.image-checkbox-checked').closest('.selectorImageDiv'); // Kijelölt elemek
        if (selected.length === 0) return; // Ha nincs kijelölt elem, kilép

        // Az utolsó kijelölt elem utáni elem
        const lastSelected = selected.last();
        const next = lastSelected.next('.selectorImageDiv');

        if (next.length > 0) {
            selected.detach().insertAfter(next); // Az összes kijelölt elemet mozgatjuk
        }

        // Frissítjük a sorrendet
        $(".sortable-grid").sortable("refresh");
        triggerSortableUpdate();
    });

    // Sorrend frissítésének triggerelése
    function triggerSortableUpdate() {
        const sortedIds = $(".sortable-grid .selectorImageDiv").map(function () {
            return $(this).data("image-id");
        }).get();

        console.log("NEW ORDER AFTER MOVE: " + sortedIds.join(','));
        $("#imageOrder").val(sortedIds.join(','));
        console.log("Új sorrend mentése:", sortedIds);
    }

    let lastSelected = null;


    function toggleAlbumUpdateButton() {
        if ($('#editAlbumTabContent').hasClass('hidden') == false) {
            // 🔹 UI frissítés
            const isDisabled = $('#manageAlbumImages .selectorImageDiv').length === 0;
            $('#insertAlbum')
                .toggleClass('bg-blue-500 hover:bg-blue-400', !isDisabled)
                .toggleClass('bg-gray-500 hover:bg-gray-400', isDisabled)
                .prop('disabled', isDisabled);
        }
    }

    function toggleSelectedImagesMoveIcons() {
        setTimeout(() => {
            const isDisabled = $('#manageAlbumImages .image-checkbox-checked').length > 0;

            console.log("Kiejöltek száma: "+length);
            console.log("is Disabled: "+isDisabled);

            $('#moveSelectedImageLeft, #moveSelectedImageRight')
                .toggleClass('bg-gray-300', !isDisabled)
                .toggleClass('bg-blue-500', isDisabled)
                .prop('disabled', !isDisabled);
        }, 100);
    }

    // 🔹 Jobbklikk tiltása csak az adott modalon belül
    $("#imagePickerModal").on("mousedown contextmenu", function (e) {
        e.preventDefault();
        e.stopPropagation();

        return true;
        console.log("🔹 Jobbklikk tiltva a modalon");
    });
    //************************************************************************** */

    /**
     * ImagePickerModal: Egér és érintő műveletek (Kijelölés és előnézeti modal nyitás.)
     * ANCHOR ImagePickerModal: Egér és érintő műveletek (Kijelölés és előnézeti modal nyitás.)
     */

    // Globális változók
    let lastSelectedImage = null;
    let longPressTimeout = null;
    let longPressTimeout_2 = null;
    let touchStartTime = 0;
    let touchStartTarget = null;
    let isLongPress = false;
    let isScrolling = false;
    let initialTouchPoints = [];
    let isMultiTouchActive = false;

    $("#imagePickerModal")
        // Desktop események
        .on("mousedown", ".selectorImage", function(e) {
            if (!('ontouchstart' in window)) {
                if (e.which === 3) { // Jobb egérgomb
                    e.preventDefault();
                    e.stopPropagation();
                    handleSelection($(this).parent(), e, false, false);
                }

                if (e.which === 1 && e.shiftKey == 1) { //shift + bal egérgomb
                    e.preventDefault();
                    e.stopPropagation();
                    handleSelection($(this).parent(), e, false, true);
                }
            }
        })
        .on("click contextmenu", ".selectorImageOpenPreviewModal", function(e) {
            if (e.which === 3 || (e.which === 1 && e.shiftKey == 1)) {

                const $parent = $(this).parent();
                handleSelection($parent, e, false, e.shiftKey);
                e.preventDefault();
            }

            if (e.which === 1 && e.shiftKey == 0) {
                //if ($(this).closest('#manageAlbumImages').length === 0) {
                    const imageId = $(this).closest('.selectorImageDiv').find('.selectorImage').data('id');
                    window.openImagePreviewModal(imageId);
                //}
            }
        });


    // 1. Eseménydelegáció használata a modal root szintjén
    function initImagePickerTouchHandlers() {
        const modal = document.getElementById('imagePickerModal');
        if (!modal) return;

        // Mindig először eltávolítjuk a meglévő eseménykezelőket
        modal.removeEventListener('touchstart', handleModalTouch);
        modal.removeEventListener('touchmove', handleModalTouch);
        modal.removeEventListener('touchend', handleModalTouch);

        // Új eseménykezelők hozzáadása
        modal.addEventListener('touchstart', handleModalTouch, { passive: false });
        modal.addEventListener('touchmove', handleModalTouch, { passive: false });
        modal.addEventListener('touchend', handleModalTouch, { passive: true });
    }

    // 2. Központosított touch eseménykezelő
    function handleModalTouch(e) {
        const targetImg = e.target.closest('.selectorImage');
        if (!targetImg) return;

        switch (e.type) {
            case 'touchstart':
                handleTouchStart(e, targetImg);
                break;
            case 'touchmove':
                handleTouchMove(e, targetImg);
                break;
            case 'touchend':
                handleTouchEnd(e, targetImg);
                break;
        }
    }

    // 3. Touch események kezelése
    const touchState = {
        longPressTimer: null,
        longPressTimer2: null,
        isLongPress: false,
        isScrolling: false,
        lastTouchPoints: 0
    };

    function handleTouchStart(e, targetImg) {
        // Reset állapotok
        clearTimeout(touchState.longPressTimer);
        touchState.isLongPress = false;
        touchState.isScrolling = false;
        touchState.lastTouchPoints = e.touches.length;

        const $current = $(targetImg).parent();

        // Kétujjas érintés
        if (e.touches.length === 2) {
            e.preventDefault();
            console.log("Two-touch detected");

            touchState.longPressTimer = setTimeout(() => {
                if (lastSelectedImage) {
                    handleSelection($current, e, true, true);
                }
            }, 400);
            return;
        }

        // Egyujjas érintés
        touchState.startTime = Date.now();
        touchState.startTarget = $current[0];

        touchState.longPressTimer2 = setTimeout(() => {

            if (!touchState.isScrolling) {
                e.preventDefault();
                touchState.isLongPress = true;
                window.openImagePreviewModal(targetImg.dataset.id);
            }
        }, 2000);
    }

    function handleTouchMove(e, targetImg) {
        // Görgetés detektálása
        if (e.touches.length === 1) {
            touchState.isScrolling = true;
            clearTimeout(touchState.longPressTimer);
        }

        // Kétujjas mozgás
        if (touchState.lastTouchPoints === 2 && e.touches.length === 2) {
            e.preventDefault();
            const $current = $(targetImg).parent();
            if (lastSelectedImage) {
                handleSelection($current, e, true, true);
            }
        }
    }

    function handleTouchEnd(e, targetImg) {
        clearTimeout(touchState.longPressTimer);
        clearTimeout(touchState.longPressTimer2);

        // Rövid érintés
        if (!touchState.isLongPress && !touchState.isScrolling && touchState.lastTouchPoints === 1) {
            const $current = $(targetImg).parent();
            handleSelection($current, e, true, false);
        }

        // Állapot reset
        touchState.isLongPress = false;
        touchState.isScrolling = false;
        touchState.lastTouchPoints = 0;
    }

    // // 4. Dinamikus tartalom kezelése
    // $(document).on('ajaxComplete DOMNodeInserted', '#imagePickerModal', function() {
    //     initImagePickerTouchHandlers();
    // });

    // // 5. Inicializálás
    // $(document).ready(function() {
    //     initImagePickerTouchHandlers();
    // });


    // 1. Létrehozunk egy MutationObserver példányt
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (document.querySelector('#imagePickerModal')) {
                initImagePickerTouchHandlers();
            }
        });
    });

    // 2. Beállítjuk a figyelést
    $(document).ready(function() {
        // Kezdeti inicializálás
        initImagePickerTouchHandlers();

        // Figyeljük a dokumentum változásait
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    // 3. AJAX események kezelése
    $(document).ajaxComplete(function() {
        if (document.querySelector('#imagePickerModal')) {
            initImagePickerTouchHandlers();
        }
    });













    // A te általad megadott handleSelection függvény változatlanul marad
    function handleSelection($current, originalEvent, isTouch = false, isMultiTouch = false) {
        const $container = $("#imagePickerModal");
        const $allImages = $container.find(".selectorImage").parent();
        const isRangeSelection = isTouch ? isMultiTouch : originalEvent.shiftKey;

        if (isRangeSelection && lastSelectedImage) {
            const $last = $(lastSelectedImage);
            const currentIndex = $allImages.index($current);
            const lastIndex = $allImages.index($last);

            const start = Math.min(currentIndex, lastIndex+1);
            const end = Math.max(currentIndex, lastIndex);

            // Triggereljük a rejtett div kattintását
            $allImages.slice(start, end + 1).find('.js-checkbox-trigger').trigger('click');
        } else {
            // Egyedi kijelölés
            $current.find('.js-checkbox-trigger').trigger('click');
            lastSelectedImage = $current[0];
        }

        if (!isTouch && originalEvent.preventDefault) {
            originalEvent.preventDefault();
        }

        toggleAlbumUpdateButton();
        toggleSelectedImagesMoveIcons();
    }



    //------------------------------------


    $('#imagePickerModal').on('click', '#removeImagesFromAlbum', function(e) {
        $('#manageAlbumImagesGrid').find('.image-checkbox-checked').each(function() {
            $(this).parent().remove();
        });

        toggleAlbumUpdateButton();
        toggleSelectedImagesMoveIcons();
    });


    //Kiválasztott képek beillesztése az albumba
    $("#imagePickerModal").on("click", "#editImageAlbum", function (e) {
        refreshAlbumImages(getImageAlbumImagesIds());

        $('#tabEditAlbum').trigger('click');

        triggerSortableUpdate();

        setTimeout(() => {
            toggleAlbumUpdateButton();
        }, 200);
    });

    function getImageAlbumImagesIds() {
        var imagesArray = [];

        $("#imagePickerContent .image-checkbox-checked .selectorImage").each(function () {
            var imageId = $(this).attr("data-id");

            if (imageId) {
                imagesArray.push(imageId);
            }
        });

        return imagesArray;
    }

    $("#imagePickerModal").on("click", "#insertAlbum", function (e) {
        var detectedData = getVar('detectedData');
        var imagesArray = [];

        $("#manageAlbumImages .selectorImage").each(function () {
            let imageId = $(this).attr("data-id");
            console.log(imageId);
            if (imageId) {
                imagesArray.push(imageId);
            }
        });

        var { action, mediaArray, shortcode, attributes } = detectedData;

        if (action == 'insert') {
            insertSelectedImages(imagesArray, action); // 📌 Meghívjuk a külső függvényt az ID tömbbel
        } else {
            if (mediaArray) {
                var newImagesArray = $('#imageOrder').val();
                newImagesArray = newImagesArray.length > 0 ? newImagesArray : imagesArray;
                console.log("DETECTED DATA :");
                console.log(detectedData);
                // const atts = {
                //     ids: newImagesArray,
                // };

                var atts = attributes;
                console.log("ATTS");
                console.log(atts);
                atts.ids = newImagesArray;

                var newShortcode = replaceShortcodeAtts(shortcode, atts);
                var content = $(window.editor).val();
                var newContent = content.split(shortcode).join(newShortcode);

                $(window.editor).val(newContent);
            }
        }

        $("#imagePickerModal").addClass('hidden');
        $('body').removeClass('overflow-y-hidden');

        window.currentGalleryDiv = null;
    });

    function replaceShortcodeInEditor(code, newCode) {
        // A #window.editor tartalmának lekérése
        const editorContent = $(window.editor).html();

        // Az eredeti code cseréje newCode-ra
        const updatedContent = editorContent.replace(code, newCode);

        // A frissített tartalom visszaállítása a #window.editor-be
        $(window.editor).html(updatedContent);
    }

    var insertSelectedImages = function (imagesArray, action) {
        const shortcode = `[gallery ids(${imagesArray.join(', ')})]`;

        if (action === 'update' && window.currentGalleryDiv) {
            let ids = imagesArray.join(',');
            console.log(ids);
            window.currentGalleryDiv.setAttribute('data-ids', ids);
            $(window.currentGalleryDiv).html(`Gallery, ids: ${ids}`)

            let newContent = $(window.editor).html(); // Új tartalom lekérése
        } else {
            insertContent(window.editor, shortcode);
        }

        $("#imagePickerModal").addClass('hidden');
        $('body').removeClass('overflow-y-hidden');

        window.currentGalleryDiv = null;
    }

    $('#imagePickerModal').on('click', '#deleteSelectedImages', function(e) {
        var deletedImages = [];
        //var currentImage = $(this);

        $('#imageList').find('.image-checkbox-checked').each(function() {
            deletedImages.push($(this).children('.selectorImage').data('id'));
        });

        console.log("DELETED IMAGES: " + deletedImages);

        $.ajax({
            url: "/admin/image-picker-delete-images",
            method: 'POST',
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').content,
                deleted_images: deletedImages
            },
            success: function(response) {
                resetImagePickerCache();
                $("#imageList").html("");
                refreshImagePickerImageList(true);

                console.log(response);

            },
            error: function(xhr, status, error) {
                console.error("AJAX hiba:", status, error);
                let errorMessage = "Hiba történt a képek törlése közben. Próbáld újra később. Törölendő képek: " + deletedImages;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    });
});

/**
 * Image Picker Modal megnyitása
 * ANCHOR[id="open-image-picker-modal"] Image Picker Modal megnyitása
 */
export function openImagePickerModal(eventSource, eventSourceId = null) {
    $('#manageAlbumImages').empty();

    var lastEditedPostId = localStorage.getItem("lastEditedPostId");
    var currentEditedPostId = $('#post_id').data('id');
    var detectedData = detectGalleryShortcode();

    setVar('detectedData', detectedData);

    const { action, mediaArray } =  detectedData;

    setVar('imagePickerMode', eventSource);
    setVar('imagePickerTargetId', eventSourceId);

    if (['editor', 'insert', 'insert-input'].includes(eventSource) == false) {
        return false;
    }

    setVar('mediaArray', mediaArray);

    const order = window.currentGalleryDiv ? window.currentGalleryDiv.dataset.order : 'asc';
    const $insertAlbum = $('#insertAlbum');
    const isDisabled = action === 'insert' || (mediaArray && mediaArray.length === 0);

    $insertAlbum
        .toggleClass('bg-blue-500 hover:bg-blue-400', !isDisabled)
        .toggleClass('bg-gray-500', isDisabled)
        .attr('disabled', isDisabled)
        .data('action', action)
        .html($insertAlbum.data(action + '-text'));

    $("#imagePickerModal").removeClass('hidden');
    $("body").addClass('overflow-y-hidden');

    $("#editImageAlbum, #tabEditAlbum").toggleClass('hidden', ['insert-input'].includes(eventSource));

    $('#tabImages').trigger('click');

    if (! $("#imageList").html()) {
        //true param, ha a modal tartalmat mindíg újra kell tölteni dev.
        console.log("Refresh Image List");
        refreshImagePickerImageList(false)
            .then(() => {
                console.log("Az Ajax hívás befejeződött");

                updateImageClasses(getVar('isCropped'));

    if (eventSource == 'editor' && action == 'update') {
        console.log("tabEditAlbum");
        $('#tabEditAlbum').trigger('click');

        refreshAlbumImages(mediaArray, action);
    } else {
        $('#tabImages').trigger('click');
    }
            })
            .catch(error => {
                console.error("Hiba történt:", error);
            });

        console.log("Refresh Image List - VÉGE");
    }   else {
        updateImageClasses(getVar('isCropped'));

        if (eventSource == 'editor' && action == 'update') {
            console.log("tabEditAlbum");
            $('#tabEditAlbum').trigger('click');

            refreshAlbumImages(mediaArray, action);
        } else {
            $('#tabImages').trigger('click');
        }
    }


};


/**
 * Reset Image Picker Cache
 * ANCHOR Reset Image Picker Cache (exported)
 * Importálva az uppy.js-ben
 */
 export function resetImagePickerCache() {
    setVar('mediaArray', []);
    $("#imageList").html("");
    localStorage.removeItem("imageListHtml");
    //localStorage.removeItem("lastEditedPostId");
    localStorage.removeItem("modalImageSize");
    localStorage.removeItem("modalImageSizes");
}


/**
 * Refresh Image Picker Image List
 * ANCHOR Refresh Image Picker Image List
 */
export function refreshImagePickerImageList(forced = false) {
    return new Promise((resolve, reject) => {
        // Ellenőrizzük, hogy a jQuery elérhető-e
        if (typeof $ === 'undefined') {
            reject(new Error('jQuery is not available'));
            return;
        }

        let $imageList = $("#imageList");
        let storedImages = localStorage.getItem("imageListHtml");
        let lastEditedPostId = localStorage.getItem("lastEditedPostId");
        let modalImageSizes = JSON.parse(localStorage.getItem("modalImageSizes"));
        let currentEditedPostId = $('#post_id').data('id');

        // Ha van mentett adat, először azt töltjük be
        if (storedImages && currentEditedPostId == lastEditedPostId && forced == false) {
            console.log("storedImages - betöltése");
            setVar('modalImageSizes', modalImageSizes);
            $imageList.html(storedImages);
            console.log("🔹 Betöltve localStorage-ból!");
            $("#loader").hide();
            updateImageClasses(getVar('isCropped'));
            resolve();
            return;
        }

        console.log("Képek betöltése ajax-al.");

        // Ha nincs adat, akkor betöltjük a képeket ajax hívással
        $.ajax({
            url: "/admin/image-picker",
            method: "GET",
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').content,
                post_id: $("#post_id").data("id"),
            },
            success: function(response) {
                $("#loader").hide();


                console.log("AjaxKépekBetöltése Success Folyamat");
                let tempContainer = $("<div>").html(response.html);
                let imageWrappers = tempContainer.find("#imagePickerContent .selectorImageDiv");
                let checkImagesLoaded = imageWrappers.length;
                let checkedImages = 0;

                if (checkImagesLoaded === 0 && forced == false) {
                    $("#loader").hide();
                    resolve();
                    return;
                }

                setVar('modalImageSizes', response.modalImageSizes);
                localStorage.setItem("modalImageSizes", JSON.stringify(response.modalImageSizes));

                // Ellenőrizzük, hogy a képek valóban léteznek-e
                imageWrappers.each(function() {
                    let $wrapper = $(this);
                    let img = $wrapper.find("img");
                    let src = img.attr("src");
                    let testImg = new Image();
                    testImg.src = src;

                    testImg.onload = function() {
                        checkCompletion();
                    };

                    testImg.onerror = function() {
                        console.warn("❌ Nem létező kép kihagyva:", src);
                        $wrapper.remove();
                        checkCompletion();
                    };
                });

                function checkCompletion() {
                    checkedImages++;
                    console.log("checkedImages_n:" + checkedImages);
                    if (checkedImages === checkImagesLoaded) {
                        let finalHtml = tempContainer.html();
                        localStorage.setItem("imageListHtml", finalHtml);
                        localStorage.setItem("lastEditedPostId", currentEditedPostId);
                        console.log("🆕 Képek frissítve és mentve a localStorage-ba!");

                        $imageList.html(finalHtml);
                        trackImageLoading().then(resolve).catch(reject);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error("Ajax hiba:", error);
                reject(error);
            }
        });

        function trackImageLoading() {
            return new Promise((resolveTrack, rejectTrack) => {
                let images = $imageList.find("#imagePickerContent .selectorImageDiv img");
                let totalImages = images.length;
                let loadedImages = 0;

                if (totalImages === 0) {
                    $("#totalCount").text("0");
                    $("#loadedCount").text("0");
                    $("#loader").hide();
                    updateImageClasses(getVar('isCropped'));
                    resolveTrack();
                    return;
                }

                $("#totalCount").text(totalImages);

                images.each(function() {
                    let img = new Image();
                    let src = $(this).attr("src");
                    img.src = src;

                    img.onload = function() {
                        loadedImages++;
                        let percent = Math.round((loadedImages / totalImages) * 100);

                        $("#loadedCount").text(loadedImages);
                        $("#progressBar").css("width", percent + "%");

                        if (loadedImages === totalImages) {
                            $("#loader").fadeOut();
                            updateImageClasses(getVar('isCropped'));
                            resolveTrack();
                        }
                    };

                    img.onerror = function() {
                        console.warn("❌ Egy kép nem töltődött be:", src);
                        totalImages--;
                        $("#totalCount").text(totalImages);

                        if (totalImages === loadedImages) {
                            $("#loader").fadeOut();
                            updateImageClasses(getVar('isCropped'));
                            resolveTrack();
                        }
                    };
                });
            });
        }
    });
}


function parseShortcodeAttributes(str) {
    const result = {};
    const regex = /(\w[\w-]*)\(([^)]*)\)/g;
    let match;

    while ((match = regex.exec(str)) !== null) {
        const key = match[1];
        const value = match[2].trim();
        result[key] = value;
    }

    return result;
}


/**
 * Detect Gallery Shortcode
 * ANCHOR Detect Gallery Shortcode
 */
function detectGalleryShortcode() {
    let textarea = $(window.editor)[0];
    let cursorPosition = textarea.selectionStart;
    let textareaContent = textarea.value;
    let galleryRegex = /\[gallery\b[^\]]*ids\(([\d\s,]+)\)[^\]]*\]/g;
    let matches = [];
    let match;
    let currentGallery = null;
    let action = 'insert';
    let shortcode = null;

    while ((match = galleryRegex.exec(textareaContent)) !== null) {
        matches.push({
            start: match.index,
            end: match.index + match[0].length,
            ids: match[1],
            shortcode: match[0]
        });
    }

    for (let m of matches) {
        if (cursorPosition >= m.start && cursorPosition <= m.end) {
            currentGallery = m;
            break;
        }
    }

    let mediaArray = [];
    var attributes = {};

    if (currentGallery) {
        console.log("currentGallery:");
        console.log(currentGallery);

        shortcode = currentGallery.shortcode;
        attributes = parseShortcodeAttributes(shortcode);

        let cleanedIds = currentGallery.ids.replace(/\s/g, '');

        mediaArray = cleanedIds.split(',');
        action = 'update';
    }

    return {action, mediaArray, shortcode, attributes};
}

/**
 * Replace Shortcode Atts
 * ANCHOR Replace Shortcode Atts
 */
function replaceShortcodeAtts(code, atts) {
    //var originalShortcode = getVar('detectedData').shortcode;
console.log("NEW DATA INPUT:");
console.log(code);
console.log(atts);
    // Reguláris kifejezés a rövidkód attribútumainak kinyerésére
    const attrRegex = /(\w+)=["']([^"']*)["']/g;

    // Objektum az attribútumok tárolására
    let attributes = {};

    // Attribútumok kinyerése a rövidkódból
    let match;
    while ((match = attrRegex.exec(code)) !== null) {
        const key = match[1];   // Attribútum neve (pl. "ids", "size", "class")
        const value = match[2]; // Attribútum értéke (pl. "884", "medium", "rounded-md shadow-sm")
        attributes[key] = value;
    }

    // Új attribútumok hozzáadása vagy felülírása
    for (const key in atts) {
        if (atts.hasOwnProperty(key)) {
            attributes[key] = atts[key];
        }
    }

    // Új rövidkód generálása
    let newShortcode = '[gallery';
    for (const key in attributes) {
        if (attributes.hasOwnProperty(key)) {
            newShortcode += ` ${key}(${attributes[key]})`;
        }
    }
    newShortcode += ']';

    return newShortcode;
}
// NOTE Szép vagy

/**
 * Refresh Album Images
 * ANCHOR Refresh Album Images
 */
export function refreshAlbumImages(mediaArray, action = "insert") {
    var modalImageSizes = getVar('modalImageSizes');

    if (!modalImageSizes || typeof modalImageSizes !== "object") {
        console.error("HIBA: modalImageSizes nem létezik vagy nem objektum:", modalImageSizes);
        return;
    }

    const albumImagesOldHtml = $("#manageAlbumImagesGrid").html();

    //$('#imagePickerContent .image-checkbox-checked').find('.js-checkbox-trigger').trigger('click');

    $("#manageAlbumImages").empty();

    let imageGrid = $(`<div id="manageAlbumImagesGrid">`).addClass("grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 p-2 sortable-grid");

    if (albumImagesOldHtml) {
        imageGrid.append(albumImagesOldHtml);
    }

    if (mediaArray) {
        mediaArray.forEach(function(imageId) {
            // 1. Eredeti elem megkeresése
            const $existingDiv = $('#imagePickerContent .selectorImage[data-id="' + imageId + '"]')
                .first()
                .closest('.selectorImageDiv');

            if ($existingDiv.length) {
                if (action == "insert") $existingDiv.find('.js-checkbox-trigger').trigger('click');

                // 3. Klónozás
                const $clonedDiv = $existingDiv.clone(false);
                Alpine.initTree($clonedDiv[0]);


                imageGrid.append($clonedDiv);
            }
        });

        $("#manageAlbumImages").append(imageGrid);

       // updateImageClasses(getVar('isCropped'));

        $('#imagePickerModal .image-checkbox').blur();

        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        // 🔹 Csak asztali eszközökön engedélyezzük a sortable funkciót
        if (!isMobile) {
            $(".sortable-grid").sortable({
                placeholder: "border-2 border-dashed border-gray-300 bg-gray-50 h-24",
                cursor: "move",
                update: function (event, ui) {
                    // 🔹 Sorrend lekérése a data-image-id alapján
                    const sortedIds = $(".sortable-grid .selectorImageDiv").map(function () {
                        return $(this).data("image-id");
                    }).get();

                    console.log("NEW ORDER: " + sortedIds.join(','));
                    $("#imageOrder").val(sortedIds.join(','));
                    console.log("Új sorrend mentése:", sortedIds);
                }
            }).disableSelection();
        } else {
            console.log("Mobil eszköz: a sortable funkció letiltva.");
        }
    } else {
        $("#manageAlbumImages").append(`<p>Még nincsenek kiválasztott képek`);
    }
}