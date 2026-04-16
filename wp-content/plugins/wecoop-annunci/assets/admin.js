/* WeCoop Annunci - Admin JS */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Aggiorna preview costo quando cambiano i giorni
        var $giorni = $('#_annuncio_giorni_pubb');
        var freeDays = parseInt($giorni.data('free') || 3, 10);

        $giorni.on('input change', function() {
            var g = parseInt($(this).val(), 10) || 3;
            var extra = Math.max(0, g - freeDays);
            var costo = extra * 1.00;
            var $info = $('#wecoop-giorni-info');
            if (!$info.length) {
                $info = $('<small id="wecoop-giorni-info" style="color:#555;display:block;margin-top:4px"></small>');
                $giorni.after($info);
            }
            if (extra > 0) {
                $info.html('💳 ' + extra + ' giorni extra = <strong>€' + costo.toFixed(2) + '</strong> da pagare');
            } else {
                $info.html('✅ Nella fascia gratuita (max ' + freeDays + ' giorni)');
            }
        }).trigger('change');
    });

})(jQuery);
