# NetBrothers Create Symfony Bundle
Command-line tool for creating skeleton structure for symfony bundle


## Usage
After installation:

On command line insert `php bin/console netbrothers:make-bundle [YourBundleName]`. Be aware to name your
bundle with "Bundle" - like symfony demand (see https://symfony.com/doc/current/bundles/best_practices.html).

```console
# this is wrong !!!
php bin/console netbrothers:make-bundle Apple

# this is correct
php bin/console netbrothers:make-bundle AppleBundle
```


## Configuration
If you prefer to change templates:
- Find under `installation/templates` basic templates. Copy them to any place you like.
- Copy `installation/config/packages/netbrothers_createbundle.yaml` to symfony's config path
- Insert into `netbrothers_createbundle.yaml` your template path.
- Clear symfony's cache.


# Author
Stefan Wessel, NetBrothers GmbH

# Licence
MIT




