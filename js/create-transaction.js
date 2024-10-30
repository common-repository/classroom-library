
jQuery(function($) {
// -------------------------------------------------------------------

    $('form[name="create-transaction"]').on('submit', function () {

        var form_data = $(this).serializeArray();

        // Here we add our nonce (The one we created on our functions.php. WordPress needs this code to verify if the request comes from a valid source.
        form_data.push( { "name" : "security", "value" : ajax_nonce } );
        console.log(form_data);

        // Here is the ajax petition.
        $.ajax({
            url : ajax_url, // Here goes our WordPress AJAX endpoint.
            type : 'post',
            data : form_data,
            success : function( response ) {
                // You can craft something here to handle the message return
                console.log(response);

                if (response.data.message && response.data.errors != 0) {

                    $('.publication-transaction-messages').empty();
                    $('.publication-transaction-messages').addClass('message-present');
                    $('.publication-transaction-messages').append(`
                        <p>`+response.data.message+`</p>
                    `);

                    $([document.documentElement, document.body]).animate({
                        scrollTop: $(".publication-transaction-messages").offset().top - 50
                    }, 1000);

                }

                if (response.data.errors == 0) {

                    location.href = location.pathname + '?success=true&message='+response.data.message.replace(/(<([^>]+)>)/gi, "");

                }
            },
            fail : function( err ) {
                // You can craft something here to handle an error if something goes wrong when doing the AJAX request.
                console.log( "There was an error: " + err );
            }
        });

        // This return prevents the submit event to refresh the page.
        return false;
    });

// -------------------------------------------------------------------

});
