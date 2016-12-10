# UF_ConfigManager
Configuration Manager sprinkle for Userfrosting V4

## Install
`cd` into the sprinkle directory of UserFrosting and clone as submodule:
```
git submodule add git@github.com:lcharette/UF_ConfigManager.git ConfigManager
```

Edit UserFrosting `public/index.php` file and add `ConfigManager` *at the end* of the sprinkle list. *THIS SPRINKLE REQUIRES TO BE LOADED LAST*

## bundle.config.json
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