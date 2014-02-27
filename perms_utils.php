<?php

namespace hfc\perms_utils;

/**
 * Given a list of permissions, filter out permissions not present on the site.
 *
 * @param array $perms list of perms to filter
 */
function filter_inactive_permissions($perms = array()) {
  return array_intersect($perms, array_keys(user_permission_get_modules()));
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
function content($content_type = FALSE) {
  $all_existing_perms = array_keys(user_permission_get_modules());
  $perms = array();
  foreach ( $all_existing_perms as $perm ) {
    $patterns = array('create', 'edit own', 'edit any', 'view own', 'view any');
    foreach ( $patterns as $pattern ) {
      if ( strpos($perm, $pattern) === 0 ) $perms[] = $perm;
    }
  }

  if ( $content_type ) {
    foreach ( $perms as $i => $perm ) {
      if ( strpos($perm, $content_type) === FALSE ) unset($perms[$i]);
    }
  }
  return $perms;
}

/**
 * Dynamic creation of permissions array based on Taxonomy
 * @todo  Longer description? Examples?
 * @param  string $content_type
 * @return array    Array of permissions
 */
function vocabs() {
  $vocabularies = taxonomy_vocabulary_load_multiple(FALSE);
  $vocab_perms = array();
  foreach ( $vocabularies as $vocab ) {
    $vocab_perms[$vocab->machine_name]['delete'] = $perms['administrator'][] = 'delete terms in ' . $vocab->vid;
    $vocab_perms[$vocab->machine_name]['edit'] = $perms['administrator'][] = 'edit terms in ' . $vocab->vid;
  }

  // example vocabulary perm
  // $perms['content editor'][] = $vocab_perms['product_formats']['edit']; // Edit Terms in Product Formats

  return $vocab_perms;
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


    case 'content editor':
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

    case 'store order manager':
      // Order
      $perms[] = 'create commerce_order entities';
      $perms[] = 'view own commerce_order entities';
      $perms[] = 'view any commerce_order entity';
      $perms[] = 'create commerce_order entities of bundle commerce_order';
      $perms[] = 'view own commerce_order entities of bundle commerce_order';
      $perms[] = 'view any commerce_order entity of bundle commerce_order';

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
      // Workbench tab is crap
      // $perms[] = 'access workbench';

      // WB Moderation
      $perms[] = 'administer workbench moderation';
      $perms[] = 'bypass workbench moderation';
      // $perms[] = 'use workbench_moderation my drafts tab';
      // $perms[] = 'use workbench_moderation needs review tab';

    case 'content editor':
      $perms[] = 'view all unpublished content';

      // WB Moderation
      $perms[] = 'view moderation history';
      $perms[] = 'view moderation messages';
      // @todo needs to be more dynamic based on what states are available
      // $perms[] = 'moderate content from draft to needs_review';
      // $perms[] = 'moderate content from needs_review to draft';
      // $perms[] = 'moderate content from needs_review to published';

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
      'administer search';
      'search content';
      'use advanced search';
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
      $perms[] = 'administer mailsystem';

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
      $perms[] = 'edit mimemail user settings';

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
  return getenv('ENVTYPE') && ! in_array(getenv('ENVTYPE'), array('production', 'staging'));
}

/**
 * Temporary function... not intended for use.
 * Meant to help brainstorm dynamic permission utilities.
 * @todo remove this after we have good functional perm utilities
 */
function all_the_perms () {
  return array (
  // 'administer blocks' => 'block',

  // 'access all webform results' => 'webform',
  // 'access own webform results' => 'webform',
  // 'edit all webform submissions' => 'webform',
  // 'delete all webform submissions' => 'webform',
  // 'access own webform submissions' => 'webform',
  // 'delete own webform submissions' => 'webform',

  // 'administer checkout' => 'commerce_checkout',
  // 'access checkout' => 'commerce_checkout',

  // 'administer customer profile types' => 'commerce_customer',
  // 'administer commerce_customer_profile entities' => 'commerce_customer',

  // 'create commerce_customer_profile entities' => 'commerce_customer',
  // 'view own commerce_customer_profile entities' => 'commerce_customer',
  // 'view any commerce_customer_profile entity' => 'commerce_customer',
  // 'create commerce_customer_profile entities of bundle billing' => 'commerce_customer',
  // 'view own commerce_customer_profile entities of bundle billing' => 'commerce_customer',
  // 'view any commerce_customer_profile entity of bundle billing' => 'commerce_customer',
  // 'create commerce_customer_profile entities of bundle shipping' => 'commerce_customer',
  // 'view own commerce_customer_profile entities of bundle shipping' => 'commerce_customer',
  // 'view any commerce_customer_profile entity of bundle shipping' => 'commerce_customer',

  // 'administer commerce discounts' => 'commerce_discount',
  // 'administer flat rate services' => 'commerce_flat_rate',
  // 'administer line item types' => 'commerce_line_item',
  // 'administer line items' => 'commerce_line_item',

  // 'administer commerce_order entities' => 'commerce_order',
  // 'create commerce_order entities' => 'commerce_order',
  // 'view own commerce_order entities' => 'commerce_order',
  // 'view any commerce_order entity' => 'commerce_order',
  // 'create commerce_order entities of bundle commerce_order' => 'commerce_order',
  // 'view own commerce_order entities of bundle commerce_order' => 'commerce_order',
  // 'view any commerce_order entity of bundle commerce_order' => 'commerce_order',
  // 'configure order settings' => 'commerce_order',

  // 'administer payment methods' => 'commerce_payment',
  // 'administer payments' => 'commerce_payment',
  // 'view payments' => 'commerce_payment',
  // 'create payments' => 'commerce_payment',
  // 'update payments' => 'commerce_payment',
  // 'delete payments' => 'commerce_payment',
  // 'administer product types' => 'commerce_product',
  // 'administer commerce_product entities' => 'commerce_product',
  // 'create commerce_product entities' => 'commerce_product',
  // 'view own commerce_product entities' => 'commerce_product',
  // 'view any commerce_product entity' => 'commerce_product',
  // 'create commerce_product entities of bundle product' => 'commerce_product',
  // 'view own commerce_product entities of bundle product' => 'commerce_product',
  // 'view any commerce_product entity of bundle product' => 'commerce_product',

  // 'administer product pricing' => 'commerce_product_pricing_ui',

  // 'administer shipping' => 'commerce_shipping',
  // 'administer taxes' => 'commerce_tax_ui',
  // 'view own wishlist' => 'commerce_wishlist',
  // 'administer wishlists' => 'commerce_wishlist',

  // 'access contextual links' => 'contextual',

  // 'access dashboard' => 'dashboard',

  // 'administer entity view modes' => 'entity_view_mode',

  // 'administer feeds' => 'feeds',
  // 'administer feeds_tamper' => 'feeds_tamper',

  // 'administer filters' => 'filter',
  'use text format filtered_html' => 'filter',
  'use text format full_html' => 'filter',

  'access forward' => 'forward',
  'access epostcard' => 'forward',
  'override email address' => 'forward',
  'administer forward' => 'forward',
  'override flood control' => 'forward',

  // 'decrypt payment card data' => 'hfc_commerce_gpg',

  // 'administer image styles' => 'image',
  // 'administer mailsystem' => 'mailsystem',

  // 'administer menu' => 'menu',
  // 'import or export menu' => 'menu_import',
  // 'administer menu positions' => 'menu_position',

  // 'administer meta tags' => 'metatag',
  // 'edit meta tags' => 'metatag',

  // 'edit mimemail user settings' => 'mimemail',

  // 'administer module filter' => 'module_filter',

  // 'bypass node access' => 'node',
  // 'administer content types' => 'node',
  // 'administer nodes' => 'node',
  // 'access content overview' => 'node',
  // 'access content' => 'node',
  // 'view own unpublished content' => 'node',
  // 'view revisions' => 'node',
  // 'revert revisions' => 'node',
  // 'delete revisions' => 'node',
  // 'create author content' => 'node',
  // 'delete own author content' => 'node',
  // 'delete any author content' => 'node',
  // 'create product_general content' => 'node',
  // 'delete own product_general content' => 'node',
  // 'delete any product_general content' => 'node',
  // 'create article content' => 'node',
  // 'delete own article content' => 'node',
  // 'delete any article content' => 'node',
  // 'create page content' => 'node',
  // 'delete own page content' => 'node',
  // 'delete any page content' => 'node',
  // 'create webform content' => 'node',
  // 'delete own webform content' => 'node',
  // 'delete any webform content' => 'node',

  // 'administer url aliases' => 'path',
  // 'create url aliases' => 'path',

  // 'administer search' => 'search',
  // 'search content' => 'search',
  // 'use advanced search' => 'search',

  // 'administer services' => 'services',
  // 'get any binary files' => 'services',
  // 'get own binary files' => 'services',
  // 'save file information' => 'services',
  // 'get a system variable' => 'services',
  // 'set a system variable' => 'services',
  // 'perform unlimited index queries' => 'services',

  // 'administer shortcuts' => 'shortcut',
  // 'customize shortcut links' => 'shortcut',
  // 'switch shortcut sets' => 'shortcut',

  // 'administer modules' => 'system',
  // 'administer site configuration' => 'system',
  // 'administer themes' => 'system',
  // 'administer software updates' => 'system',

  // I don't know what this is?? Didn't add it to anything
  // 'administer actions' => 'system',

  // 'access administration pages' => 'system',
  // 'access site in maintenance mode' => 'system',
  // 'view the administration theme' => 'system',
  // 'access site reports' => 'system',
  // 'block IP addresses' => 'system',

  // 'administer taxonomy' => 'taxonomy',
  // 'edit terms in 2' => 'taxonomy',
  // 'delete terms in 2' => 'taxonomy',
  // 'edit terms in 3' => 'taxonomy',
  // 'delete terms in 3' => 'taxonomy',
  // 'edit terms in 1' => 'taxonomy',
  // 'delete terms in 1' => 'taxonomy',

  // 'import taxonomy by csv' => 'taxonomy_csv',
  // 'export taxonomy by csv' => 'taxonomy_csv',

  // 'access toolbar' => 'toolbar',

  // 'administer permissions' => 'user',
  // 'administer users' => 'user',
  // 'access user profiles' => 'user',
  // 'change own username' => 'user',
  // 'cancel account' => 'user',
  // 'select account cancellation method' => 'user',

  // 'administer workbench' => 'workbench',
  // 'access workbench' => 'workbench',

  // 'configure store' => 'commerce',

  // 'administer fieldgroups' => 'field_group',

  // 'administer pathauto' => 'pathauto',

  // 'notify of path changes' => 'pathauto',

  // 'administer xmlsitemap' => 'xmlsitemap',

  // 'view all unpublished content' => 'workbench_moderation',
  // 'administer workbench moderation' => 'workbench_moderation',
  // 'bypass workbench moderation' => 'workbench_moderation',
  // 'view moderation history' => 'workbench_moderation',
  // 'view moderation messages' => 'workbench_moderation',
  // 'use workbench_moderation my drafts tab' => 'workbench_moderation',
  // 'use workbench_moderation needs review tab' => 'workbench_moderation',
  // 'moderate content from draft to needs_review' => 'workbench_moderation',
  // 'moderate content from needs_review to draft' => 'workbench_moderation',
  // 'moderate content from needs_review to published' => 'workbench_moderation',

  // 'administer views' => 'views',
  // 'access all views' => 'views',

  // 'administer features' => 'features',
  // 'manage features' => 'features',
  // 'generate features' => 'features',

  // 'administer rules' => 'rules',
  // 'bypass rules access' => 'rules',
  // 'access rules debug' => 'rules',
);
}
