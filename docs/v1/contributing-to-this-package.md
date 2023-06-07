[⬅️ Back to Documentation overview](/nova-calendar)

---

# Contributing to this package

Contributors to this package are highly welcomed. 

Before you begin coding;

- check out the project's [GitHub page](https://github.com/wdelfuego/nova-calendar) where we talk about possible enhancements, new features and implementation strategies.

- check out the project's [dual license](/nova-calendar/index.html#license) model and make sure you agree to release your contribution under the same terms.

Before you make a pull request;

- run the package's [test suite](#running-tests) to make sure your changes don't break things

- realise that your pull request may be rejected, merged as is or merged after changes at the discretion of the project maintainer [wdelfuego](https://github.com/wdelfuego).

    Whatever the decision, it will be argumented and explained in public on GitHub so anyone can see the considerations behind it and weigh in with their opinion.

For any questions, doubts or remarks you have, please create a [discussion](https://github.com/wdelfuego/nova-calendar/discussions) on GitHub and we'll discuss your thoughts there :).

## Running tests
This package comes with a basic test suite that will be expanded as we run into issues. 

You can run the test suite directly from the Nova project in which you are using it.

1. Make sure `phpunit` is available in your project as a dev dependency (it's present by default in new Laravel projects so it's probably already there).

1. Open your project's `phpunit.xml` and add the following entry to the `<testsuites>` node:
    ```xml
    <testsuite name="NovaCalendar">
      <directory suffix="Test.php">./vendor/wdelfuego/nova-calendar</directory>
    </testsuite>
    ```
    Make sure to use the correct package path if your local fork of this package is located in a different directory.

1. Run the test suite by running the following command from your project's root directory:
   ```sh
    phpunit --testsuite NovaCalendar
   ```

    If the test suite completes as expected, you should see something like this:

    ```console
    PHPUnit 9.5.23 #StandWithUkraine    

    ...........................                                       27 / 27 (100%)

    Time: 00:02.155, Memory: 26.00 MB

    OK (27 tests, 106 assertions)
    ```