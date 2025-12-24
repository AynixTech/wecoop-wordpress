
        jQuery(document).ready(function($) {
            var mediaUploader;
            $('#upload_gallery_button').click(function(e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media({
                    title: 'Seleziona Immagini',
                    button: { text: 'Aggiungi alla Galleria' },
                    multiple: true
                });

                mediaUploader.on('select', function() {
                    var selection = mediaUploader.state().get('selection');
                    var ids = [];
                    var preview = $('#portfolio_gallery_preview');
                    preview.html('');
                    selection.each(function(attachment) {
                        ids.push(attachment.id);
                        preview.append('<div class="gallery-image" style="margin:5px;">' + '<img src="' + attachment.attributes.url + '" width="75"></div>');
                    });
                    $('#portfolio_gallery').val(ids.join(','));
                });

                mediaUploader.open();
            });
        });
    