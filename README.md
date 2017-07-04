# Configuration Manager Sprinkle
Configuration Manager sprinkle for Userfrosting V4. Lets you edit UserFrosting configs from the interface.

> This version only works with UserFrosting 4.1.x !

## Install
Edit UserFrosting `app/sprinkles.json` file and add the following to the `require` list : `"lcharette/uf_configmanager": "^2.0.0"`. Also add `FormGenerator` and `ConfigManager` to the `base` list. For example:

```
{
    "require": {
        "lcharette/uf_configmanager": "^2.0.0"
    },
    "base": [
        "core",
        "account",
        "admin",
        "FormGenerator",
        "ConfigManager"
    ]
}
```

Run `composer update` then `php bakery bake` to install the sprinkle.

## Permissions
The migration will automatically add the `update_site_config` permission to the `Site Administrator` role. To give access to the config UI to another user, simply add the `update_site_config` permission slug to that user role. 

## Add link to the menu
The configuration UI is bound to the the `/settings` route. Simply add a link to this route where you want it. The checkAccess make it so it will appear only for users having the appropriate permission. For example, you can add the following to the sidebar menu :

```
{% if checkAccess('update_site_config') %}
<li>
    <a href="{{site.uri.public}}/settings"><i class="fa fa-gears fa-fw"></i> <span>{{ translate("SITE.CONFIG.MANAGER") }}</span></a>
</li>
{% endif %}
```

## Adding custom config

! TODO

> *NOTE* Only `.json` are accepted. `Yaml` schemas are cannot be used for now.
