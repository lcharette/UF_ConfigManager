# Configuration Manager Sprinkle
Configuration Manager sprinkle for Userfrosting V4

## Install
`cd` into the sprinkle directory of UserFrosting and clone as submodule:
```
git submodule add git@github.com:lcharette/UF_ConfigManager.git ConfigManager
```

This sprinkle requires the `FormGenerator` sprinkle. You'll find instruction on how to install it here : https://github.com/lcharette/UF_FormGenerator

Next, edit UserFrosting `public/index.php` file and add `ConfigManager` _at the end_ of the sprinkle list to enable it. _IMPORTANT: THIS SPRINKLE REQUIRES TO BE LOADED LAST_

## Add the js bundle
Edit the `build/bundle.config.json` and add this at the end
```
    "js/ConfigManager" : {
        "scripts": [
            "js/ConfigManager.js"
        ],
        "options": {
            "result": {
                "type": {
                  "styles": "plain"
                }
            }
        }
    }
```

## Create the MySQL table
Add the necessary prefix to the `settings` table name if your install requires it.
```
CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(255) COLLATE utf8_bin NOT NULL,
  `value` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `cached` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);
  
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
```