<?php echo js_tag('vendor/tiny_mce/tiny_mce'); ?>
<script type="text/javascript">
jQuery(window).load(function () {
  Omeka.wysiwyg({
    mode: 'specific_textareas',
    editor_selector: 'html-editor'
  });
});
</script>
<?php
// In config, the submit form is set apart.
$elements = $form->getElements();
foreach( $elements as $element) {
    echo $element;
}
?>
