#Integrate to existing Symfony Project


Install bundle
```shell
composer require smart-core/cms-bundle
```


Add code to /src/Kernel.php
```php
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        // ......            
            
        $this->getBundle('CMSBundle')->configureContainer($container, $this->getProjectDir());
    }
}
```


Add routes
```yaml
# /config/routes/z-cms.yaml
smart_core_cms:
    resource: '@CMSBundle/Resources/config/routes.yaml'
```


Configure security
```yaml
# /config/packages/security.yaml
security:
    ......
    firewalls:
        ......        
        cms_admin:
            provider: '===YOU_PROVIDER_NAME==='
            context: cms
            pattern: ^/%cms.admin_path%
            form_login:
                check_path: /%cms.admin_path%/
                login_path: /%cms.admin_path%/
                default_target_path: /%cms.admin_path%/
            logout:
                path: /%cms.admin_path%/logout
            remember_me:
                secret: "%env(APP_SECRET)%"
                name: REMEMBER_ME
                lifetime: 31536000 # 365 days in seconds
                path: /
                domain: ~
        ......
        main:
            context: cms
            ......
            
    role_hierarchy:
        ROLE_SUPER_ADMIN: ROLE_ADMIN
        ROLE_ADMIN: ROLE_USER

    access_control:
        - { path: ^/%cms.admin_path%/$, allow_if: '!is_authenticated() or is_fully_authenticated()' } }
        - { path: ^/%cms.admin_path%, roles: ROLE_ADMIN }
        ......
```


First run
```shell
bin/console cms:setup
```

