<?php
// Include your header and other necessary files
get_header();
?>

<main class="container">
  <div class="page-layout">
    <section class="page-header">
      <h1><?php echo theme_translate('services.title'); ?></h1>
      <p><?php echo theme_translate('services.description'); ?></p>
    </section>

    <div class="services-page">
      <section id="training" class="service-section">
        <a href="<?php echo esc_url(home_url('/formazione')); ?>">
          <div class="icon-container">
            <i class="fa fa-chalkboard-teacher service-icon"></i> <!-- Icona per formazione -->
          </div>
          <div class="service-text">
            <h2><?php echo theme_translate("training.title"); ?></h2>
            <p><?php echo theme_translate("training.description"); ?></p>
          </div>
        </a>
      </section>

      <section id="document-drafting" class="service-section">
        <a href="<?php echo esc_url(home_url('/redazione-documentale')); ?>">
          <div class="icon-container">
            <i class="fa fa-file-alt service-icon"></i> <!-- Icona per redazione documentale -->
          </div>
          <div class="service-text">
            <h2><?php echo theme_translate("document_drafting.title"); ?></h2>
            <p><?php echo theme_translate("document_drafting.description"); ?></p>
          </div>
        </a>
      </section>

      <section id="health-surveillance" class="service-section">
        <a href="<?php echo esc_url(home_url('/sorveglianza-sanitaria')); ?>">
          <div class="icon-container">
            <i class="fa fa-user-md service-icon"></i> <!-- Icona per sorveglianza sanitaria -->
          </div>
          <div class="service-text">
            <h2><?php echo theme_translate("health_surveillance.title"); ?></h2>
            <p><?php echo theme_translate("health_surveillance.description"); ?></p>
          </div>
        </a>
      </section>

      <section id="business-management" class="service-section">
        <a href="<?php echo esc_url(home_url('/gestione-aziendale')); ?>">
          <div class="icon-container">
            <i class="fa fa-briefcase service-icon"></i> <!-- Icona per gestione aziendale -->
          </div>
          <div class="service-text">
            <h2><?php echo theme_translate("business_management.title"); ?></h2>
            <p><?php echo theme_translate("business_management.description"); ?></p>
          </div>
        </a>
      </section>
    </div>
  </div>
</main>

<?php
// Include your footer or closing files
get_footer();
?>
