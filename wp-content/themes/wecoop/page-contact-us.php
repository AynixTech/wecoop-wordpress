<?php
/*
Template Name: Contact Page
*/
get_header(); 
?>

<div class="container contact-page">
    <h1><?php the_title(); ?></h1>

    <div class="contact-content">
            <div class="contact-form">
            <?php echo do_shortcode('[contact-form-7 id="ab9a966" title="Modulo di contatto 1"]'); ?>
            </div>

            <div class="contact-info">
                <h2>Contattaci</h2>
               
                <p><strong>Sede legale:</strong> WeCoop - Associazione di Promozione Sociale - Via Benefattori dell’Ospedale, 3 - 20159 Milano (MI)</p>
                <p><strong>Email:</strong> info@wecoop.org</p>
                <p><strong>Telefono:</strong>+39 334 139 0175</p>
                <h2>Info</h2>
                <p class="description">WeCoop</p>
                <p class="description">PIVA 97977210158</p>
            </div>
    </div>
    
</div>

<?php get_footer(); ?>
