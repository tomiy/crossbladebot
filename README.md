# CrossbladeBot

CrossbladeBot is a Twitch IRC bot made entirely in PHP and ran through the CLI.

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/b5c32cec78ed4e558a7b266aed39d7df)](https://www.codacy.com/gh/tomiy/crossbladebot/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=tomiy/crossbladebot&amp;utm_campaign=Badge_Grade)

## Getting Started

In config/client.json, put your bot name, channel, and oauth.

Then, in a console, run `php main.php` from the root directory.

### Prerequisites

*   PHP 7.4 and up
*   Composer

### Installing

Clone the repo and run `composer update` from the root directory.

## Running the tests

In config/logger.json, set the level to 4 (debug). The crossbladebot.log file should show much more info about what's going on.

Use the following commands to generate a coverage report:

*   Windows: `vendor/bin/phpunit.bat ./tests --coverage-clover ./build/logs/clover.xml`
*   Linux: `php vendor/bin/phpunit ./tests --coverage-clover ./build/logs/clover.xml`

## Deployment

You need to be able to run a process through a daemon indefinitely, so be careful deploying the bot on a shared env - they usually don't like it very much.

## Contributing

Just make a PR and i'll check it out, i'm not really into big books of rules.

## Authors

*   **Tom Chappaz** - *Initial work* - [tomiy](https://github.com/tomiy)

See also the list of [contributors](https://github.com/tomiy/crossbladebot/contributors) who participated in this project.

## License

This project is licensed under the GPL 3.0 License - see the LICENSE file for details.

## Acknowledgments

*   tmi.js for the initial structure
