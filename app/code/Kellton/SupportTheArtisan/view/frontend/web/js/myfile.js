require(
        [
            'jquery',
            'Magento_Ui/js/modal/modal'
        ],
        function(
            $,
            modal
        ) {
            var options = {
                type: 'popup',
                responsive: true,
            };

            var popup = modal(options, $('#popup-modal'));
            $("#termscondition").on('click',function(){
                $("#popup-modal").modal("openModal");
            });
            $("#termscondition1").on('click',function(){
                $("#popup-modal").modal("openModal");
            });
        }
    );