# Configuration Manager Sprinkle
Configuration Manager sprinkle for Userfrosting V4. Lets you edit UserFrosting configs from the interface.

## Install
Edit UserFrosting `app/sprinkles/sprinkles.json` file and add the following to the `require` list :
```
"lcharette/uf_configmanager": "dev-develop"
```

Run `composer update` then `composer run-script bake` to install the sprinkle.

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