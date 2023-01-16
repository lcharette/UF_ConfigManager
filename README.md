# Configuration Manager Sprinkle for [UserFrosting 5](https://www.userfrosting.com)

[![Donate][kofi-badge]][kofi]
[![Latest Version][releases-badge]][releases]
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Build][build-badge]][build]
[![Codecov][codecov-badge]][codecov]
[![StyleCI][styleci-badge]][styleci]
[![][PHPStan-img]][PHPStan]

[kofi]: https://ko-fi.com/A7052ICP
[kofi-badge]: https://img.shields.io/badge/Donate-Buy%20Me%20a%20Coffee-blue.svg
[releases]: https://github.com/lcharette/UF_ConfigManager/releases
[releases-badge]: https://img.shields.io/github/release/lcharette/UF_ConfigManager.svg?include_prereleases&sort=semver
[build]: https://github.com/lcharette/UF_ConfigManager/actions?query=workflow%3ABuild
[build-badge]: https://img.shields.io/github/actions/workflow/status/lcharette/UF_ConfigManager/Build.yml?branch=5.0&logo=github
[codecov]: https://codecov.io/gh/lcharette/UF_ConfigManager
[codecov-badge]: https://codecov.io/gh/lcharette/UF_ConfigManager/branch/5.0/graph/badge.svg
[styleci]: https://styleci.io/repos/76127967
[styleci-badge]: https://styleci.io/repos/76127967/shield?branch=5.0&style=flat
[PHPStan-img]: https://img.shields.io/github/actions/workflow/status/lcharette/UF_ConfigManager/PHPStan.yml?branch=5.0&label=PHPStan
[PHPStan]: https://github.com/lcharette/UF_ConfigManager/actions/workflows/PHPStan.yml

Configuration Manager sprinkle for [UserFrosting 5](https://www.userfrosting.com). Lets you edit UserFrosting configs from the interface.

# Help and Contributing

If you need help using this sprinkle or found any bug, feels free to open an issue or submit a pull request. You can also find me on the [UserFrosting Chat](https://chat.userfrosting.com/) most of the time for direct support.

# Installation

1. Add this package with Composer : 
   ```
   composer require lcharette/uf_configmanager
   ```

2. Edit your Sprinkle Recipe to include the ConfigManager as a Sprinkle dependency :
   ```
   \UserFrosting\Sprinkle\ConfigManager\ConfigManager::class
   ```

3. Run :
   ```
   composer update
   ```
   ```
   php bakery bake
   ```

## Permissions
The migration will automatically add the `update_site_config` permission to the `Site Administrator` role. If it's not added automatically, you can run the `UserFrosting\Sprinkle\ConfigManager\Database\Seeds\SettingsPermissions` seed using the `php bakery seed` command or add it manually in the admin UI. To give access to the config UI to another user, simply add the `update_site_config` permission slug to that user role.

## Add link to the menu
The configuration UI is bound to the the `/settings` route. Simply add a link to this route where you want it. The checkAccess make it so it will appear only for users having the appropriate permission. For example, you can add the following to the sidebar menu :

```html
{% if checkAccess('update_site_config') %}
<li>
    <a href="{{site.uri.public}}/settings"><i class="fa fa-gears fa-fw"></i> <span>{{ translate("SITE.CONFIG.MANAGER") }}</span></a>
</li>
{% endif %}
```

## Adding custom config

Settings are separated by _topics_ in the UI. Each topic is represented by a file, located in `schema/config/`. Unlike normal schema files, all entries needs to be wrapped inside a `config` key. A `name` and `desc` top-level entry will allow to define the title and description of the topic.

```json
{
    "name" : "SITE.CONFIG",
    "desc" : "SITE.CONFIG.DESC",

    "config": { ... }
}
```

For example, to add an entry for `site.title` text and `site.registration.enabled` checkbox option in a "UserFrosting Settings" topic :

```json
{
    "name" : "SITE.CONFIG",
    "desc" : "SITE.CONFIG.DESC",

    "config": {
        "site.title" : {
            "validators" : {
                "required" : {
                    "message" : "SITE.TITLE.REQUIRED"
                }
            },
            "form" : {
                "type" : "text",
                "label" : "SITE.TITLE",
                "icon" : "fa-comment"
            }
        },
        "site.registration.enabled" : {
            "validators" : {},
            "form" : {
                "type" : "checkbox",
                "label" : "SITE.REGISTRATION.ENABLED"
            }
        }
    }
}
```

> *NOTE* Only `.json` are accepted. `Yaml` schemas are cannot be used for now.

# License

By [Louis Charette](https://github.com/lcharette). Copyright (c) 2020, free to use in personal and commercial software as per the MIT license.
