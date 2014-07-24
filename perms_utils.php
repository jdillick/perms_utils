<?php
/**
 * @file
 * perms_utils.php
 *
 * Utility functions for managing a dynamic list of permissions for roles.
 */

namespace hfc\perms_utils;

if ( ! defined(ENVIRONMENT) ) {
  define(ENVIRONMENT, getenv('ENVTYPE'));
}
/**
 * Given a list of permissions, filter out permissions not present on the site.
 *
 * @param array $perms list of perms to filter
 */
function filter_inactive_permissions($perms = array()) {
  return array_intersect($perms, array_keys(user_permission_get_modules()));
}

/**
 * Just a stub / noop function.
 *
 * @param array $perms list of perms
 * @return array those same perms back at ya.
 */
function statics($perms = array()) {
  return $perms;
}

/**
 * Get all permissions currently assigned to a role.
 *
 * @param string $role role name
 * @return array of permissions
 */
function role($role) {
  $role_object = user_role_load_by_name($role);
  $perms = user_role_permissions(array($role_object->rid => 1));

  return array_keys(current($perms));
}

/**
 * Give all perms for one or more module
 */
function module_admin($modules = FALSE, $blacklist = array()) {
  if ( ! $modules ) return array();
  if ( ! is_array($modules) ) $modules = array($modules);

  $perms = array();
  foreach ( user_permission_get_modules() as $perm => $perm_module ) {
    if ( in_array($perm_module, $modules) && ! in_array($perm, $blacklist) ) {
      $perms[] = $perm;
    }
  }
  return $perms;
}


/**
 * Dynamic creation of permissions array based on content types
 * @todo  Longer description? Examples?
 * @param  string $content_type
 * @return array    Array of permissions
 */
function content($content_types = FALSE) {
  $all_existing_perms = array_keys(user_permission_get_modules());
  $perms = array();

  if ( ! $content_types ) {
    return array();
  }

  if ( ! is_array($content_types) ) {
    $content_type = array($content_types);
  }

  // Get all content perms
  foreach ( $all_existing_perms as $perm ) {
    $patterns = array('create', 'edit own', 'edit any', 'view own', 'view any');
    foreach ( $patterns as $pattern ) {
      if ( strpos($perm, $pattern) === 0 ) {
        $ctperms[] = $perm;
      }
    }
  }

  // Of content perms, gather ones that match my content
  foreach ( $content_types as $content_type ) {
    foreach ( $ctperms as $i => $perm ) {
      if ( strpos($perm, $content_type) !== FALSE ) {
        $perms[] = $perm;
      }
    }
  }
  return $perms;
}

/**
 * Dynamic creation of permissions array based on Taxonomy
 * @param  array $arguments (optional) changes behavior of vocabs.
 *
 *  To get a flat list of permissions for specific vocabularies,
 *  pass an arguments array like so:
 *
 *  $arguments = array(
 *    'edit' => array('vocab_machinename1','vocab_machinename2', 'vocab_machinename3',),
 *    'delete' => array('vocab_machinename3','vocab_machinename4',),
 *  );
 * @return array Array of permissions, by default associative by vocab machinename and type
 */
function vocabs($arguments = array()) {
  $vocabularies = taxonomy_vocabulary_load_multiple(FALSE);
  $vocab_perms = array();
  foreach ( $vocabularies as $vocab ) {
    $vocab_perms[$vocab->machine_name]['delete'] = $perms['administrator'][] = 'delete terms in ' . $vocab->vid;
    $vocab_perms[$vocab->machine_name]['edit'] = $perms['administrator'][] = 'edit terms in ' . $vocab->vid;
  }

  // if no arguments, give the complete list
  if ( ! $arguments ) {
    return $vocab_perms;
  }

  // if arguments are specified, gather all perms that match type and vocab machinename
  $permissions = array();
  foreach ( $vocab_perms as $vocab_machine_name => $types ) {
    foreach ( $types as $type => $perm ) {
      if ( isset($arguments[$type]) ) {
        if ( in_array($vocab_machine_name, $arguments[$type]) ) {
          $permissions[] = $perm;
        }
      }
    }
  }
  return $permissions;
}


/******************************************************************************
 * Begin Permissions Bundles (spanning multiple modules)
 ******************************************************************************/
/**
 * Core specific permissions
 * @param  string $access_level
 * @return array    Array of permissions
 */
function core($access_level) {
  $perms = array();
  switch ($access_level){
    case 'developer':
      // Path
      $perms[] = 'administer url aliases';

      // System
      $perms[] = 'administer site configuration';
      $perms[] = 'administer themes';
      $perms[] = 'access site in maintenance mode';
      $perms[] = 'access site reports';
      $perms[] = 'block IP addresses';

    case 'content editor':
      // System
      $perms[] = 'access administration pages';
      $perms[] = 'view the administration theme';

      // Path
      $perms[] = 'create url aliases';

      // Toolbar
      $perms[] = 'access toolbar';

      // Contextual
      $perms[] = 'access contextual links';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }

  return $perms;
}

/**
 * Non-specific permissions for nodes, blocks, taxonomies and menus
 * @param  string $access_level
 * @return array    Array of permissions
 */
function content_admin($access_level) {
  $perms = array();
  switch ($access_level){
    case 'developer':
      // Node
      $perms[] = 'bypass node access';
      $perms[] = 'administer content types';
      // No one should have delete for history or forensics
      // $perms[] = 'delete revisions';

      // Taxonomy
      $perms[] = 'administer taxonomy';

      // Block
      $perms[] = 'administer blocks';

      // Entity View Modes
      $perms[] = 'administer entity view modes';

      // Image
      $perms[] = 'administer image styles';

      // Menu Import
      $perms[] = 'import or export menu';

      // Menu Position
      $perms[] = 'administer menu positions';

      // Field Group
      $perms[] = 'administer fieldgroups';

      // PathAuto
      $perms[] = 'administer pathauto';
      $perms[] = 'administer pathauto';
      $perms[] = 'notify of path changes';

      $perms[] = 'edit file';

    case 'content editor':
      // File
      $perms[] = 'view file';

      // Node
      $perms[] = 'administer nodes';
      $perms[] = 'access content overview';
      $perms[] = 'view own unpublished content';
      $perms[] = 'view revisions';
      $perms[] = 'revert revisions';

      // Menu
      $perms[] = 'administer menu';

      // Meta Tag
      $perms[] = 'edit meta tags';

    case 'anonymous':
      $perms[] = 'access content';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }

  return $perms;
}


/**
 * Permissions for commerce and commerce related modules
 * @param  string $access_level
 * @return array    Array of permissions
 */
function commerce($access_level){
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'configure store';

      // Checkout
      $perms[] = 'administer checkout';

      // Customer
      $perms[] = 'administer customer profile types';
      $perms[] = 'administer commerce_customer_profile entities';
      $perms[] = 'create commerce_customer_profile entities';

      // Line Item
      $perms[] = 'administer line item types';

      // Order
      $perms[] = 'administer commerce_order entities';
      $perms[] = 'configure order settings';

      // Payment
      $perms[] = 'administer payment methods';

      // Product
      $perms[] = 'administer product types';
      $perms[] = 'administer commerce_product entities';

    case 'store admin':
      // Discount
      $perms[] = 'administer commerce discounts';

      // Flat Rate
      $perms[] = 'administer flat rate services';

      // Customer
      $perms[] = 'view any commerce_customer_profile entity';
      $perms[] = 'view any commerce_customer_profile entity of bundle billing';
      $perms[] = 'view any commerce_customer_profile entity of bundle shipping';

      $perms[] = 'administer line items'; // @todo Are these safe for order manager?
      $perms[] = 'administer payments'; // @todo Are these safe for order manager?

      // Product Pricing UI
      $perms[] = 'administer product pricing';

      // Shipping
      $perms[] = 'administer shipping';

      // Tax UI
      $perms[] = 'administer taxes';

      // Wishlist
      $perms[] = 'administer wishlists';

      // Files / Licenses
      $perms[] = 'bypass license control';

    case 'store order manager':
      // Order
      $perms[] = 'create commerce_order entities';
      $perms[] = 'view own commerce_order entities';
      $perms[] = 'view any commerce_order entity';
      $perms[] = 'create commerce_order entities of bundle commerce_order';
      $perms[] = 'view own commerce_order entities of bundle commerce_order';
      $perms[] = 'view any commerce_order entity of bundle commerce_order';

      // Files / Licenses
      $perms[] = 'administer licenses';

      // Payment
      $perms[] = 'view payments';
      $perms[] = 'create payments';
      $perms[] = 'update payments';
      $perms[] = 'delete payments';

    case 'content editor':
      $perms[] = 'create commerce_product entities';
      $perms[] = 'view own commerce_product entities';
      $perms[] = 'view any commerce_product entity';
      $perms[] = 'create commerce_product entities of bundle product';
      $perms[] = 'view own commerce_product entities of bundle product';
      $perms[] = 'view any commerce_product entity of bundle product';

    case 'authenticated user':
      // Customer
      $perms[] = 'view own commerce_customer_profile entities';
      $perms[] = 'create commerce_customer_profile entities of bundle billing';
      $perms[] = 'view own commerce_customer_profile entities of bundle billing';
      $perms[] = 'create commerce_customer_profile entities of bundle shipping';
      $perms[] = 'view own commerce_customer_profile entities of bundle shipping';

      // Wishlist
      $perms[] = 'view own wishlist';

      // Files / Licenses
      $perms[] = 'view own licenses';

    case 'anonymous':
      // Checkout
      $perms[] = 'access checkout';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }

  return $perms;
}

/**
 * Feeds and feeds related modules permissions
 * @param  string $access_level
 * @return array    Array of permissions
 */
function feeds($access_level) {
  $perms = array();
  switch ($access_level){
    case 'developer':
      // Feeds
      $perms[] = 'administer feeds';

      // Tamper
      $perms[] = 'administer feeds_tamper';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }

  return $perms;
}


function workbench($access_level) {
  $perms = array();
  switch ($access_level){

    case 'developer':
      $perms[] = 'administer workbench';

      // WB Moderation
      $perms[] = 'administer workbench moderation';
      $perms[] = 'bypass workbench moderation';

    case 'content editor':
      $perms[] = 'view all unpublished content';

      // WB Moderation
      $perms[] = 'view moderation history';
      $perms[] = 'view moderation messages';

      // @todo needs to be more dynamic based on what states are available
      // $perms[] = 'moderate content from draft to needs_review';
      // $perms[] = 'moderate content from needs_review to draft';
      // $perms[] = 'moderate content from needs_review to published';
      $perms[] = 'use workbench_moderation my drafts tab';
      $perms[] = 'use workbench_moderation needs review tab';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }
  return $perms;
}




/******************************************************************************
 * Begin Permissions by Module
 ******************************************************************************/
function comment($access_level){
  $perms = array();
  switch ($access_level){
    case 'developer':
    case 'content editor':
      $perms[] = 'administer comments';
      $perms[] = 'skip comment approval';
    case 'anonymous':
      $perms[] = 'access comments';
      $perms[] = 'post comments';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }

  return $perms;
}


function ds($access_level) {
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'admin_fields';
      $perms[] = 'admin_classes';
      $perms[] = 'admin_display_suite';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }

  return $perms;
}


function pci($access_level) {
  $perms = array();
  switch ($access_level){
    case 'store admin':
    case 'store order manager':
      $perms[] = 'decrypt payment card data';
      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }

  return $perms;
}


function features($access_level) {
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'administer features';
      $perms[] = 'manage features';
      $perms[] = 'generate features';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }

  return $perms;
}


function panels($access_level) {
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'use panels dashboard';
      $perms[] = 'view pane admin links';
      $perms[] = 'administer pane access';

      // Buggy :(
      // $perms[] = 'use panels in place editing';
      // $perms[] = 'change layouts in place editing';

      $perms[] = 'administer advanced pane settings';
      $perms[] = 'administer panels layouts';
      $perms[] = 'administer panels styles';
      $perms[] = 'use panels caching features';
      $perms[] = 'use panels locks';

      // Panels Mini
      $perms[] = 'create mini panels';
      $perms[] = 'administer mini panels';
      break;

    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }

  return $perms;
}


function rules($access_level){
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'administer rules';
      $perms[] = 'bypass rules access';
      $perms[] = 'access rules debug';
      break;

    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }

  return $perms;
}

function search($access_level) {
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'administer search';
      $perms[] = 'use advanced search';
    case 'content editor':
      $perms[] = 'search content';
      break;

    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }
  return $perms;
}


function shortcut($access_level) {
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'administer shortcuts';
      $perms[] = 'switch shortcut sets';

    case 'content editor':
      $perms[] = 'customize shortcut links';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }
  return $perms;
}


function mailsystem($access_level){
  $perms = array();
  switch ($access_level){
    case 'developer':
      // $perms[] = 'administer mailsystem';
      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }
  return $perms;
}


function metatag($access_level){
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'administer meta tags';

    case 'content editor':
      $perms[] = 'edit meta tags';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }
  return $perms;
}


function mimemail($access_level){
  $perms = array();
  switch ($access_level){
    case 'developer':
      // $perms[] = 'edit mimemail user settings';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }
  return $perms;
}


function services($access_level){
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'administer services';
      $perms[] = 'get any binary files';
      $perms[] = 'get own binary files';
      $perms[] = 'save file information';
      $perms[] = 'get a system variable';
      $perms[] = 'set a system variable';
      $perms[] = 'perform unlimited index queries';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }
  return $perms;
}


function taxonomy_csv($access_level) {
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'import taxonomy by csv';
      $perms[] = 'export taxonomy by csv';
      break;

    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }
  return $perms;
}

function views($access_level) {
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'administer views';
      $perms[] = 'access all views';

    break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }
  return $perms;
}

function webform($access_level) {
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'access all webform results';
      $perms[] = 'edit all webform submissions';
      // No one should have delete for history or forensics
      // $perms[] = 'delete all webform submissions';
      // $perms[] = 'delete own webform submissions';
    case 'content editor':
      $perms[] = 'access own webform results';
      $perms[] = 'access own webform submissions';
      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }

  return $perms;
}


function xmlsitemap($access_level){
  $perms = array();
  switch ($access_level){
    case 'developer':
      $perms[] = 'administer xmlsitemap';

      break;
    default:
      drupal_set_message(t('@level not valid for @func', array(
        '@level' => $access_level,
        '@func' => __FUNCTION__,
        )
      ), 'error');
  }
  return $perms;
}

function is_production() {
  return ENVIRONMENT && in_array(ENVIRONMENT, array('production', 'staging'));
}
