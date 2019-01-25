# Configuration Manager Sprinkle for [UserFrosting 4](https://www.userfrosting.com)

[![Latest Version](https://img.shields.io/github/release/lcharette/UF_ConfigManager.svg)](https://github.com/lcharette/UF_ConfigManager/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![UserFrosting Version](https://img.shields.io/badge/UserFrosting->=%204.1-brightgreen.svg)](https://github.com/userfrosting/UserFrosting)
[![StyleCI](https://github.styleci.io/repos/76127967/shield?branch=master&style=flat)](https://github.styleci.io/repos/76127967)
[![Donate](https://img.shields.io/badge/Donate-Buy%20Me%20a%20Coffee-blue.svg)](https://ko-fi.com/A7052ICP)

Configuration Manager sprinkle for [UserFrosting 4](https://www.userfrosting.com). Lets you edit UserFrosting configs from the interface.

# Help and Contributing

If you need help using this sprinkle or found any bug, feels free to open an issue or submit a pull request. You can also find me on the [UserFrosting Chat](https://chat.userfrosting.com/) most of the time for direct support.

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

By [Louis Charette](https://github.com/lcharette). Copyright (c) 2019, free to use in personal and commercial software as per the MIT license.
