    <div class="wrap">
        <h2>
            <?php echo $this->page_title; ?>
        </h2>

        <form method="post" action="options.php">
            <?php
                settings_fields('materialbox_fields');
                do_settings_sections($this->slug);
                submit_button();
            ?>
        </form>
    </div>