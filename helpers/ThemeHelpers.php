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
    return get_view()->formSelect($name, $default, $attributes, $options);
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
    return get_view()->formSelect($name, $default, $attributes, $options);
}

function contribution_select_field_data_type($name, $default = '', $attributes = array())
{
    $options = get_db()->getTable('ContributionContributorField')->getDataTypes();
    $options = array('' => 'Select Field Size') + $options;
    return get_view()->formSelect($name, $default, $attributes, $options);
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
    echo head($head);
} 


/**
 * Check if the captcha is set up, display a message if not.
 */
function contribution_check_captcha()
{
    if (!Omeka_Captcha::isConfigured()) {
?>
    <p class="alert">You have not entered your <a href="http://recaptcha.net/">reCAPTCHA</a> API keys under <a href="<?php echo url('security#recaptcha_public_key'); ?>">security settings</a>. We recommend adding these keys, or the contribution form will be vulnerable to spam.</p>
<?php 
    }
}

/**
 * Get a URL to the public contribution page.
 *
 * @param string $action Action to link to, main index if none.
 * @return string URL
 */
function contribution_contribute_url($actionName = null)
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
    return get_view()->url($options, $route, array(), true);
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
    $url = contribution_contribute_url($actionName);
    return "<a href=\"$url\">$linkText</a>";
}

/**
 * Get the Contributor that added a given item to the site.
 *
 * @param Item $item
 * @return ContributionContributor
 */
function contribution_get_item_contributor($item = null)
{
    if (!$item) {
        $item = get_current_item();
    }

    $linkage = get_db()->getTable('ContributionContributedItem')->findByItem($item);

    if ($linkage) {
        return $linkage->Contributor;
    } else {
        return null;
    }
}

function contribution_is_item_public($item = null)
{
    if(!$item) {
        $item = get_current_item();
    }
    
    $linkage = get_db()->getTable('ContributionContributedItem')->findByItem($item);

    if ($linkage) {
        return $linkage->public;
    } else {
        return null;
    }
}

/**
 * Get metadata for a given contributor.
 *
 * @param string $propertyName
 * @param ContributionContributor $contributor
 * @return string
 */
function contributor($propertyName, $contributor = null)
{
    if (!$contributor) {
        $contributor = contribution_get_item_contributor($item);
    }
    switch ($propertyName) {
        case 'ID':
            $property = $contributor->id;
            break;
        case 'Name':
            $property = $contributor->name;
            break;
        case 'Email Address':
            $property = $contributor->email;
            break;
        default:
            $data = $contributor->getMetadata();
            if (array_key_exists($propertyName, $data)) {
                $property = $data[$propertyName];
            } else {
                $property = null;
            }
    }
    return html_escape($property);
}
