$(document).ready(function() {
    if (location.hash === "#login") {
        $("#loginModal").modal("show");
    }
    if (location.hash === "") {
        const uri = window.location.toString();

        if (uri.indexOf("#") > 0) {
            const clean_uri = uri.substring(0,
                uri.indexOf("#"));

            window.history.replaceState({},
                document.title, clean_uri);
        }
    }
});