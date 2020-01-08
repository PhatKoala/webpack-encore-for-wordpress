=== Webpack Encore for WordPress ===

== Description ==
This plugin is intended to be used as part of WordPress theme development.  It wraps up the functionality of Webpack Encore and exposes it to WordPress.

Webpack Encore is a simpler way to integrate Webpack into your application.  It wraps Webpack, giving you a clean & powerful API for bundling JavaScript modules, pre-processing CSS & JS and compiling and minifying assets.

== Installation ==
1. <a href="https://nodejs.org/en/download/">Install Node.js</a>.

2. <a href="https://yarnpkg.com/lang/en/docs/install/">Install Yarn package manager</a>.

3. From your theme's directory, install Encore via Yarn: yarn add @symfony/webpack-encore --dev

This command creates (or modifies) a package.json file and downloads dependencies into a node_modules/ directory. Yarn also creates/updates a yarn.lock (called package-lock.json if you use npm).

If you are using version control, you should commit package.json and yarn.lock (or package-lock.json if using npm) to version control, but ignore node_modules/.

4. Copy the contents of "theme-template" bundled with this plugin into your theme's directory.

5. Activate the plugin.

6. Compile your assets;

# compile assets once
> yarn encore dev

# or, recompile assets automatically when files change
> yarn encore dev --watch

# on deploy, create a production build
> yarn encore production

If you are using version control, commit the production build.

7. To automatically enqueue CSS and JS, place this inside your functions.php

add_action('wp_enqueue_scripts', function(){
    global $webpackEncore;
    $webpackEncore->enqueue_entry_css('app');
    $webpackEncore->enqueue_entry_js('app');
});

8. Inside your theme file, when referencing an asset, use <?php asset('images/logo.png'); ?>

If you are using a child theme, you will need to write <?php asset('images/logo.png', true); ?>