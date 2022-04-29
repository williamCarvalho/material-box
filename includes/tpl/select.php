<select
    name="<?php echo $arguments['uid']; ?>"
    id="<?php echo $arguments['uid']; ?>">
    <?php foreach ($arguments['options'] as $key => $label) {
        printf('<option value="%s" %s>%s</option>', $key, selected($value, $key, false), $label);
    } ?>
</select>