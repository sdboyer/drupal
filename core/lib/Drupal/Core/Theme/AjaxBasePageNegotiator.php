<?php

/**
 * @file
 * Contains \Drupal\Core\Theme\AjaxBasePageNegotiator.
 */

namespace Drupal\Core\Theme;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a theme negotiator that deals with the active theme on ajax requests.
 *
 * Many different pages can invoke an Ajax request to system/ajax or another
 * generic Ajax path. It is almost always desired for an Ajax response to be
 * rendered using the same theme as the base page, because most themes are built
 * with the assumption that they control the entire page, so if the CSS for two
 * themes are both loaded for a given page, they may conflict with each other.
 * For example, Bartik is Drupal's default theme, and Seven is Drupal's default
 * administration theme. Depending on whether the "Use the administration theme
 * when editing or creating content" checkbox is checked, the node edit form may
 * be displayed in either theme, but the Ajax response to the Field module's
 * "Add another item" button should be rendered using the same theme as the rest
 * of the page.
 *
 * Therefore specify '_theme: ajax_base_page' as part of the router options.
 */
class AjaxBasePageNegotiator implements ThemeNegotiatorInterface {

  /**
   * The CSRF token generator.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfGenerator;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructs a new AjaxBasePageNegotiator.
   *
   * @param \Drupal\Core\Access\CsrfTokenGenerator $token_generator
   *   The CSRF token generator.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory.
   */
  public function __construct(CsrfTokenGenerator $token_generator, ConfigFactory $config_factory) {
    $this->csrfGenerator = $token_generator;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Request $request) {
    // Check whether the route was configured to use the base page theme.
    return ($route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT))
      && $route->hasOption('_theme')
      && $route->getOption('_theme') == 'ajax_base_page';
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(Request $request) {
    if (($ajax_page_state = $request->request->get('ajax_page_state'))  && !empty($ajax_page_state['theme']) && !empty($ajax_page_state['theme_token'])) {
      $theme = $ajax_page_state['theme'];
      $token = $ajax_page_state['theme_token'];

      // Prevent a request forgery from giving a person access to a theme they
      // shouldn't be otherwise allowed to see. However, since everyone is
      // allowed to see the default theme, token validation isn't required for
      // that, and bypassing it allows most use-cases to work even when accessed
      // from the page cache.
      if ($theme === $this->configFactory->get('system.theme')->get('default') || $this->csrfGenerator->validate($token, $theme)) {
        return $theme;
      }
    }
  }

}
