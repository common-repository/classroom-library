
jQuery(function($) {
    // -------------------------------------------------------------------

    setTimeout(function () {

        $('.post-new-php.post-type-mbcl_publication .edit-post-visual-editor__post-title-wrapper').before(`<div>
            <form id="publication-lookup" onsubmit="event.preventDefault();">
                <input type="text" id="barcode" placeholder="Enter barcode/ISBN" autocomplete="off">
                <button class="button" id="publication-lookup-submit" type="submit">Find</button>
            </form>
        </div>`);

        $('#barcode').focus();
    }, 1000);




    function lookup_mbcl_publication(){

        $('#barcode').prop('disabled', true);

        var barcode = $('#barcode').val();
        var lookup_url = 'https://openlibrary.org/api/books?bibkeys=ISBN:'+barcode+'&format=json&jscmd=data';

        $.ajax({
            type : "get",
            dataType : "json",
            url : lookup_url,
            success: function(data) {

                var returned_publication = data[Object.keys(data)[0]];
                console.log( returned_publication );
                $('#barcode').prop('disabled', false);

                if ( returned_publication != undefined ) {

                    wp.data.dispatch( 'core/editor' ).editPost( { title: returned_publication.title } )

                    // set barcode
                    $('#mbcl_publication_barcode').val(barcode);

                    // set main author
                    var author_name = returned_publication.authors[0].name.split(" ");
                    $('#mbcl_publication_author_first_name').val(author_name[0]);
                    $('#mbcl_publication_author_last_name').val(author_name[1]);

                    // set cover
                    // if (returned_publication.cover.large !== undefined) {
                    if ("cover" in returned_publication) {
                        $('#mbcl_publication_cover_image_url').val(returned_publication.cover.large);
                    }

                    // set more info link
                    $('#mbcl_publication_openlibrary_key').val(returned_publication.key);

                    // get "works" ID
                    $.ajax({
                        type: "get",
                        dataType: "json",
                        url: "https://openlibrary.org"+returned_publication.key+".json",
                        success: function (data) {

                            console.log(data);

                            // get description from "works" ID
                            $.ajax({
                                type: "get",
                                dataType: "json",
                                url: "https://openlibrary.org" + data.works[0].key + ".json",
                                success: function (data) {

                                    console.log(data);

                                    var publication_description = data.description;
                                    if ( typeof publication_description === 'object' && "value" in publication_description ) {
                                        publication_description = publication_description.value;
                                    }

                                    wp.data.dispatch( 'core/editor' ).resetBlocks( wp.blocks.parse( publication_description ) );
                                }
                            });
                        }
                    });

                } else {

                    alert("Publication not found.");

                }

            }
        });
    }

    $(document).delegate('#publication-lookup-submit', 'click keyUp', function(e){
        e.preventDefault();
        lookup_mbcl_publication();
    });

    // -------------------------------------------------------------------

});
