<aside class="sidebar">
    <?php if (is_active_sidebar('sidebar-1')) : ?>
        <?php dynamic_sidebar('sidebar-1'); ?>
    <?php else : ?>
        <p>Aggiungi widget alla sidebar.</p>
    <?php endif; ?>
</aside>
