function initFlashAlert($alertContainer) {
    const $alerts = $(".alert", $alertContainer);
    setTimeout(
        function(){
            $alerts.alert('close');
            },
        12000
    );
    $alerts.on('close.bs.alert', function() {
        const $parent = $(this).parent();
        $(this).on('closed.bs.alert', function() {
            $parent.remove();
        });
    });
}

initFlashAlert($("#flash-messages-container .alert-container"));