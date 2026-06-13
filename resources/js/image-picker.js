import * as updateClasses from './image-picker/update-classes.js';

import * as imagePickerModal from './image-picker/image-picker-modal.js';
import * as imagePreviewModal from './image-picker/image-preview-modal.js';

import * as filePickerModal from './file-picker/file-picker-modal.js';
import * as filePreviewModal from './file-picker/file-preview-modal.js';


let tabsContainer = document.querySelector("#edit-album-tabs");
let vars = {
    isCropped: false,
    currentPreviewImageId: null,
    currentPreviewFileId: null,
    currentPreviewImageSize: "thumbnail",
    modalImageSizes: {},
    mediaArray: [],
    detectedData: {},
    imagePickerMode: null,
    imagePickerTargetId: null,
};


export function setVar(key, val) {
    if (vars.hasOwnProperty(key)) {
        vars[key] = val;
    } else {
        console.error(`A(z) ${key} változó nem létezik.`);
    }
}

export function getVar(key) {
    if (vars.hasOwnProperty(key)) {
        return vars[key];
    } else {
        console.error(`A(z) ${key} változó nem létezik.`);
        return undefined;
    }
}


document.addEventListener('alpine:initialized', () => {
    document.addEventListener("DOMContentLoaded", function () {
        var isCropped = Cookies.get('croppedEffect');

        if (isCropped === undefined) {
            isCropped = 'true';
            Cookies.set('croppedEffect', isCropped, { expires: 7 });
        }

        isCropped = isCropped === 'true';

        $('#cropToggle').prop('checked', isCropped);

        updateClasses.updateImageClasses(isCropped);

        setVar('isCropped', isCropped);

        $('#imagePickerModal').on('change', '#cropToggle', function () {
            const isChecked = $(this).is(':checked');
            isCropped = isChecked;
            Cookies.set('croppedEffect', isChecked, { expires: 7 });

            setVar('isCropped', isCropped);
            updateClasses.updateImageClasses(isChecked);
        });
    });
});
