import {setVar, getVar} from '../image-picker.js';

//Update image classes
export function updateImageClasses(isCropped) {
    $('#imagePickerModal .selectorImageDiv')
        .toggleClass('flex', !isCropped);
    $('#imagePickerModal .selectorImageDiv label')
        .toggleClass('items-center', !isCropped)
        .toggleClass('aspect-square flex', isCropped);
    $('#imagePickerModal .selectorImage').toggleClass('min-w-full min-h-full object-cover object-center', isCropped);
}

