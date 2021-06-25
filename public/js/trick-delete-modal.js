$(document).ready(function() {
    $("#trickDeleteModal button.delete-btn").on("click", function () {
        $(this).addClass("disabled");
        $(this).prop("disabled", true);
        $(this).parent("form").submit();
    });
});

