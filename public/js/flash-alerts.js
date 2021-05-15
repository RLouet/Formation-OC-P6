function initFlashAlert($alerts) {
    setTimeout(
        function(){
            $alerts.alert("close");
            },
        12000
    );
    $alerts.on("close.bs.alert", function() {
        const $parent = $(this).parent();
        $(this).on("closed.bs.alert", function() {
            $parent.remove();
        });
    });
}

initFlashAlert($("#flash-messages-container .alert-container .alert"));

function showFlashMessage(type, message) {
    const $alertContainer = $("#flash-messages-container > .row");
    const $alertItem = $("<div class=\"col-auto alert-container\">\n" +
        "                                <div class=\"alert alert-" + type + " alert-dismissible fade show my-1\" role=\"alert\">\n" +
        "                                    " + message + "\n" +
        "                                    <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">\n" +
        "                                        <span aria-hidden=\"true\">&times;</span>\n" +
        "                                    </button>\n" +
        "                                </div>\n" +
        "                            </div>")
    ;
    $alertContainer.append($alertItem);
    initFlashAlert($($(".alert-container .alert"), $alertItem));
}