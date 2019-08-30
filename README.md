# CrossbladeBot

CrossbladeBot is a Twitch IRC bot made entirely in PHP and ran through the CLI.

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/106b8405d12940c8bc67e184f09a8926)](https://app.codacy.com/app/tomiy/crossbladebot?utm_source=github.com&utm_medium=referral&utm_content=tomiy/crossbladebot&utm_campaign=Badge_Grade_Dashboard)

## Getting Started

In config/client.json, put your bot name, channel, and oauth.

Then, in a console, run `php main.php` from the root folder.

### Prerequisites

  * PHP 7 and up

### Installing

Clone the repo. There is nothing else to do (for now).

## Running the tests

In config/logger.json, set the level to 4 (debug). The crossbladebot.log file should show much more info about what's going on.

I have no coverage right now but i'm planning to add some in the future.

## Deployment

You need to be able to run a process through a daemon indefinitely, so be careful deploying the bot on a shared env - they usually don't like it very much.

## Contributing

Just make a PR and i'll check it out, i'm not really into big books of rules.

## Authors

  * **Tom Chappaz** - *Initial work* - [tomiy](https://github.com/tomiy)

See also the list of [contributors](https://github.com/tomiy/crossbladebot/contributors) who participated in this project.

## License

This project is licensed under the GPL 3.0 License - see the LICENSE file for details.

## Acknowledgments

  * tmi.js for the initial structure
