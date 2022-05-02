<select
    name="<?php echo esc_attr($arguments['uid']); ?>"
    id="<?php echo esc_attr($arguments['uid']); ?>">
    <?php foreach ($arguments['options'] as $key => $label) { ?>
        <option
            value="<?php echo esc_attr($key); ?>"
            <?php echo selected($value, $key, false); ?>>
            <?php echo esc_html($label); ?>
        </option>
    <?php } ?>
</select>