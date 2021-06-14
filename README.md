# Bilemo

My seventh OpenClassRooms Project with PHP/Symphony.

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/71d0962974834783bcf51671bf44f6f6)](https://www.codacy.com/gh/kevinmulot/OC-P7-BileMo/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=kevinmulot/OC-P7-BileMo&amp;utm_campaign=Badge_Grade)
[![Maintainability](https://api.codeclimate.com/v1/badges/c31ab97e077166298e97/maintainability)](https://codeclimate.com/github/kevinmulot/OC-P7-BileMo/maintainability)
![OenClassRooms](https://img.shields.io/badge/OpenClassRooms-DA_PHP/SF-blue.svg)
![Project](https://img.shields.io/badge/Project-7-blue.svg)

---

## Installation

### Prerequisites

Install GitHub (<https://gist.github.com/derhuerst/1b15ff4652a867391f03>) \
Install Composer (<https://getcomposer.org>) \
Install Postman  (<https://postman.com/downloads>)

Symfony 4.4 requires PHP 7.1.3 or higher to run.\
Prefer MySQL 5.6 or higher.

### Download

[![Repo Size](https://img.shields.io/github/repo-size/kevinmulot/OC-P7-BileMo?label=Repo+Size)](https://github.com/kevinmulot/OC-P7-BileMo) \
Execute the following command line to download the project into your chosen directory :

```shell
git clone https://github.com/kevinmulot/OC-P7-BileMo.git
```

Install dependencies by running the following command :

```shell
composer install
```

### Database and JWT configuration

Generate the SSH keys for the JWT generation with your own passphrase :

```shell
$ mkdir -p config/jwt
$ openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
$ openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

Set your database connection in the **.env** file (l.28) and provide the JWT passphrase you gave for your SSH keys previously.

```shell
DATABASE_URL=mysql://username:password@127.0.0.1:3306/bilemo?serverVersion=5.7
JWT_PASSPHRASE=#your passphrase
```

Set your database connection in .env file ``[DATABASE_URL] (l.28)``.

```shell
DATABASE_URL=mysql://root:@127.0.0.1:3306/bilemo?serverVersion=5.7
```

Create database:

```shell
php bin/console doctrine:database:create
```

Build the database structure using the following command:

```shell
php bin/console doctrine:migrations:migrate
```

Load the data fixtures

```shell
php bin/console doctrine:fixtures:load
```

### Run the application

Launch the Apache/Php runtime environment by using :

```shell
php bin/console server:run
```

### Default Admin credentials

Default username ```bilemo```\
Default password for the user is ```admin```

### Default Client credentials

Default username ```client#```\
Default password for the user is ```clientpass```

### Login

Provide your credentials on ```/api/login_check``` in order to get your bearer Token.
Copy your token in the header of your request, in the authorization section.

---

## Documentation

Use your navigator to access the documentation with this URL ```https://example.com/api/doc```.

## Support

BileMo has continuous support !

[![Project Maintained](https://img.shields.io/maintenance/yes/2020.svg?label=Maintained)](https://github.com/kevinmulot/OC-P7-BileMo)
[![GitHub Last Commit](https://img.shields.io/github/last-commit/kevinmulot/OC-P7-BileMo.svg?label=Last+Commit)](https://github.com/kevinmulot/OC-P7-BileMo/commits/master)

## Issues

Issues can be created here.

[![GitHub Open Issues](https://img.shields.io/github/issues/kevinmulot/OC-P7-BileMo.svg?label=Issues)](https://github.com/kevinmulot/OC-P7-BileMo/issues)

## Pull Requests

Pull Requests can be created here.

[![GitHub Open Pull Requests](https://img.shields.io/github/issues-pr/kevinmulot/OC-P7-BileMo.svg?label=Pull+Requests)](https://github.com/kevinmulot/OC-P7-BileMo/pulls)

## Copyright

Code released under the MIT License.

[![GitHub License](https://img.shields.io/github/license/kevinmulot/OC-P7-BileMo.svg?label=License)](https://github.com/kevinmulot/OC-P7-BileMo/blob/master/LICENSE.md)
