Edgy-bet is a program written in PHP for detecting arbitrage between high street bookmakers and the Betfair betting exchange.


The code has not been alive in 3 years and needs to extensive de-rusting to work.
That said, the logic and maths are sound and may help others on their endeavours (If you care to dig it out of the code)

In order to get the UI and automated scraping up and running you'll need to:

1) Update oddsChecker.class.php
2) get a betfair API key to fill in betfair.class.php
3) update (if needed?) betfair methods in betfair*.php
4) setup a cron job to hit automator.php once a minute
5) Setup a valid DB schema

Feel free to create an issue in this repo if theres any issues with the above and I might be able to help (especially the last one)


Shield: [![CC BY 4.0][cc-by-shield]][cc-by]

This work is licensed under a
[Creative Commons Attribution 4.0 International License][cc-by].

[![CC BY 4.0][cc-by-image]][cc-by]

[cc-by]: http://creativecommons.org/licenses/by/4.0/
[cc-by-image]: https://i.creativecommons.org/l/by/4.0/88x31.png
[cc-by-shield]: https://img.shields.io/badge/License-CC%20BY%204.0-lightgrey.svg
