<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

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
    $mainTitle = __('Contribution');
    $subsections = array_merge(array($mainTitle), $subsections);
    $displayTitle = implode(' | ', $subsections);
    $head = array('title' => $displayTitle,
            'bodyclass' => 'contribution',
            'content_class' => 'horizontal-nav');
    echo head($head);
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
 * Get a URL to the public contribution page.
 *
 * @param string $action Action to link to, main index if none.
 * @return string URL
 */
function contribution_contribute_url($actionName = null)
{
    $options = array();
    if (!empty($actionName)) {
        $options['action'] = $actionName;
    }
    return get_view()->url($options, 'contribution', array(), true);
}

/**
 * Get a link to the public contribution action page.
 *
 * @param Record|integer $contributedItem.
 * @param string $action Action to link to, main index if none.
 * @param string $linkText
 * @return string HTML
 */
function contribution_link_to($contributedItem = null, $action = null, $linkText = 'Contribute')
{
    $url = contribution_url($action, $contributedItem);
    return sprintf('<a href="%s">%s</a>', $url, $linkText);
}

/**
 * Get a URL to the public contribution action page.
 *
 * @param string $action Action to link to, main index if none.
 * @param Record|integer $contributedItem.
 * @return string URL
 */
function contribution_url($action = null, $contributedItem = null)
{
    $string = get_option('contribution_page_path');
    if (!empty($action)) {
        $string .= '/' . $action;
        if (!empty($contributedItem)) {
            $string .= '/';
            $string .= is_object($contributedItem) ? $contributedItem->id : (integer) $contributedItem;
        }
    }
    return url($string);
}
