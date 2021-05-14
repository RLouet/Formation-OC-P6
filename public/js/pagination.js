$(document).ready(function() {
    function nl2br (str) {
        if (typeof str === "undefined" || str === null) {
            return "";
        }
        const breakTag = "<br />";
        return (str + "").replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, "$1" + breakTag + "$2");
    }

    function generatePaginationItem(entity, data) {
        let item = "";
        switch (entity) {
            case "trick":
                item = "<article class=\"col mb-3 mb-md-4 trick-item\">\n" +
                    "    <div class=\"card h-100\">\n" +
                    "        <div class=\"item-header\">\n" +
                    "            <a href=\"" + window.location.origin + "/tricks/" + data.id + "\" title=\"Voir le trick\">\n" +
                    "                <img src=\"" + data.heroUrl + "\" class=\"card-img-top\">\n" +
                    "            </a>\n" +
                    "        </div>\n" +
                    "        <div class=\"card-body\">\n" +
                    "            <h3 class=\"card-title mb-0\">" + data.name + "</h3>\n" +
                    "        </div>\n" +
                    "        <div class=\"card-footer\">\n" +
                    "            <div class=\"row\">\n" +
                    "                <div class=\"col-6 text-center edit\">\n" +
                    "                    <a href=\"#\" title=\"Modifier le Trick\" class=\"edit-btn\"><em class=\"far fa-edit\"></em></a>\n" +
                    "                </div>\n" +
                    "                <div class=\"col-6 text-center delete\">\n" +
                    "                    <a href=\"#\" title=\"Supprimer les Trick\" class=\"delete-btn\"><em class=\"far fa-trash-alt\"></em></a>\n" +
                    "                </div>\n" +
                    "            </div>\n" +
                    "\n" +
                    "        </div>\n" +
                    "    </div>\n" +
                    "</article>"
                ;
                break;
            case "message":
                const date = new Date(data.date);
                item = "<div class=\"row px-1 justify-content-center message-item\">\n" +
                    "    <div class=\"col-2 col-md-1 text-center mt-4 pr-0 text-wrap message-author\">\n" +
                    "        <img src=\"/imgs/avatar.png\" class=\"img-fluid rounded-circle border border-info d-block m-auto\"  width=\"100%\" alt=\"...\">\n" +
                    "        <small class=\"text-break\">" + data.author.username + "</small>\n" +
                    "    </div>\n" +
                    "    <div class=\"col-10 col-md-11 col-xl-6 mt-3 message-content\">\n" +
                    "        <div class=\"border rounded px-2\">\n" +
                    "            <p class=\"mb-2 border-bottom\"><small class=\"text-muted\"><strong>Le " + date.toLocaleDateString() + " à " + date.toLocaleTimeString() + "</strong></small></p>\n" +
                    "            <p>" + nl2br(data.content) + "</p>\n" +
                    "        </div>\n" +
                    "    </div>\n" +
                    "</div>"
                ;
                break;
        }
        return item;
    }

    $(".pagination-btn").on("click", function(e){
        const $button = $(this);
        const entity = $button.data("entity");
        const $target = $($button.data("target"));
        const parentId = $button.data("parent-id");

        $button.addClass("disabled");
        $button.html("<span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span> Chargement...");

        let offset = $("." + entity + "-item", $target).length;
        $.ajax({
            url: window.location.origin + "/ajax/load" + entity + "s",
            method: "POST",
            data: {
                offset,
                parentId
            },
            dataType: "json",
            success(data) {
                if (data.end) {
                    $button.remove();
                }
                for (const itemData of data.itemsData) {
                    $target.append(generatePaginationItem(entity, itemData));
                }
                $button.removeClass("disabled");
                $button.html("Voir plus");
            },
            error(e) {
                showFlashMessage("danger", "Une erreur s'est produite.");
                $button.removeClass("disabled");
                $button.html("Voir plus");
            }
        });
    });
});