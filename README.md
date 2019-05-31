# Cacheful Wordpress plugin
Cacheful offers a simple, yet powerful way to warm-up your website cache.
This specific module for Wordpress integrates with the cacheful API in order to initiate a warm-up process,
either manually or automatically after your website cache has been flushed.
This way the first page load for every request is handled by the server,
so that no actual visitor has to deal with delay in page load.

## Install with Composer
- Download the repository and place it in `wp-content/plugins`
- Run`composer install` in the repository

## Quick start in 3 steps
- Create a free account [on cacheful](https://cacheful.app).
- Create your first team and project.
- Configure the following keys in `wp-config.php`
    - `CACHEFUL_ENABLED`
    - `CACHEFUL_PROJECT_ID`
    - `CACHEFUL_API_TOKEN`
