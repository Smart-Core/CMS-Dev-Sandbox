#Integrate to Symfony Project

Install bundle
```shell
composer require smart-core/cms-bundle
```


Edit /composer.json
```json lines
"scripts": {
    ..........
    "auto-scripts": {
        "sh bin/clear_cache": "script",
        "rm -rf public/bundles": "script",
        "assets:install --symlink --relative %PUBLIC_DIR%": "symfony-cmd",
        "cms:adminlte:create-symlink": "symfony-cmd"
    },
    ..........
},
```

Apply changes in composer.json
```shell
composer install
```


Add routes
```yaml
# /config/routes/z-cms.yaml
smart_core_cms:
    resource: '@CMSBundle/Resources/config/routes.yaml'
```


First run
```shell
bin/console cms:install
```

