<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

/**
 * Gets all available contribution types, displayed by their alias.
 *
 * @return array Array of ContributionType records
 */
function contribution_get_types()
{
    return get_db()->getTable('ContributionType')->findAll();
}

function contribution_get_contributor_fields()
{
    return get_db()->getTable('ContributionContributorField')->findAll();
}

/**
 * Returns HTML for select box for contribution types.
 *
 * @return string HTML for select element
 */
function contribution_select_type($props=array(), $value=null, $label=null)
{
    return _select_from_table('ContributionType', $props, $value, $label);
}

/**
 * Gets all ContributionTypeElements for the given ContributionType.
 *
 * @return array Array of ContributionTypeElement records.
 */
function contribution_get_elements_for_type($type)
{
    // Allow an id or an object to be passed
    if (is_int($type)) {
        $type = get_db()->getTable('ContributionType')->find($type);
    }
    
    return $type->getTypeElements();
}

/**
 * Get an HTML <select> of possible type elements for a type.
 *
 * @param ContributionType|integer $type
 * @param string $name Select element name.
 * @param string $default Default select option.
 * @param array $attributes Select element HTML attributes.
 * @return string HTML
 */
function contribution_select_element_for_type($type, $name, $default = '', $attributes = array())
{
    // Allow an id or an object to be passed
    if (is_int($type)) {
        $type = get_db()->getTable('ContributionType')->find($type);
    }

    $options = $type->getPossibleTypeElements();
    $options = array('' => 'Select an Element') + $options;
    return __v()->formSelect($name, $default, $attributes, $options);
}

/**
 * Get an HTML <select> of possible item types for a new type.
 *
 * @param <type> $name Select element name.
 * @param <type> $default Default select option.
 * @param <type> $attributes Select element HTML attributes.
 * @return string HTML
 */
function contribution_select_item_type($name, $default = '', $attributes = array())
{
    $options = get_db()->getTable('ContributionType')->getPossibleItemTypes();
    $options = array('' => 'Select an Item Type') + $options;
    return __v()->formSelect($name, $default, $attributes, $options);
}

/**
 * Print the header for the contribution admin pages.
 *
 * Creates a consistent navigation across the pages.
 *
 * @param array $subsections Array of names that specify the "path" to this page.
 * @return string
 */
function contribution_admin_header($subsections = array())
{
    $mainTitle = 'Contribution';
    $subsections = array_merge(array($mainTitle), $subsections);
    $displayTitle = implode(' | ', $subsections);
    $head = array('title' => $displayTitle,
              'bodyclass' => 'contribution',
              'content_class' => 'horizontal-nav');
    head($head); ?>
<h1><?php echo $displayTitle; ?></h1>
<ul id="section-nav" class="navigation">
<?php echo nav(array(
    'Start' => uri('contribution/index'),
    'Settings' => uri('contribution/settings'),
    'Types' => uri('contribution/types'),
    'Contributors' => uri('contribution/contributors')
    ));
?>
</ul>
<?php
    return $displayTitle;
}

/**
 * Get a link to the public contribution page.
 *
 * @param string $linkText
 * @param string $action Action to link to, main index if none.
 * @return string HTML
 */
function contribution_link_to_contribute($linkText = 'Contribute', $actionName = null)
{
    $path = get_option('contribution_page_path');
    if (empty($path)) {
        $route = 'contributionDefault';
        
    } else {
        $route = 'contributionCustom';
    }
    $options = array();
    if (!empty($actionName)) {
        $options['action'] = $actionName;
    }
    $url = __v()->url($options, $route, array(), true);

    return "<a href=\"$url\">$linkText</a>";
}
