$(document).ready(() => {
    $('.buy-btn').on('click', function(e){
        e.preventDefault();
        let productId = $(this).data('product');
        $.ajax({
            cache: false,
            method:"POST",
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

    $('.auth-form').on('submit', function(){
        e.preventDefault();
        $.ajax({
            method:"POST",
            cache: false,
            url: "order.php",
            data: {
                email: $('#email').val().trim()
            },
            success: function(data) {
                console.log(data);
                $('.js-success').text(data).show();
            }
        });
    });

    $('.reg-form').on('submit', function(){
        e.preventDefault();
        $.ajax({
            method:"POST",
            cache: false,
            url: "order.php",
            data: {
                email: $('#email').val().trim(),
                name: $('#name').val().trim()
            },
            success: function(data) {
                console.log(data);
                $('.js-success').text(data).show();
            }
        });
    });
});