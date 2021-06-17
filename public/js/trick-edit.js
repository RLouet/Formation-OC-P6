$(document).ready(function() {
    $("#trick_name").keyup(function () {
        $(".trick-name").text($(this).val());
    })
});