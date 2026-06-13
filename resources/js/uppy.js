import Uppy from '@uppy/core'
import Dashboard from '@uppy/dashboard'
import XHRUpload from '@uppy/xhr-upload'
import { resetImagePickerCache } from './image-picker/image-picker-modal.js';

// A rejtett input mezőből olvassuk ki az adatokat
const maxFileSize = document.querySelector('input[type="hidden"][data-maxFileSize]').getAttribute('data-maxFileSize') * 1024;
//const allowedFileTypes = document.querySelector('input[type="hidden"][data-allowedFileTypes]').getAttribute('data-allowedFileTypes').split(',');
// Beolvasás és pont hozzáadása a kiterjesztésekhez
let allowedFileTypes = document
    .querySelector('input[data-allowedFileTypes]')
    .getAttribute('data-allowedFileTypes')
    .split(',')
    .map(type => {
        type = type.trim();
        return type.includes('/') ? type : '.' + type;
    });


// Uppy.js inicializálása a beolvasott konfigurációkkal
const uppy = new Uppy({
    locale: {
        strings: {
            dropPasteFiles: 'Húzd ide a fájlokat vagy %{browse}',
            browse: 'tallózz'
        }
    },
    restrictions: {
        maxFileSize: parseInt(maxFileSize), // A konfigurációból beolvasott fájlméret
        allowedFileTypes: allowedFileTypes // A konfigurációból beolvasott MIME típusok
    }
})
.on('file-added', (file) => {
    console.log('Fájlnév:', file.name);
    console.log('MIME-típus:', file.type);
})

.use(Dashboard, {
    inline: true,
    target: '#uppy-dashboard',
    showProgressDetails: true,
    proudlyDisplayPoweredByUppy: false,
    note: "Szia User!",
    minHeight: 300,  // Minimum magasság
    maxHeight: 600,  // Maximális magasság, ha sok fájl van
    doneButtonHandler: () => {
        window.closeUploadModal();
    },
})
.use(XHRUpload, {
    endpoint: '/admin/upload',
    formData: true,
    fieldName: 'file',
    limit: 1,
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
    },
    validateStatus: (status) => {
        return status >= 200 && status < 500; // 422 ne legyen hálózati hiba
    },
    onAfterResponse(response) {
        if (response.status !== 422) return;

        let json;
        try {
            json = JSON.parse(response.responseText);
        } catch (err) {
            console.error('JSON parse error:', response.responseText);
            throw new Error('Hibás szerver válasz');
        }

        if (json.errors?.file) {
            return Promise.reject(new Error(json.errors.file[0])); // Csak az első hibaüzenetet adjuk vissza
        }
    },
    getResponseData: (responseText, response) => {
        try {
            return JSON.parse(responseText);
        } catch (err) {
            return { success: false, message: 'Hibás válaszformátum' };
        }
    }
});

// POST ID beállítása minden fájlhoz
const postId = $('#post_id').data('id');
uppy.setMeta({ post_id: postId });

uppy.on('upload-error', (file, error, response) => {
  console.error('Szerver válasz:', error, response);

  if (error?.response?.body) {
      const serverError = error.response.body;

      uppy.info({
          message: serverError.message || 'Feltöltési hiba',
          details: serverError.errors
              ? Object.values(serverError.errors).flat().join(', ') // Minden hibaüzenetet összefűzünk
              : null,
          type: 'error',
          duration: 5000
      });
  } else {
      uppy.info({
          message: error.message || 'Hálózati hiba',
          type: 'error',
          duration: 5000
      });
  }
});

uppy.on('complete', () => {
    console.log("Upload Files - Complete");
});

// Háttér kattintás eseménykezelője
$('#_previewModal').click(function(e) {
    // Csak akkor zárjuk be, ha a kattintás a háttéren történt
    if ($(e.target).is('#previewModal')) {
        $('#previewModal').addClass('hidden');
    }
});

function resizeUppyContent() {
    if ( $('#uploadModal').hasClass('.hidden') == false ) {
        var height =  $('#uploadModalContent').height();
        $('#uploadModal .uppy-Dashboard-innerWrap').height(height-100);
    }
}

$(window).on('resize', function() { resizeUppyContent(); });

// Modal kezelés
window.openUploadModal = () => {
    resetImagePickerCache();

    document.getElementById('uploadModal').classList.remove('hidden');
    resizeUppyContent();
    uppy.getPlugin('Dashboard').openModal();
}

window.closeUploadModal = () => {
    document.getElementById('uploadModal').classList.add('hidden');
    uppy.getPlugin('Dashboard').closeModal();
    uppy.cancelAll();
    resetImagePickerCache();
}



// Overlay kattintás kezelése
document.getElementById('uploadModal').addEventListener('click', (e) => {
    if (
        !e.target.closest('.bg-white') //|| !e.target.closest('.uppy-Dashboard-inner')
    ) {
       // closeUploadModal();
    }
});

// Feltöltés közbeni görgetés a modal-ban
uppy.on('upload-progress', (file, progress) => {
    const dashboard = document.querySelector('#uppy-dashboard');
    if (progress.bytesUploaded && progress.bytesTotal) {
        const scrollHeight = dashboard.scrollHeight;
        const scrollTop = dashboard.scrollTop;
        const clientHeight = dashboard.clientHeight;
        const progressPercentage = (progress.bytesUploaded / progress.bytesTotal) * 100;

        // Ha a fájlok elég nagyok ahhoz, hogy szükséges legyen görgetni, folytatjuk a görgetést
        if (scrollTop + clientHeight < scrollHeight && progressPercentage === 100) {
            dashboard.scrollTop = scrollHeight; // Görgetés a legördülő elem aljára
        }
    }
});

// Feltöltés végén a modal tartalmának frissítése
uppy.on('complete', (result) => {
    document.getElementById('upload-info').innerHTML = `
        ${result.successful.length} fájl sikeresen feltöltve!<br>
        Hibák: ${result.failed.length}
        `
})
