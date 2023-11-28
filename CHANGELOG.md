# Change Log

## [5.0.0](https://github.com/lcharette/UF_ConfigManager/compare/3.0.0...4.0.0)
- Update for UserFrosting 5
- Default schema is not included anymore. You can copy it from `public/schema/config/`. 
- Demo is available to test from `public/`

## [3.0.0]
- Bump minimum PHP version to 7.1.
- Support for FormGenerator 4.0
- `Config` model renamed to `Setting` to better reflect the table name.
- `ConfigManager` constructor now accept only the required services instead of the whole CI.
- Remove `ConfigManaher::set_atomic` method.
- `ConfigManagerController::update` now throws an error if `$_POST` or `$args` is missing, or if schema is not found.
- Updated migration definitions for newer version of UserFrosting.
- Stricter PHP7 type throughout.
- Added automated testing.
- Added PHP-CS-Fixer, Travis, PHPStan, StyleCI configuration.

## [2.1.0]
- Support for UserFrosting 4.2 / FormGenerator 3.0.0

## [2.0.4]
- Fix issue with FormGenerator (for real this time)
- Bump FormGenerator version

## [2.0.3]
- Fix issue with FormGenerator

## [2.0.2]
- Fix assets bundle issue

## [2.0.1]
- Updated dependencies

## [2.0.0]
Updated for UserFrosting v4.1.x

## [1.0.2]
- Update composer.json

## [1.0.1]
- Added controlled access
- Added more settings to the default UI

## 1.0.0
- Initial release

[3.0.0]: https://github.com/lcharette/UF_ConfigManager/compare/2.1.0...3.0.0
[2.1.0]: https://github.com/lcharette/UF_ConfigManager/compare/2.0.4...2.1.0
[2.0.4]: https://github.com/lcharette/UF_ConfigManager/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/lcharette/UF_ConfigManager/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/lcharette/UF_ConfigManager/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/lcharette/UF_ConfigManager/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/lcharette/UF_ConfigManager/compare/1.0.2...2.0.0
[1.0.2]: https://github.com/lcharette/UF_ConfigManager/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/lcharette/UF_ConfigManager/compare/1.0.0...1.0.1
