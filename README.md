# Configuration Manager Sprinkle
Configuration Manager sprinkle for Userfrosting V4

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
Edit UserFrosting `app/sprinkles/sprinkles.json` file and add `ConfigManager` to the sprinkle list to enable it globally.

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

### Create the MySQL table
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