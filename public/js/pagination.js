$(document).ready(function() {
    $(".pagination-btn").on("click", function(e){
        const $button = $(this);
        const entity = $button.data("entity");
        const $target = $($button.data("target"));

        $button.addClass("disabled");
        $button.html("<span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span> Chargement...");

        let offset = $("." + entity.toLowerCase() + "-item", $target).length;
        $.ajax({
            url: window.location.origin + "/ajax/load" + entity + "s",
            method: "POST",
            data: {
                offset,
            },
            dataType: "json",
            success(data) {
                if (data.end) {
                    $button.remove();
                }
                for (const item of data.itemsHtml) {
                    $target.append(item);
                }
                $button.removeClass("disabled");
                $button.html("Voir plus");
            },
            error(e) {
                alert("error");
                //showFlashMessage("danger", "Une erreur s'est produite.");
                $button.removeClass("disabled");
                $button.html("Voir plus");
            }
        });

    });
});