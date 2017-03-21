# Configuration Manager Sprinkle
Configuration Manager sprinkle for Userfrosting V4. Lets you edit UserFrosting configs from the interface.

## Install
### Clone Sprinkle as Submodule
`cd` into the sprinkle directory of UserFrosting and clone as submodule:
```
git submodule add git@github.com:lcharette/UF_ConfigManager.git ConfigManager
```

### Install dependencies
This sprinkle requires the `FormGenerator` sprinkle. You'll find instruction on how to install it here : https://github.com/lcharette/UF_FormGenerator

### Update composer
From the UserFrosting `/app` folder, run `composer update`

### Add to the sprinkle list
Edit UserFrosting `app/sprinkles/sprinkles.json` file and add `ConfigManager` to the sprinkle list to enable it.

### Edit index.php
You also need to add the Sprinkle Middleware. Find this line:
```
$app->run();
```

And add this right above it:
```
$app->add($container->configManager);
```

### Update the assets build
From the UserFrosting `/build` folder, run `npm run uf-assets-install`

### Install database migrations
Go to the `migrations/` directory and run `php install.php`.

## Permissions
The migration will automatically add the `update_site_config` permission to the `Site Administrator` role. To give access to the config UI to another user, simply add the `update_site_config` permission slug to that user role. 

## Add link to the menu
The configuration UI is bound to the the `/admin/settings` route. Simply add a link to this route where you want it. The checkAccess make it so it will appear only for users having the appropriate permission. For example, you can add the following to the sidebar menu :

```
{% if checkAccess('update_site_config') %}
<li>
    <a href="{{site.uri.public}}/admin/settings"><i class="fa fa-gears fa-fw"></i> <span>{{ translate("SITE.CONFIG.MANAGER") }}</span></a>
</li>
{% endif %}
```