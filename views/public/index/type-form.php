<h2><?php echo $type->alias; ?></h2>
<?php foreach ($type->getTypeElements() as $element) {
    echo $this->elementForm($element, $item);
} ?>
