/**
 * WeCoop Eventi Admin JS
 */

jQuery(document).ready(function($) {
    
    // Tabs multilingua
    $('.wecoop-tab').on('click', function(e) {
        e.preventDefault();
        var lang = $(this).data('lang');
        
        $('.wecoop-tab').removeClass('active');
        $(this).addClass('active');
        
        $('.wecoop-tab-content').removeClass('active');
        $('#content-' + lang).addClass('active');
    });
    
    // Toggle evento online
    $('#evento-online-check').on('change', function() {
        if ($(this).is(':checked')) {
            $('#link-online-field').slideDown();
            $('#luogo-fisico-fields, #indirizzo-fields').slideUp();
            $('input[name="luogo"]').removeAttr('required');
        } else {
            $('#link-online-field').slideUp();
            $('#luogo-fisico-fields, #indirizzo-fields').slideDown();
            $('input[name="luogo"]').attr('required', 'required');
        }
    });
    
    // Toggle richiede iscrizione
    $('#richiede-iscrizione-check').on('change', function() {
        if ($(this).is(':checked')) {
            $('#posti-field, #prezzo-field').slideDown();
        } else {
            $('#posti-field, #prezzo-field').slideUp();
        }
    });
    
    var currentEventoId = null;
    
    /**
     * Cambio evento
     */
    $('#evento-select').on('change', function() {
        var eventoId = $(this).val();
        
        if (!eventoId) {
            $('#iscritti-container').hide();
            return;
        }
        
        currentEventoId = eventoId;
        loadIscritti(eventoId);
    });
    
    /**
     * Carica iscritti
     */
    function loadIscritti(eventoId) {
        $('#iscritti-list').html('<tr><td colspan="5">Caricamento...</td></tr>');
        $('#iscritti-container').show();
        
        $.ajax({
            url: wecoopEventiAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'wecoop_get_evento_iscritti',
                nonce: wecoopEventiAdmin.nonce,
                evento_id: eventoId
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    
                    // Info evento
                    $('#evento-title').text(data.evento.titolo);
                    
                    var infoHtml = '';
                    infoHtml += '<div><strong>Data:</strong> ' + data.evento.data + '</div>';
                    infoHtml += '<div><strong>Ora:</strong> ' + data.evento.ora + '</div>';
                    infoHtml += '<div><strong>Luogo:</strong> ' + data.evento.luogo + '</div>';
                    infoHtml += '<div><strong>Iscritti:</strong> ' + data.totale;
                    if (data.evento.posti > 0) {
                        infoHtml += ' / ' + data.evento.posti;
                    }
                    infoHtml += '</div>';
                    
                    $('#evento-info').html(infoHtml);
                    
                    // Lista iscritti
                    if (data.partecipanti.length === 0) {
                        $('#iscritti-list').html('<tr><td colspan="5" class="empty">Nessun iscritto</td></tr>');
                    } else {
                        var html = '';
                        data.partecipanti.forEach(function(p) {
                            html += '<tr>';
                            html += '<td>' + (p.nome || '—') + '</td>';
                            html += '<td>' + (p.email || '—') + '</td>';
                            html += '<td>' + (p.telefono || '—') + '</td>';
                            html += '<td>' + (p.note || '—') + '</td>';
                            html += '<td>' + (p.data_iscrizione || '—') + '</td>';
                            html += '</tr>';
                        });
                        $('#iscritti-list').html(html);
                    }
                }
            },
            error: function() {
                $('#iscritti-list').html('<tr><td colspan="5" style="color: red;">Errore nel caricamento</td></tr>');
            }
        });
    }
    
    /**
     * Export CSV
     */
    $('#export-iscritti').on('click', function() {
        if (!currentEventoId) return;
        
        var form = $('<form>', {
            action: wecoopEventiAdmin.ajaxurl,
            method: 'POST'
        });
        
        form.append($('<input>', {type: 'hidden', name: 'action', value: 'wecoop_export_iscritti'}));
        form.append($('<input>', {type: 'hidden', name: 'nonce', value: wecoopEventiAdmin.nonce}));
        form.append($('<input>', {type: 'hidden', name: 'evento_id', value: currentEventoId}));
        
        $('body').append(form);
        form.submit();
        form.remove();
    });
});
