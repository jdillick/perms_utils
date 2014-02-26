<?php

namespace hfc\perms_utils;

function content_perms($content_type = FALSE) {
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

function comment_perms() {
  return array(
    'administer comments',
    'access comments',
    'post comments',
    'skip comment approval',
  );
}

function admin_ds_perms() {
  return array(
    'admin_view_modes',
    'admin_fields',
    'admin_classes',
    'admin_display_suite',
  );
}

/**
 * Give all perms for one or more module
 */
function module_admin_perms($modules = FALSE) {
  // for stuff we don't want to accidentally hand out
  $blacklist = array();

  if ( ! $modules ) return array();
  if ( ! is_array($modules) ) $modules = array($modules);

  $perms = array();
  foreach ( user_permission_get_modules() as $perm => $perm_module ) {
    if ( in_array($perm_module, $modules) && ! in_array($perm, $blacklist) ) {
      $perms[] = $perm;
    }
  }
  return $perm;
}

function all_the_perms () {
  return array (
  'administer blocks' => 'block',
  'access all webform results' => 'webform',
  'access own webform results' => 'webform',
  'edit all webform submissions' => 'webform',
  'delete all webform submissions' => 'webform',
  'access own webform submissions' => 'webform',
  'delete own webform submissions' => 'webform',

  'administer checkout' => 'commerce_checkout',
  'access checkout' => 'commerce_checkout',

  'administer customer profile types' => 'commerce_customer',
  'administer commerce_customer_profile entities' => 'commerce_customer',

  'create commerce_customer_profile entities' => 'commerce_customer',
  'view own commerce_customer_profile entities' => 'commerce_customer',
  'view any commerce_customer_profile entity' => 'commerce_customer',
  'create commerce_customer_profile entities of bundle billing' => 'commerce_customer',
  'view own commerce_customer_profile entities of bundle billing' => 'commerce_customer',
  'view any commerce_customer_profile entity of bundle billing' => 'commerce_customer',
  'create commerce_customer_profile entities of bundle shipping' => 'commerce_customer',
  'view own commerce_customer_profile entities of bundle shipping' => 'commerce_customer',
  'view any commerce_customer_profile entity of bundle shipping' => 'commerce_customer',

  'administer commerce discounts' => 'commerce_discount',
  'administer flat rate services' => 'commerce_flat_rate',
  'administer line item types' => 'commerce_line_item',
  'administer line items' => 'commerce_line_item',

  'administer commerce_order entities' => 'commerce_order',
  'create commerce_order entities' => 'commerce_order',
  'view own commerce_order entities' => 'commerce_order',
  'view any commerce_order entity' => 'commerce_order',
  'create commerce_order entities of bundle commerce_order' => 'commerce_order',
  'view own commerce_order entities of bundle commerce_order' => 'commerce_order',
  'view any commerce_order entity of bundle commerce_order' => 'commerce_order',
  'configure order settings' => 'commerce_order',

  'administer payment methods' => 'commerce_payment',
  'administer payments' => 'commerce_payment',
  'view payments' => 'commerce_payment',
  'create payments' => 'commerce_payment',
  'update payments' => 'commerce_payment',
  'delete payments' => 'commerce_payment',
  'administer product types' => 'commerce_product',
  'administer commerce_product entities' => 'commerce_product',
  'create commerce_product entities' => 'commerce_product',
  'view own commerce_product entities' => 'commerce_product',
  'view any commerce_product entity' => 'commerce_product',
  'create commerce_product entities of bundle product' => 'commerce_product',
  'view own commerce_product entities of bundle product' => 'commerce_product',
  'view any commerce_product entity of bundle product' => 'commerce_product',

  'administer product pricing' => 'commerce_product_pricing_ui',

  'administer shipping' => 'commerce_shipping',
  'administer taxes' => 'commerce_tax_ui',
  'view own wishlist' => 'commerce_wishlist',
  'administer wishlists' => 'commerce_wishlist',
  'access contextual links' => 'contextual',

  'access dashboard' => 'dashboard',

  'administer entity view modes' => 'entity_view_mode',
  'administer feeds' => 'feeds',
  'administer feeds_tamper' => 'feeds_tamper',

  'administer filters' => 'filter',
  'use text format filtered_html' => 'filter',
  'use text format full_html' => 'filter',

  'access forward' => 'forward',
  'access epostcard' => 'forward',
  'override email address' => 'forward',
  'administer forward' => 'forward',
  'override flood control' => 'forward',
  'decrypt payment card data' => 'hfc_commerce_gpg',
  'administer image styles' => 'image',
  'administer mailsystem' => 'mailsystem',
  'administer menu' => 'menu',
  'import or export menu' => 'menu_import',
  'administer menu positions' => 'menu_position',
  'administer meta tags' => 'metatag',
  'edit meta tags' => 'metatag',
  'edit mimemail user settings' => 'mimemail',
  'administer module filter' => 'module_filter',

  'bypass node access' => 'node',
  'administer content types' => 'node',
  'administer nodes' => 'node',
  'access content overview' => 'node',
  'access content' => 'node',
  'view own unpublished content' => 'node',
  'view revisions' => 'node',
  'revert revisions' => 'node',
  'delete revisions' => 'node',
  'create author content' => 'node',
  'delete own author content' => 'node',
  'delete any author content' => 'node',
  'create product_general content' => 'node',
  'delete own product_general content' => 'node',
  'delete any product_general content' => 'node',
  'create article content' => 'node',
  'delete own article content' => 'node',
  'delete any article content' => 'node',
  'create page content' => 'node',
  'delete own page content' => 'node',
  'delete any page content' => 'node',
  'create webform content' => 'node',
  'delete own webform content' => 'node',
  'delete any webform content' => 'node',
  'use panels dashboard' => 'panels',
  'view pane admin links' => 'panels',
  'administer pane access' => 'panels',
  'use panels in place editing' => 'panels',
  'change layouts in place editing' => 'panels',
  'administer advanced pane settings' => 'panels',
  'administer panels layouts' => 'panels',
  'administer panels styles' => 'panels',
  'use panels caching features' => 'panels',
  'use panels locks' => 'panels',
  'create mini panels' => 'panels_mini',
  'administer mini panels' => 'panels_mini',
  'administer url aliases' => 'path',
  'create url aliases' => 'path',
  'administer search' => 'search',
  'search content' => 'search',
  'use advanced search' => 'search',
  'administer services' => 'services',
  'get any binary files' => 'services',
  'get own binary files' => 'services',
  'save file information' => 'services',
  'get a system variable' => 'services',
  'set a system variable' => 'services',
  'perform unlimited index queries' => 'services',
  'administer shortcuts' => 'shortcut',
  'customize shortcut links' => 'shortcut',
  'switch shortcut sets' => 'shortcut',
  'administer modules' => 'system',
  'administer site configuration' => 'system',
  'administer themes' => 'system',
  'administer software updates' => 'system',
  'administer actions' => 'system',
  'access administration pages' => 'system',
  'access site in maintenance mode' => 'system',
  'view the administration theme' => 'system',
  'access site reports' => 'system',
  'block IP addresses' => 'system',
  'administer taxonomy' => 'taxonomy',
  'edit terms in 2' => 'taxonomy',
  'delete terms in 2' => 'taxonomy',
  'edit terms in 3' => 'taxonomy',
  'delete terms in 3' => 'taxonomy',
  'edit terms in 1' => 'taxonomy',
  'delete terms in 1' => 'taxonomy',
  'import taxonomy by csv' => 'taxonomy_csv',
  'export taxonomy by csv' => 'taxonomy_csv',
  'access toolbar' => 'toolbar',
  'administer permissions' => 'user',
  'administer users' => 'user',
  'access user profiles' => 'user',
  'change own username' => 'user',
  'cancel account' => 'user',
  'select account cancellation method' => 'user',
  'administer workbench' => 'workbench',
  'access workbench' => 'workbench',
  'configure store' => 'commerce',


  'administer fieldgroups' => 'field_group',
  'administer pathauto' => 'pathauto',
  'notify of path changes' => 'pathauto',
  'administer xmlsitemap' => 'xmlsitemap',
  'view all unpublished content' => 'workbench_moderation',
  'administer workbench moderation' => 'workbench_moderation',
  'bypass workbench moderation' => 'workbench_moderation',
  'view moderation history' => 'workbench_moderation',
  'view moderation messages' => 'workbench_moderation',
  'use workbench_moderation my drafts tab' => 'workbench_moderation',
  'use workbench_moderation needs review tab' => 'workbench_moderation',
  'moderate content from draft to needs_review' => 'workbench_moderation',
  'moderate content from needs_review to draft' => 'workbench_moderation',
  'moderate content from needs_review to published' => 'workbench_moderation',
  'administer views' => 'views',
  'access all views' => 'views',
  'administer features' => 'features',
  'manage features' => 'features',
  'generate features' => 'features',
  'administer rules' => 'rules',
  'bypass rules access' => 'rules',
  'access rules debug' => 'rules',
);
}
