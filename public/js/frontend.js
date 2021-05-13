$(document).ready(function() {
    const $LOGIN_MODAL = $("#loginModal");

    function removeHashGet(value) {
        const uri = window.location.toString();
        if (uri.indexOf(value) > 0) {
            const CLEAN_URI = uri.substring(
                0,
                uri.indexOf(value)
            );

            window.history.replaceState(
                {},
                document.title,
                CLEAN_URI
            );
        }
    }

    if (location.hash !== "#login") {
        removeHashGet("?target=");
    }
    if (location.hash === "#login") {
        removeHashGet("#");
        $LOGIN_MODAL.modal("show");
    }
    if (location.hash === "") {
        removeHashGet("#");
    }
    window.onhashchange = function(){
        if (location.hash === "") {
            removeHashGet("#");
        }
    };

    $LOGIN_MODAL.on("hide.bs.modal", function(e){
        removeHashGet("?target=");
        if (location.hash === "login") {
            removeHashGet("#");
        }
    });

});