/*global showFlashMessage, unblockBtn*/

$(document).ready(function() {
    const $switchRoleModal = $("#switchRoleModal");

    $switchRoleModal.on("show.bs.modal", function (e) {
        const $button = $(e.relatedTarget);
        const targetRole = $button.data("target-role");
        $(".user-username").html($button.data("user-username"));
        $(".target-role").html(targetRole);
        $("#userToSwitchField").val($button.data("user-id"));
        unblockBtn($("button[type='submit']", $(this)));
    });

    $("form", $switchRoleModal).submit(function (e){
        e.preventDefault();

        $.ajax({
            url: $(this).attr("action"),
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success(data) {
                if (data.success) {
                    const $userItem = $(".user-item.user-" + data.user.id);
                    switch ($.inArray("ROLE_ADMIN", data.user.role) >= 0) {
                        case true:
                            $(".user-role", $userItem).html("Admin");
                            $(".switch-role-btn", $userItem).data("target-role", "membre");
                            break;
                        case false:
                            $(".user-role", $userItem).html("Membre");
                            $(".switch-role-btn", $userItem).data("target-role", "administrateur");
                            break;
                    }

                    $switchRoleModal.modal("hide");
                    showFlashMessage("primary", "Le rôle de l'utilisateur a bien été modifié.");
                    return;
                }

                $switchRoleModal.modal("hide");
                showFlashMessage("danger", data.error);
            },
            error(e) {
                $switchRoleModal.modal("hide");
                showFlashMessage("danger", "Une erreur s'est produite.");
            }
        });
    });

    const $deleteUserModal = $("#deleteUserModal");

    $deleteUserModal.on("show.bs.modal", function (e) {
        const $button = $(e.relatedTarget);
        $(".user-username").html($button.data("user-username"));
        $("#userToDeleteField").val($button.data("user-id"));
    });

    $("form", $deleteUserModal).submit(function (e){
        e.preventDefault();

        $.ajax({
            url: $(this).attr("action"),
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success(data) {
                if (data.success) {
                    const $userItem = $(".user-item.user-" + data.user.id);

                    $userItem.remove();

                    $deleteUserModal.modal("hide");
                    showFlashMessage("primary", "L'utilisateur a bien été supprimé.");
                    return;
                }

                $deleteUserModal.modal("hide");
                showFlashMessage("danger", data.error);
            },
            error(e) {
                $deleteUserModal.modal("hide");
                showFlashMessage("danger", "Une erreur s'est produite.");
            }
        });
    });
});