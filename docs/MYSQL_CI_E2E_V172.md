# CI-only MySQL/PHP end-to-end verification (new in V1.7.2)

The workflow spins up **disposable MySQL 8.0** as a GitHub Actions `services` container, runs migrations 001–006 in order, and starts PHP's built-in HTTP server on localhost with `APP_ENV=development` for request/response tests. This is NOT remote staging, HTTPS/TLS verification or production suitability. The test refuses other DB hosts/environments/passwords.

The tests cover: schema migration success, health probes, unauthenticated access, synthetic patient registration/duplicate refusal, incorrect password, login token, patient scope, draft consent policy retrieval, failed inactive grant, valid revocation, consent event ledger, legacy grant refusal, logout token invalidation.

Required CI environment: `mysql:8.0` service, `php-cli`, `php-mysql`, `php-mbstring`, `default-mysql-client`. No real patient data, doctors or licensed clinical services are tested. At the current local environment the test has NOT run due to lack of MySQL and PHP pdo_mysql extensions.
