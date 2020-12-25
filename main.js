$(document).ready(() => {
    $('.buy-btn').on('click', function(e){
        e.preventDefault();
        let productId = $(this).data('product');
        $.ajax({
            cache: false,
            url: "order.php",
            data: {
                productId: productId,
                name: 'order'
            },
            success: function(data) {
                console.log(data);
                $('.js-success').text(data).show();
            }
        });
    });
});