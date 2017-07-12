# Configuration Manager Sprinkle for [UserFrosting 4](https://www.userfrosting.com)

Configuration Manager sprinkle for [UserFrosting 4](https://www.userfrosting.com). Lets you edit UserFrosting configs from the interface.

> This version only works with UserFrosting 4.1.x !

# Help and Contributing

If you need help using this sprinkle or found any bug, feels free to open an issue or submit a pull request. You can also find me on the [UserFrosting Chat](https://chat.userfrosting.com/) most of the time for direct support. 

<a href='https://ko-fi.com/A7052ICP' target='_blank'><img height='36' style='border:0px;height:36px;' src='https://az743702.vo.msecnd.net/cdn/kofi4.png?v=0' border='0' alt='Buy Me a Coffee at ko-fi.com' /></a>

# Installation

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

# Licence

By [Louis Charette](https://github.com/lcharette). Copyright (c) 2017, free to use in personal and commercial software as per the MIT license.