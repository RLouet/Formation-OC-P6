/*global showFlashMessage, currentUser*/
function nl2br (str) {
    if (typeof str === "undefined" || str === null) {
        return "";
    }
    const breakTag = "<br />";
    return (str + "").replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, "$1" + breakTag + "$2");
}

function generatePaginationItem(entity, data, userRoles) {
    let item = "";
    switch (entity) {
        case "trick":
            let managementBtns = "";
            if (data.author.id === currentUser || (userRoles && userRoles.includes("ROLE_ADMIN"))) {
                managementBtns = "<div class=\"card-footer\">\n" +
                    "                 <div class=\"row\">\n" +
                    "                     <div class=\"col-6 text-center edit\">\n" +
                    "                         <a href=\"" + window.location.origin + "/tricks/edit/" + data.slug + "\" title=\"Modifier le Trick\" class=\"edit-btn\"><em class=\"far fa-edit\"></em></a>\n" +
                    "                     </div>\n" +
                    "                     <div class=\"col-6 text-center delete\">\n" +
                    "                         <a href=\"#\" title=\"Supprimer les Trick\" class=\"delete-btn\" data-toggle=\"modal\" data-target=\"#trickDeleteModal\" data-name=\"" + data.name + "\" data-id=\"" + data.id + "\"><em class=\"far fa-trash-alt\"></em></a>\n" +
                    "                     </div>\n" +
                    "                 </div>\n" +
                    "            </div>\n"
                ;
            }
            item = "<article class=\"col mb-3 mb-md-4 trick-item trick-" + data.id + "\">\n" +
                "    <div class=\"card h-100\">\n" +
                "        <div class=\"item-header\">\n" +
                "            <a href=\"" + window.location.origin + "/tricks/details/" + data.slug + "\" title=\"Voir le trick\">\n" +
                "                <img src=\"" + data.heroUrl + "\" class=\"card-img-top\">\n" +
                "            </a>\n" +
                "        </div>\n" +
                "        <div class=\"card-body\">\n" +
                "            <h3 class=\"card-title mb-0\">" + data.name + "</h3>\n" +
                "        </div>\n" + managementBtns +
                "    </div>\n" +
                "</article>"
            ;
            break;
        case "message":
            let deleteMsgBtn = "";
            if (data.author.id === currentUser || (userRoles && userRoles.includes("ROLE_ADMIN"))) {
                deleteMsgBtn = "<div class=\"text-right delete-message-btn-container\">\n" +
                    "                 <button type=\"button\" class=\"btn btn-sm btn-danger\" data-toggle=\"modal\" data-target=\"#messageDeleteModal\" data-id=\"" + data.id + "\">Supprimer</button>\n" +
                    "            </div>\n"
                ;
            }
            const date = new Date(data.date);
            item = "<div class=\"row px-1 justify-content-center message-item trick-message-" + data.id + "\">\n" +
                "    <div class=\"col-2 col-md-1 text-center mt-4 pr-0 text-wrap message-author\">\n" +
                "        <img src=\"" + data.author.avatarUrl + "\" class=\"img-fluid rounded-circle border border-info d-block m-auto\"  width=\"100%\" alt=\"...\">\n" +
                "        <small class=\"text-break\">" + data.author.username + "</small>\n" +
                "    </div>\n" +
                "    <div class=\"col-10 col-md-11 col-xl-6 mt-3 message-content\">\n" +
                "        <div class=\"border rounded px-2\">\n" +
                "            <p class=\"mb-2 border-bottom\"><small class=\"text-muted\"><strong>Le " + date.toLocaleDateString() + " à " + date.toLocaleTimeString() + "</strong></small></p>\n" +
                "            <p>" + nl2br(data.content) + "</p>\n" + deleteMsgBtn +
                "        </div>\n" +
                "    </div>\n" +
                "</div>"
            ;
            break;
        case "user":
            const subscription = new Date(data.subscriptionDate);
            const role = data.roles.includes("ROLE_ADMIN")?"Admin":"Membre";
            const targetRole = data.roles.includes("ROLE_ADMIN")?"membre":"administrateur";
            const enabledClass = data.enabled?"":" table-dark";
            const enabledText = data.enabled?"":"<br><b>(Non activé)</b>";
            const changeRoleBtn = data.id === currentUser?"":"<br><a href=\"#\" role=\"button\" class=\"switch-role-btn\" data-toggle=\"modal\" data-target=\"#switchRoleModal\" title=\"Change le rôle\" data-user-id=\"" + data.id + "\" data-user-username=\"" + data.username + "\" data-target-role=\"" + targetRole + "\">Modifier</a>";
            const deleteBtn = data.id === currentUser?"":"<a href=\"#\" role=\"button\" data-toggle=\"modal\" data-target=\"#deleteUserModal\" title=\"Supprimer\" data-user-id=\"" + data.id + "\" data-user-username=\"" + data.username + "\">Supprimer</a>";
            item = "<tr class=\"user-item user-" + data.id + enabledClass + "\">\n" +
                "    <th scope=\"row\">" + data.id + "</th>\n" +
                "    <td>" + data.username + "</td>\n" +
                "    <td>" + data.email + "</td>\n" +
                "    <td>" + subscription.toLocaleDateString() + enabledText + "</td>\n" +
                "    <td>\n" +
                "        <span class=\"user-role\">" + role + "</span>\n" +
                "        " + changeRoleBtn + "\n" +
                "    </td>\n" +
                "    <td>" + deleteBtn +"</td>\n" +
                "</tr>"
            ;
            break;
    }
    return item;
}

$(document).ready(function() {

    $(".pagination-btn").on("click", function(e){
        const $button = $(this);
        const entity = $button.data("entity");
        const $target = $($button.data("target"));
        const parentId = $button.data("parent-id");

        $button.prop("disabled", true);
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
                    $target.append(generatePaginationItem(entity, itemData, data.userRoles));
                }
                $button.removeClass("disabled");
                $button.prop("disabled", false);
                $button.html("Voir plus");
            },
            error(e) {
                showFlashMessage("danger", "Une erreur s'est produite !!!!.");
                $button.removeClass("disabled");
                $button.prop("disabled", false);
                $button.html("Voir plus");
            }
        });
    });
});