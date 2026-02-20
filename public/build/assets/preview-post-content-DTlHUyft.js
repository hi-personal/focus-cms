document.addEventListener("DOMContentLoaded",function(){$(".previewButton").on("click",function(){$("#title").val();const e=$("#myeditor").val();$("#previewModal").removeClass("hidden"),$("body").addClass("overflow-hidden"),$("#previewContent").html(`
            <div class="flex flex-col items-center justify-center py-10">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-600"></i>
                <p class="mt-4 text-gray-700 text-lg font-semibold">Előnézet generálása...</p>
            </div>
        `);var t=$("#post_id").data("id"),o=$("#post_type_name").data("post-type-name");$.ajax({url:`/admin/post-type/${o}s/preview/save-temp`,method:"POST",data:{_token:document.querySelector('meta[name="csrf-token"]').content,content:JSON.stringify({content:e}),id:t},success:function(n){$("#previewContent").html(`
                    <iframe
                        src="/admin/post-type/${o}s/preview?token=${n}&id=${t}"
                        class="w-full h-full border-none"
                    ></iframe>
                `)},error:function(){$("#previewContent").html(`
                    <div class="text-red-600 text-center py-6">
                        <i class="fas fa-exclamation-triangle text-3xl"></i>
                        <p class="mt-2 font-semibold">Hiba történt az előnézet betöltése közben.</p>
                    </div>
                `)}})}),$(".closeModal").click(function(e){e.preventDefault(),e.stopPropagation(),$("#previewModal").addClass("hidden"),$("body").removeClass("overflow-hidden"),window.toggleBodyOverflow()}),$("#previewModal").click(function(e){$(e.target).is("#previewModal")&&($("#previewModal").addClass("hidden"),$("body").removeClass("overflow-hidden"))}),$(document).on("keydown",function(e){e.keyCode===27&&setTimeout(()=>{$("#previewModal").addClass("hidden"),$("body").removeClass("overflow-hidden")},40)})});
