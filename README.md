# Slim 4 Skeleton Application

## Install

From directory in which you want to install your new application, run this command:

```bash
composer create-project iagapie/slim-skeleton [app-name]
```

Replace `[app-name]` with name of your new application. You'll want to:

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `var/logs/` and `var/cache/` is writable.

To run the application in development, you can run these commands 

```bash
cd [app-name]
composer serve
```

To run the test suite:

```bash
composer test
```

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
