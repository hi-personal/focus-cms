//Resize TinyMCE editor function
export function registerResizeEditor(editor) {
    let sized = false;
    let docH = 0;
    let vh = window.innerHeight * 0.01;

    // Then we set the value in the --vh custom property to the root of the document
    //document.documentElement.style.setProperty('--vh', `${vh}px`);

    //window.addEventListener('resize', () => {
        // We execute the same script as before

    //});

    const textarea = document.getElementById('myeditor');
    const editorStatus = $('input[name="editor_status"');

    if(editorStatus.val() == 'focused') {
         setHeight("click");
            $("body").addClass("overflow-y-hidden");
            $("#exit-editor").removeClass("hidden").addClass("inline-flex");
            editorStatus.val('focused')
    }

    $(textarea).on("click", function(){
        if (sized == false) {
            //$('#detailsInputsOpen').trigger('click');
            setHeight("click");
            $("body").addClass("overflow-y-hidden");
            $("#exit-editor").removeClass("hidden").addClass("inline-flex");
            editorStatus.val('focused')
        }
    } );

    $(window).on("resize", function() {
        if (sized == true) {
            setHeight();
        }

        let vh = window.innerHeight * 0.01;
        //document.documentElement.style.setProperty('--vh', `${vh}px`);
    });

    function setHeight(action = null) {
        let  distance = $(textarea).offset().top;
        docH = $(window).height();
        docH = docH - 140;

        textarea.style.height = docH + 'px';

        var minus = $("#postEditNavContainer").height() > 50 ? 150 : 100
        $('html, body').animate({ scrollTop:  (distance - minus ) }, 100);

        if (action == "click") sized = true;
    }

    $("#exit-editor").on("click", function() {
        sized = false;
        $("body").removeClass("overflow-y-hidden");
        $("#exit-editor").addClass("hidden").addClass("inline-flex");
        $('html, body').animate({ scrollTop: 0 }, 100);
        editorStatus.val('unfocused')
    });

    // $(textarea).on("keydown", function(e) {
    //     if( e.key == "Escape" || e.keyCode == 27 ) {
    //         $("#exit-editor").trigger("click");
    //     }
    // });
}



